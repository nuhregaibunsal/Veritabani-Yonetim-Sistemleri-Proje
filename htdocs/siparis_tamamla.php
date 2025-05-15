<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = $_SESSION['giren'];

// Kullanıcının adresi çekiliyor (ilk adresi alınıyor, dilersen varsayılan adres sütunu eklersen ona göre de güncelleyebiliriz)
$adres_sorgu = $db->prepare("SELECT * FROM adresler WHERE kullanici_id = :kullanici_id LIMIT 1");
$adres_sorgu->execute([':kullanici_id' => $kullanici_id]);
$adres = $adres_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$adres) {
    $_SESSION['hata'] = "Sipariş vermek için lütfen önce bir teslimat adresi ekleyin.";
    header("Location: sepet.php"); // veya profil.php
    exit();
}

$adres_id = $adres['adres_id'];

// Sepetteki ürünleri çek
$sorgu = $db->prepare("SELECT sepetogeleri.*, urunler.fiyat, urunler.satici_id FROM sepetogeleri JOIN urunler ON sepetogeleri.urun_id = urunler.urun_id WHERE sepetogeleri.kullanici_id = :kullanici_id");
$sorgu->execute([':kullanici_id' => $kullanici_id]);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

if (empty($urunler)) {
    $_SESSION['hata'] = "Sepetiniz boş.";
    header("Location: sepet.php");
    exit();
}

try {
    $db->beginTransaction();

    foreach ($urunler as $urun) {
        $satici_id = $urun['satici_id'];
        $toplam_tutar = $urun['adet'] * $urun['fiyat'];

        // Sipariş oluştur
        $siparis = $db->prepare("INSERT INTO siparisler (satici_id, kullanici_id, adres_id, durum, toplam_tutar) 
                                 VALUES (:satici_id, :kullanici_id, :adres_id, 'Hazırlanıyor', :toplam_tutar)");
        $siparis->execute([
            ':satici_id' => $satici_id,
            ':kullanici_id' => $kullanici_id,
            ':adres_id' => $adres_id,
            ':toplam_tutar' => $toplam_tutar
        ]);

        $siparis_id = $db->lastInsertId();

        // Sipariş öğesi ekle
        $siparis_oge = $db->prepare("INSERT INTO siparisogeleri (siparis_id, urun_id, adet, birim_fiyat) VALUES (:siparis_id, :urun_id, :adet, :birim_fiyat)");
        $siparis_oge->execute([
            ':siparis_id' => $siparis_id,
            ':urun_id' => $urun['urun_id'],
            ':adet' => $urun['adet'],
            ':birim_fiyat' => $urun['fiyat']
        ]);

        // Stok azalt
        $stok_guncelle = $db->prepare("UPDATE urunler SET stok = stok - :adet WHERE urun_id = :urun_id");
        $stok_guncelle->execute([
            ':adet' => $urun['adet'],
            ':urun_id' => $urun['urun_id']
        ]);
    }

    // Sepeti temizle
    $temizle = $db->prepare("DELETE FROM sepetogeleri WHERE kullanici_id = :kullanici_id");
    $temizle->execute([':kullanici_id' => $kullanici_id]);

    $db->commit();

    $_SESSION['basari'] = "Siparişiniz başarıyla oluşturuldu.";
    header("Location: sepet.php");
    exit();

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['hata'] = "Sipariş oluşturulurken bir hata oluştu: " . $e->getMessage();
    header("Location: sepet.php");
    exit();
}
?>
