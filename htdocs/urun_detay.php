<?php
session_start();
include 'baglanti.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$urun_id = $_GET['id'];

// Ürün bilgilerini al
$sorgu = $db->prepare("SELECT u.*, uk.kategori_id FROM urunler u 
                      LEFT JOIN urun_kategoriler uk ON u.urun_id = uk.urun_id 
                      WHERE u.urun_id = :id");
$sorgu->execute([':id' => $urun_id]);
$urun = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    header("Location: index.php");
    exit();
}

// Ürün yorumlarını al
$sorgu_yorumlar = $db->prepare("SELECT k.ad_soyad, u.yorum_metni, u.puan, u.yorum_tarihi 
                                FROM urunyorumlari u 
                                LEFT JOIN Kullanicilar k ON u.kullanici_id = k.kullanici_id 
                                WHERE u.urun_id = :urun_id");
$sorgu_yorumlar->execute([':urun_id' => $urun_id]);
$yorumlar = $sorgu_yorumlar->fetchAll(PDO::FETCH_ASSOC);

// Giriş yapan kullanıcı
$kullanici2 = null;
if (isset($_SESSION['giren'])) {
    $sorgu2 = $db->prepare("SELECT * FROM Kullanicilar WHERE kullanici_id = :id");
    $sorgu2->execute([':id' => $_SESSION['giren']]);
    $kullanici2 = $sorgu2->fetch(PDO::FETCH_ASSOC);
}

// Sepete ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ekle"])) {
    $adet = max(1, intval($_POST["adet"]));

    $stok_sorgu = $db->prepare("SELECT stok FROM urunler WHERE urun_id = :urun_id");
    $stok_sorgu->execute([':urun_id' => $urun_id]);
    $urun_stok = $stok_sorgu->fetch(PDO::FETCH_ASSOC)['stok'];

    if ($urun_stok >= $adet) {
        $sorgu = $db->prepare("SELECT * FROM sepetogeleri WHERE kullanici_id = :kullanici_id AND urun_id = :urun_id");
        $sorgu->execute([':kullanici_id' => $_SESSION['giren'], ':urun_id' => $urun_id]);
        $varsa = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($varsa) {
            $guncelle = $db->prepare("UPDATE sepetogeleri SET adet = adet + :adet WHERE sepet_oge_id = :id");
            $guncelle->execute([':adet' => $adet, ':id' => $varsa['sepet_oge_id']]);
        } else {
            $ekle = $db->prepare("INSERT INTO sepetogeleri (kullanici_id, urun_id, adet) VALUES (:kullanici_id, :urun_id, :adet)");
            $ekle->execute([':kullanici_id' => $_SESSION['giren'], ':urun_id' => $urun_id, ':adet' => $adet]);
        }

        header("Location: sepet.php");
        exit();
    } else {
        echo "<script>alert('Stok yetersizdir!');</script>";
    }
}

// Yorum ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["yorum_ekle"])) {
    if (!isset($_SESSION['giren'])) {
        header("Location: giris.php");
        exit();
    }

    $yorum_metni = $_POST["yorum_metni"];
    $puan = $_POST["puan"];

    $ekle_yorum = $db->prepare("INSERT INTO urunyorumlari (kullanici_id, urun_id, yorum_metni, puan, yorum_tarihi) 
                                VALUES (:kullanici_id, :urun_id, :yorum_metni, :puan, NOW())");
    $ekle_yorum->execute([
        ':kullanici_id' => $_SESSION['giren'],
        ':urun_id' => $urun_id,
        ':yorum_metni' => $yorum_metni,
        ':puan' => $puan
    ]);

    header("Location: urun_detay.php?id=$urun_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($urun['ad']); ?> - Ürün Detay</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex justify-between items-center sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    <div class="flex gap-4">
        <?php if ($kullanici2): ?>
            <span class="text-gray-700"><?php echo htmlspecialchars($kullanici2['ad_soyad']); ?></span>
            <a href="index.php" class="hover:text-blue-600">Market</a>
            <?php if ($kullanici2['satici'] == 1): ?>
                <a href="saticipanel/saticiindex.php" class="hover:text-blue-600">Satıcı Paneli</a>
            <?php endif; ?>
            <a href="sepet.php" class="hover:text-blue-600">Sepetim</a>
            <a href="cikis.php" class="hover:text-red-600">Çıkış</a>
        <?php else: ?>
            <a href="giris.php" class="hover:text-blue-600">Giriş Yap</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container mx-auto mt-6 flex gap-6">
    <!-- Ürün Bilgileri -->
    <div class="w-2/3 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($urun['ad']); ?></h2>
        <img src="saticipanel/<?php echo $urun['resim_url']; ?>" class="w-full h-64 object-cover rounded mb-4">
        <p class="text-lg font-bold mb-2">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
        <p class="text-sm text-gray-600 mb-2">Stok: <?php echo $urun['stok']; ?> adet</p>

        <p class="mb-4"><?php echo htmlspecialchars($urun['aciklama']); ?></p>

        <?php if ($kullanici2): ?>
            <form method="POST" class="flex items-center gap-2 mb-4">
                <p id="stokUyari" class="text-red-500 hidden">Stok yetersiz! En fazla <?php echo $urun['stok']; ?> adet ekleyebilirsiniz.</p>
                <div class="flex items-center border rounded overflow-hidden">
                    <button type="button" onclick="azalt()" class="bg-gray-200 px-3 py-2">−</button>
                    <input type="number" name="adet" id="adet" value="1" min="1" max="<?php echo $urun['stok']; ?>" class="w-16 text-center outline-none" readonly>
                    <button type="button" onclick="arttir()" class="bg-gray-200 px-3 py-2">+</button>
                </div>
                <button type="submit" name="ekle" class="bg-blue-500 text-white px-4 py-2 rounded">Sepete Ekle</button>
            </form>
        <?php else: ?>
            <p class="text-blue-600"><a href="giris.php">Giriş yaparak sepetinize ekleyebilirsiniz.</a></p>
        <?php endif; ?>

        <h3 class="text-xl mt-6 mb-2">Yorumlar</h3>
        <?php if ($yorumlar): ?>
            <?php foreach ($yorumlar as $yorum): ?>
                <div class="border rounded p-3 mb-3 bg-gray-50">
                    <p><strong><?php echo htmlspecialchars($yorum['ad_soyad']); ?></strong> - <?php echo $yorum['yorum_tarihi']; ?></p>
                    <p><?php echo nl2br(htmlspecialchars($yorum['yorum_metni'])); ?></p>
                    <p class="text-sm text-yellow-600">Puan: <?php echo $yorum['puan']; ?>/5</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Henüz yorum yapılmamış.</p>
        <?php endif; ?>

        <?php if ($kullanici2): ?>
            <h3 class="text-xl mt-6 mb-2">Yorum Yapın</h3>
            <form method="POST">
                <textarea name="yorum_metni" rows="3" class="w-full border rounded p-2 mb-2" required></textarea>
                <select name="puan" class="w-full border rounded p-2 mb-2" required>
                    <option value="">Puan Seçin</option>
                    <option value="1">1 / 5</option>
                    <option value="2">2 / 5</option>
                    <option value="3">3 / 5</option>
                    <option value="4">4 / 5</option>
                    <option value="5">5 / 5</option>
                </select>
                <button type="submit" name="yorum_ekle" class="bg-green-500 text-white px-4 py-2 rounded">Yorum Gönder</button>
            </form>
        <?php else: ?>
            <p class="text-blue-600"><a href="giris.php">Yorum yapabilmek için giriş yapın.</a></p>
        <?php endif; ?>
    </div>

    <!-- Önerilen Ürünler -->
    <div class="w-1/3">
        <h3 class="text-xl font-semibold mb-4">Önerilen Ürünler</h3>
        <div class="grid gap-4">
            <?php
            $onerilen_sorgu = $db->prepare("SELECT u.urun_id, u.ad, u.fiyat, u.resim_url 
                                            FROM urunler u 
                                            LEFT JOIN urun_kategoriler uk ON u.urun_id = uk.urun_id 
                                            WHERE uk.kategori_id = :kategori_id AND u.urun_id != :current_id 
                                            ORDER BY RAND() LIMIT 3");
            $onerilen_sorgu->execute([':kategori_id' => $urun['kategori_id'], ':current_id' => $urun['urun_id']]);
            $onerilenler = $onerilen_sorgu->fetchAll(PDO::FETCH_ASSOC);

            foreach ($onerilenler as $o):
            ?>
            <div class="bg-white p-3 rounded shadow text-center">
                <img src="saticipanel/<?php echo $o['resim_url']; ?>" class="w-full h-40 object-cover rounded mb-2">
                <h4 class="font-semibold"><?php echo htmlspecialchars($o['ad']); ?></h4>
                <p class="text-sm text-gray-600">₺<?php echo number_format($o['fiyat'], 2, ',', '.'); ?></p>
                <a href="urun_detay.php?id=<?php echo $o['urun_id']; ?>" class="text-blue-500 text-sm">Detay</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    const adetInput = document.getElementById('adet');
    const maxStok = <?php echo $urun['stok']; ?>;
    const stokUyari = document.getElementById('stokUyari');

    function arttir() {
        let value = parseInt(adetInput.value);
        if (value < maxStok) {
            adetInput.value = value + 1;
            stokUyari.classList.add('hidden');
        } else {
            stokUyari.classList.remove('hidden');
        }
    }

    function azalt() {
        let value = parseInt(adetInput.value);
        if (value > 1) {
            adetInput.value = value - 1;
            stokUyari.classList.add('hidden');
        }
    }
</script>


</body>
</html>
