<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = $_SESSION['giren'];

if (isset($_GET['sil'])) {
    $sil_id = $_GET['sil'];
    $sil = $db->prepare("DELETE FROM sepetogeleri WHERE sepet_oge_id = :id AND kullanici_id = :kullanici_id");
    $sil->execute([':id' => $sil_id, ':kullanici_id' => $kullanici_id]);
    header("Location: sepet.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ekle"])) {
    $urun_id = $_POST["urun_id"];
    $adet = max(1, intval($_POST["adet"]));

    $sorgu = $db->prepare("SELECT * FROM sepetogeleri WHERE kullanici_id = :kullanici_id AND urun_id = :urun_id");
    $sorgu->execute([':kullanici_id' => $kullanici_id, ':urun_id' => $urun_id]);
    $urun = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($urun) {
        $guncelle = $db->prepare("UPDATE sepetogeleri SET adet = adet + :adet WHERE sepet_oge_id = :sepet_oge_id");
        $guncelle->execute([':adet' => $adet, ':sepet_oge_id' => $urun['sepet_oge_id']]);
    } else {
        $ekle = $db->prepare("INSERT INTO sepetogeleri (kullanici_id, urun_id, adet) VALUES (:kullanici_id, :urun_id, :adet)");
        $ekle->execute([':kullanici_id' => $kullanici_id, ':urun_id' => $urun_id, ':adet' => $adet]);
    }

    header("Location: sepet.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["urun_id"]) && isset($_POST["adet"])) {
        $urun_id = $_POST["urun_id"];
        $adet = max(1, intval($_POST["adet"]));

        $sorgu = $db->prepare("SELECT * FROM sepetogeleri JOIN urunler ON sepetogeleri.urun_id = urunler.urun_id WHERE sepetogeleri.kullanici_id = :kullanici_id AND sepetogeleri.urun_id = :urun_id");
        $sorgu->execute([':kullanici_id' => $kullanici_id, ':urun_id' => $urun_id]);
        $urun = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($urun) {
            if ($adet > $urun['stok']) {
                $_SESSION['hata'] = "Stok yetersizdir. Maksimum {$urun['stok']} adet eklenebilir.";
                header("Location: sepet.php");
                exit();
            }

            $guncelle = $db->prepare("UPDATE sepetogeleri SET adet = :adet WHERE sepet_oge_id = :sepet_oge_id");
            $guncelle->execute([':adet' => $adet, ':sepet_oge_id' => $urun['sepet_oge_id']]);
        }

        header("Location: sepet.php");
        exit();
    }
}

$sorgu = $db->prepare("SELECT sepetogeleri.sepet_oge_id, sepetogeleri.adet, urunler.urun_id, urunler.ad, urunler.stok, urunler.fiyat, urunler.resim_url FROM sepetogeleri JOIN urunler ON sepetogeleri.urun_id = urunler.urun_id WHERE sepetogeleri.kullanici_id = :kullanici_id");
$sorgu->execute([':kullanici_id' => $kullanici_id]);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$toplam = 0;
foreach ($urunler as $urun) {
    $toplam += $urun['fiyat'] * $urun['adet'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sepetim</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function updateAdet(urunId, yeniAdet) {
            if (yeniAdet < 1) return;
            document.getElementById('adet_' + urunId).value = yeniAdet;
            document.getElementById('form_' + urunId).submit();
        }
    </script>
     
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    <div class="flex gap-4">
        <a href="index.php" class="text-gray-700 hover:text-black">Market</a>
        <a href="sepet.php" class="text-gray-700 hover:text-black">Sepetim</a>
        <a href="cikis.php" class="text-gray-700 hover:text-black">Çıkış Yap</a>
    </div>
</nav>

<div class="max-w-4xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Sepetim</h1>

    <?php if (isset($_SESSION['hata'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php echo $_SESSION['hata']; unset($_SESSION['hata']); ?>
        </div>
    <?php endif; ?>

    <?php if (count($urunler) === 0): ?>
        <div class="text-center bg-white p-6 shadow rounded-lg">
            <p class="text-lg">Sepetinizde ürün yok.</p>
            <a href="index.php" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700">Alışverişe Devam Et</a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($urunler as $urun): ?>
                <div class="bg-white rounded-lg shadow-md p-6 flex flex-col mb-4 relative">
                    
                    <!-- Adet Kontrolü Sağ Üstte -->
                    <form action="sepet.php" method="POST" id="form_<?php echo $urun['urun_id']; ?>" class="absolute top-4 right-4 flex items-center gap-2">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['urun_id']; ?>">
                        <button type="button" onclick="updateAdet(<?php echo $urun['urun_id']; ?>, <?php echo $urun['adet'] - 1; ?>)" class="bg-gray-300 px-2 py-1 rounded hover:bg-gray-400" <?php echo ($urun['adet'] <= 1) ? 'disabled' : ''; ?>>-</button>
                        <input type="number" id="adet_<?php echo $urun['urun_id']; ?>" name="adet" value="<?php echo $urun['adet']; ?>" min="1" class="w-12 text-center p-1 border rounded" readonly>
                        <button type="button" onclick="updateAdet(<?php echo $urun['urun_id']; ?>, <?php echo $urun['adet'] + 1; ?>)" class="bg-gray-300 px-2 py-1 rounded hover:bg-gray-400">+</button>
                    </form>

                    <!-- Ürün Bilgileri -->
                    <div class="flex items-center gap-4">
                        <div class="flex justify-center items-center w-32 h-32">
                            <img src="saticipanel/<?php echo htmlspecialchars($urun['resim_url']); ?>" alt="<?php echo htmlspecialchars($urun['ad']); ?>" class="object-cover max-h-full rounded">
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($urun['ad']); ?></h2>
                            <p class="text-sm text-gray-600">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
                            <p class="text-sm text-gray-500">Stok: <?php echo $urun['stok']; ?></p>
                        </div>
                    </div>

                    <!-- Alt kısım -->
                    <div class="flex justify-between items-center mt-4">
                        <a href="sepet.php?sil=<?php echo $urun['sepet_oge_id']; ?>" class="text-red-500 hover:underline">Sepetten Kaldır</a>
                        <div class="font-semibold">
                            Toplam: ₺<?php echo number_format($urun['fiyat'] * $urun['adet'], 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Genel Toplam -->
        <div class="mt-6">
            <div class="text-xl font-semibold mb-2">
                Genel Toplam: ₺<?php echo number_format($toplam, 2, ',', '.'); ?>
            </div>
            <div class="flex justify-between">
                <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Alışverişe Devam Et</a>
                <a href="siparis_tamamla.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Siparişi Tamamla</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
