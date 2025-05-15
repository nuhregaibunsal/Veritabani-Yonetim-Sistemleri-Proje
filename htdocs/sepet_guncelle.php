<?php
session_start();
include 'baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["urun_id"]) && isset($_POST["adet"])) {
    $kullanici_id = $_SESSION['giren'];
    $urun_id = $_POST["urun_id"];
    $adet = max(1, intval($_POST["adet"])); // Adet 1'in altına düşmesin

    // Sepette ürün var mı?
    $sorgu = $db->prepare("SELECT * FROM sepetogeleri WHERE kullanici_id = :kullanici_id AND urun_id = :urun_id");
    $sorgu->execute([':kullanici_id' => $kullanici_id, ':urun_id' => $urun_id]);
    $urun = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($urun) {
        // Güncelleme işlemi
        $guncelle = $db->prepare("UPDATE sepetogeleri SET adet = :adet WHERE sepet_oge_id = :sepet_oge_id");
        $guncelle->execute([':adet' => $adet, ':sepet_oge_id' => $urun['sepet_oge_id']]);
        
        echo 'success'; // Başarılı işlem
    } else {
        echo 'error'; // Ürün bulunamadı
    }
}
?>
