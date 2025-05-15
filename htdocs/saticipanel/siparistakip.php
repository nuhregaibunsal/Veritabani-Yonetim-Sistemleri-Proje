<?php
session_start();
include "../baglanti.php";

$kullanici_id = $_SESSION['giren'];

// Sadece satıcı erişebilir
$yetkisorgu = $db->prepare("SELECT satici FROM kullanicilar WHERE kullanici_id = :id");
$yetkisorgu->execute([':id' => $kullanici_id]);
$kullanici = $yetkisorgu->fetch(PDO::FETCH_ASSOC);
if ($kullanici['satici'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Durumu güncelle (Kargoya Teslim Edildi butonuna basıldıysa)
if (isset($_POST['teslim_et']) && isset($_POST['siparis_id'])) {
    $siparis_id = $_POST['siparis_id'];

    // Sadece kendi siparişi ise güncellesin
    $kontrol = $db->prepare("SELECT * FROM siparisler WHERE siparis_id = :id AND satici_id = :satici");
    $kontrol->execute([':id' => $siparis_id, ':satici' => $kullanici_id]);
    if ($kontrol->rowCount() > 0) {
        $guncelle = $db->prepare("UPDATE siparisler SET durum = 'Tamamlandı' WHERE siparis_id = :id");
        $guncelle->execute([':id' => $siparis_id]);
    }
}

// Filtre belirleme
$filtre = $_GET['filtre'] ?? 'bekleyen';
$durum = $filtre === 'tamamlanan' ? 'Tamamlandı' : 'Hazırlanıyor';

// Siparişleri çek
$siparislerSorgu = $db->prepare("
    SELECT s.*, k.ad_soyad, a.adres_satiri1
    FROM siparisler s
    JOIN kullanicilar k ON s.kullanici_id = k.kullanici_id
    LEFT JOIN adresler a ON s.adres_id = a.adres_id
    WHERE s.satici_id = :satici_id AND s.durum = :durum
    ORDER BY s.siparis_id DESC
");
$siparislerSorgu->execute([':satici_id' => $kullanici_id, ':durum' => $durum]);
$siparisler = $siparislerSorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a{
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">Sipariş Takip</div>
    <div class="flex gap-4 items-center">
        <a href="saticiindex.php" class="text-gray-700 hover:text-black">Panel</a>
        <a href="../index.php" class="text-gray-700 hover:text-black">Market</a>
        <a href="../cikis.php" class="text-gray-700 hover:text-black">Çıkış</a>
    </div>
</nav>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Sipariş Takibi</h1>

    <!-- Filtreleme Butonları -->
    <div class="mb-6 flex gap-4">
        <a href="?filtre=bekleyen" class="px-4 py-2 rounded 
           <?php echo $filtre === 'bekleyen' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
            Bekleyen Siparişler
        </a>
        <a href="?filtre=tamamlanan" class="px-4 py-2 rounded 
           <?php echo $filtre === 'tamamlanan' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
            Tamamlanan Siparişler
        </a>
    </div>

    <?php if (count($siparisler) === 0): ?>
        <p>Seçilen kritere uygun sipariş bulunamadı.</p>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($siparisler as $siparis): ?>
                <div class="bg-white shadow p-4 rounded-lg relative">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-semibold">Sipariş ID: <?php echo $siparis['siparis_id']; ?></h2>
                        <span class="text-sm text-gray-600"><?php echo $siparis['siparis_tarihi']; ?></span>
                    </div>
                    <p><strong>Alıcı:</strong> <?php echo htmlspecialchars($siparis['ad_soyad']); ?></p>
                    <p><strong>Adres:</strong> <?php echo htmlspecialchars($siparis['adres_satiri1'] ?? 'Adres yok'); ?></p>
                    <p><strong>Durum:</strong> <?php echo htmlspecialchars($siparis['durum']); ?></p>
                    <p><strong>Toplam Tutar:</strong> ₺<?php echo number_format($siparis['toplam_tutar'], 2, ',', '.'); ?></p>

                    <!-- Sipariş ürünleri -->
                    <div class="mt-4">
                        <h3 class="font-semibold mb-2">Sipariş Ürünleri:</h3>
                        <ul class="list-disc pl-5 space-y-1">
                            <?php
                            $ogeSorgu = $db->prepare("SELECT so.*, u.ad FROM siparisogeleri so JOIN urunler u ON so.urun_id = u.urun_id WHERE so.siparis_id = :siparis_id");
                            $ogeSorgu->execute([':siparis_id' => $siparis['siparis_id']]);
                            $ogeler = $ogeSorgu->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($ogeler as $oge): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($oge['ad']); ?></strong> -
                                    Adet: <?php echo $oge['adet']; ?>,
                                    Fiyat: ₺<?php echo number_format($oge['birim_fiyat'], 2, ',', '.'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Sadece Bekleyen Siparişlerde Buton Göster -->
                    <?php if ($filtre === 'bekleyen'): ?>
                        <form method="POST" class="mt-4 text-right">
                            <input type="hidden" name="siparis_id" value="<?php echo $siparis['siparis_id']; ?>">
                            <button type="submit" name="teslim_et" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Kargoya Teslim Edildi
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
