<?php
session_start();
include 'baglanti.php';

$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$sirala = isset($_GET['sirala']) ? $_GET['sirala'] : '';

// Ürünleri çekmek
$sql = "SELECT * FROM urunler WHERE 1=1";
$params = [];

if ($arama != "") {
    $sql .= " AND ad LIKE :arama";
    $params[':arama'] = "%$arama%";
}

if ($kategori != "") {
    $sql .= " AND urun_id IN (SELECT urun_id FROM urun_kategoriler WHERE kategori_id = :kategori)";
    $params[':kategori'] = $kategori;
}

if ($sirala == "artan") {
    $sql .= " ORDER BY fiyat ASC";
} elseif ($sirala == "azalan") {
    $sql .= " ORDER BY fiyat DESC";
} elseif ($sirala == "yeni") {
    $sql .= " ORDER BY urun_id DESC";
}

$sorgu = $db->prepare($sql);
$sorgu->execute($params);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri çekmek
$sorgu_kategoriler = $db->prepare("SELECT * FROM kategoriler");
$sorgu_kategoriler->execute();
$kategoriler = $sorgu_kategoriler->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcı bilgisi
$kullanici2 = null;
if (isset($_SESSION['giren'])) {
    $sorgu2 = $db->prepare("SELECT * FROM Kullanicilar WHERE kullanici_id = :id");
    $sorgu2->execute([':id' => $_SESSION['giren']]);
    $kullanici2 = $sorgu2->fetch(PDO::FETCH_ASSOC);
}

// Sepete ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ekle"])) {
    $urun_id = $_POST["urun_id"];
    $adet = max(1, intval($_POST["adet"]));

    $stok_sorgu = $db->prepare("SELECT stok FROM urunler WHERE urun_id = :urun_id");
    $stok_sorgu->execute([':urun_id' => $urun_id]);
    $urun_stok = $stok_sorgu->fetch(PDO::FETCH_ASSOC)['stok'];

    if ($urun_stok >= $adet) {
        $sorgu = $db->prepare("SELECT * FROM sepetogeleri WHERE kullanici_id = :kullanici_id AND urun_id = :urun_id");
        $sorgu->execute([':kullanici_id' => $_SESSION['giren'], ':urun_id' => $urun_id]);
        $urun = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($urun) {
            $guncelle = $db->prepare("UPDATE sepetogeleri SET adet = adet + :adet WHERE sepet_oge_id = :sepet_oge_id");
            $guncelle->execute([':adet' => $adet, ':sepet_oge_id' => $urun['sepet_oge_id']]);
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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ana Sayfa - E-Ticaret</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .carousel-inner { height: 300px; }
        .carousel-inner img { height: 100%; width: 100%; object-fit: contain; }
        a { text-decoration: none; }
    </style>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    <div class="flex gap-4">
        <?php if ($kullanici2): ?>
            <a href="profil.php" class="text-gray-700 hover:text-black"><?php echo htmlspecialchars($kullanici2['ad_soyad']); ?></a>
            <?php if ($kullanici2['satici'] == 1): ?>
                <a href="saticipanel/saticiindex.php" class="text-gray-700 hover:text-black">Satıcı Paneli</a>
            <?php endif; ?>
            <a href="sepet.php" class="text-gray-700 hover:text-black">Sepetim</a>
            <a href="cikis.php" class="text-gray-700 hover:text-black">Çıkış Yap</a>
        <?php else: ?>
            <a href="giris.php" class="text-gray-700 hover:text-black">Giriş Yap</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Carousel -->
<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="saticipanel/img/68244946081db.png" class="d-block w-100" alt="Slider 1">
        </div>
        <div class="carousel-item">
            <img src="img/Badem.jpg" class="d-block w-100" alt="Slider 2">
        </div>
        <div class="carousel-item">
            <img src="img/Ekstra.jpg" class="d-block w-100" alt="Slider 3">
        </div>
    </div>
</div>

<!-- Filtre + Ürünler -->
<div class="flex px-8 py-4 gap-4">
    <div class="w-1/5">
        <form method="GET">
            <div class="mb-4">
                <label class="font-semibold">Arama</label>
                <input type="text" name="arama" placeholder="Ürün adı ara..." class="w-full p-2 border rounded" value="<?php echo htmlspecialchars($arama); ?>">
            </div>
            <div class="mb-4">
                <label class="font-semibold">Kategori</label>
                <select class="w-full p-2 border rounded" name="kategori">
                    <option value="">Tüm Ürünler</option>
                    <?php foreach ($kategoriler as $kategori_item): ?>
                        <option value="<?php echo $kategori_item['kategori_id']; ?>" <?php echo ($kategori_item['kategori_id'] == $kategori ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($kategori_item['ad']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="font-semibold">Sıralama</label>
                <select class="w-full p-2 border rounded" name="sirala">
                    <option value="">Varsayılan</option>
                    <option value="artan" <?php echo ($sirala == 'artan' ? 'selected' : ''); ?>>Fiyat Artan</option>
                    <option value="azalan" <?php echo ($sirala == 'azalan' ? 'selected' : ''); ?>>Fiyat Azalan</option>
                    <option value="yeni" <?php echo ($sirala == 'yeni' ? 'selected' : ''); ?>>Yeniden Eskiye</option>
                </select>
            </div>
            <div class="flex flex-col gap-2">
              <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Filtrele</button>
              <a href="index.php" class="w-full bg-gray-400 text-white py-2 rounded text-center hover:bg-gray-500">Filtreyi Temizle</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($urunler as $urun): ?>
            <div class="bg-white rounded-lg shadow p-4">
                <a href="urun_detay.php?id=<?php echo $urun['urun_id']; ?>">  <!-- ID parametresini düzelttim -->
                    <img src="saticipanel/<?php echo $urun['resim_url']; ?>" class="w-full h-48 object-cover mb-2 rounded" alt="Ürün Resmi">
                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($urun['ad']); ?></h3>
                </a>
                <p class="text-red-600 font-semibold">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
                <p class="text-sm">Stok: <?php echo htmlspecialchars($urun['stok']); ?></p>

                <?php if (isset($_SESSION['giren'])): ?>
                    <form action="index.php" method="POST" class="space-y-2">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['urun_id']; ?>">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="adetDegistir(this, -1, <?php echo $urun['stok']; ?>)" class="bg-gray-300 px-2 rounded">-</button>
                            <input type="number" name="adet" min="1" max="<?php echo $urun['stok']; ?>" value="1" class="w-16 text-center p-1 border rounded" required>
                            <button type="button" onclick="adetDegistir(this, 1, <?php echo $urun['stok']; ?>)" class="bg-gray-300 px-2 rounded">+</button>
                        </div>
                        <button type="submit" name="ekle" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Sepete Ekle</button>
                    </form>
                <?php else: ?>
                    <a href="giris.php" class="mt-2 w-full block bg-blue-500 text-white py-2 rounded hover:bg-blue-600 text-center">Giriş Yapın</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function adetDegistir(button, degisim, maxStok) {
    const input = button.parentElement.querySelector('input[name="adet"]');
    let adet = parseInt(input.value);
    if (isNaN(adet)) adet = 1;
    adet += degisim;
    if (adet < 1) adet = 1;
    if (adet > maxStok) adet = maxStok;
    input.value = adet;
}
</script>

</body>
</html>
