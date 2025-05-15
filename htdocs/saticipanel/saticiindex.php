<?php
session_start();
include "../baglanti.php";
$kullanici_id = $_SESSION['giren'];
$yetkisorgu = $db->prepare("SELECT satici as satici FROM kullanicilar WHERE kullanici_id = :id");
$yetkisorgu->execute([':id' => $kullanici_id]);
$yetkisorgu = $yetkisorgu->fetch(PDO::FETCH_ASSOC);
if ($yetkisorgu['satici']!=1) {
    header("Location: ../index.php");
    exit;
};



// 1. Toplam sipariş
$siparisSorgu = $db->prepare("SELECT COUNT(*) as toplam_siparis FROM siparisler WHERE satici_id = :id AND durum = :istenen");
$siparisSorgu->execute([':id' => $kullanici_id, ':istenen'=>"Hazırlanıyor"]);
$siparis = $siparisSorgu->fetch(PDO::FETCH_ASSOC)['toplam_siparis'] ?? 0;

// 2. Toplam ürün
$urunSorgu = $db->prepare("SELECT COUNT(*) as toplam_urun FROM urunler WHERE satici_id = :id");
$urunSorgu->execute([':id' => $kullanici_id]);
$urun = $urunSorgu->fetch(PDO::FETCH_ASSOC)['toplam_urun'] ?? 0;

// 3. Toplam satış tutarı
$satisSorgu = $db->prepare("SELECT SUM(toplam_tutar) as toplam_satis FROM siparisler WHERE satici_id = :id");
$satisSorgu->execute([':id' => $kullanici_id]);
$satis = $satisSorgu->fetch(PDO::FETCH_ASSOC)['toplam_satis'] ?? 0;

// Ürün listesi ve arama
$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$satici_id = $_SESSION['giren'];

if ($arama != "") {
    $sorgu = $db->prepare("SELECT * FROM urunler WHERE satici_id = :satici_id AND ad LIKE :arama");
    $sorgu->execute([
        ':satici_id' => $satici_id,
        ':arama' => '%' . $arama . '%'
    ]);
} else {
    $sorgu = $db->prepare("SELECT * FROM urunler WHERE satici_id = :satici_id");
    $sorgu->execute([':satici_id' => $satici_id]);
}

$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satıcı Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a {
            text-decoration: none;
        }
        .search-and-add-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">Satıcı Paneli</div>
    <div class="flex gap-4 items-center">
        <?php
        $sorgu2 = $db->prepare("SELECT * FROM Kullanicilar WHERE kullanici_id = :id");
        $sorgu2->execute([':id' => $_SESSION['giren']]);
        $kullanici2 = $sorgu2->fetch(PDO::FETCH_ASSOC);
        ?>
        <a href="../profil.php" class="text-gray-700 hover:text-black"><?php echo htmlspecialchars($kullanici2['ad_soyad']); ?></a>
        <a href="../sepet.php" class="text-gray-700 hover:text-black">Sepetim</a>
        <a href="../index.php" class="text-gray-700 hover:text-black">Market</a>
        <a href="../cikis.php" class="text-gray-700 hover:text-black">Çıkış</a>
    </div>
</nav>

<div class="container mx-auto px-4 py-6">

    <!-- İstatistikler -->
    <div class="flex gap-4 mb-6">
        <div class="bg-white shadow p-4 rounded-lg w-1/3 cursor-pointer hover:bg-gray-100">
            <a href="siparistakip.php">
                <h3 class="font-semibold">Siparişler</h3>
                <p class="text-2xl"><?php echo $siparis; ?></p>
            </a>
        </div>

        <div class="bg-white shadow p-4 rounded-lg w-1/3">
            <h3 class="font-semibold">Toplam Ürün</h3>
            <p class="text-2xl"><?php echo $urun; ?></p>
        </div>
        <div class="bg-white shadow p-4 rounded-lg w-1/3">
            <h3 class="font-semibold">Toplam Satış (₺)</h3>
            <p class="text-2xl"><?php echo number_format($satis, 2, ',', '.'); ?></p>
        </div>
    </div>

    <!-- Arama ve Yeni Ürün Ekle Butonunun Yan Yana Olacağı Kısım -->
    <div class="search-and-add-container mb-6">
        <!-- Ürün Arama Kutusu -->
        <form method="GET" class="flex w-1/2">
            <input type="text" name="arama" placeholder="Ürün adı ara..." class="border rounded-l px-3 py-2 focus:outline-none w-full" value="<?php echo isset($_GET['arama']) ? htmlspecialchars($_GET['arama']) : ''; ?>">
            <button type="submit" class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700">Ara</button>
        </form>

        <!-- Yeni Ürün Ekle Butonu -->
        <a href="urun_ekle.php" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">Yeni Ürün Ekle</a>
    </div>

    <!-- Ürün Listeleme -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($urunler as $urun) { ?>
            <div class="bg-white rounded-lg shadow p-4">
                <img src="<?php echo htmlspecialchars($urun['resim_yolu'] ?? 'https://via.placeholder.com/300x200'); ?>" class="w-full h-48 object-cover mb-2 rounded">
                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($urun['ad']); ?></h3>
                <p class="text-red-600 font-semibold">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
                <p class="text-sm">Stok: <?php echo htmlspecialchars($urun['stok']); ?></p>
                <p class="text-sm"><?php echo $urun['aktif'] ? 'Aktif' : 'Pasif'; ?></p>
                <a href="urun_duzenle.php?urun_id=<?php echo $urun['urun_id']; ?>" class="text-blue-600">Düzenle</a> |
                <a href="urun_sil.php?urun_id=<?php echo $urun['urun_id']; ?>" class="text-red-600" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
            </div>
        <?php } ?>
    </div>

</div>

</body>
</html>
