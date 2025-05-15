<?php
session_start();
include '../baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: ../giris.php");
    exit();
}

if (!isset($_GET['urun_id'])) {
    echo "Ürün ID belirtilmedi.";
    exit();
}

$urun_id = $_GET['urun_id'];

// Ürünü getir
$urun_sorgu = $db->prepare("SELECT * FROM urunler WHERE urun_id = :urun_id AND satici_id = :satici_id");
$urun_sorgu->execute([':urun_id' => $urun_id, ':satici_id' => $_SESSION['giren']]);
$urun = $urun_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    echo "Ürün bulunamadı.";
    exit();
}

// Tüm kategorileri getir
$kategori_sorgu = $db->query("SELECT * FROM kategoriler", PDO::FETCH_ASSOC);
$kategoriler = $kategori_sorgu->fetchAll();

// Ürünün mevcut kategorilerini getir
$eski_kategori_sorgu = $db->prepare("SELECT kategori_id FROM urun_kategoriler WHERE urun_id = :urun_id");
$eski_kategori_sorgu->execute([':urun_id' => $urun_id]);
$eski_kategoriler = $eski_kategori_sorgu->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];
    $secilen_kategoriler = $_POST['kategoriler'] ?? [];

    // Resim işlemleri
    $resim_url = $urun['resim_url'];
    if (isset($_POST['resim_sil']) && $resim_url) {
        $resim_path = "img/" . $resim_url;
        if (file_exists($resim_path)) {
            unlink($resim_path);
        }
        $resim_url = null;
    }

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] === 0) {
        $dosyaAdi = basename($_FILES["resim"]["name"]);
        $uzanti = strtolower(pathinfo($dosyaAdi, PATHINFO_EXTENSION));
        $yeniDosyaAdi = uniqid() . "." . $uzanti;
        $hedefKlasor = "img/" . $yeniDosyaAdi;

        if (in_array($uzanti, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["resim"]["tmp_name"], $hedefKlasor)) {
                $resim_url = $yeniDosyaAdi;
            }
        }
    }

    // Ürünü güncelle
    $guncelle = $db->prepare("UPDATE urunler SET ad = :ad, aciklama = :aciklama, fiyat = :fiyat, stok = :stok, resim_url = :resim_url WHERE urun_id = :urun_id AND satici_id = :satici_id");
    $guncelle->execute([
        ':ad' => $ad,
        ':aciklama' => $aciklama,
        ':fiyat' => $fiyat,
        ':stok' => $stok,
        ':resim_url' => $resim_url,
        ':urun_id' => $urun_id,
        ':satici_id' => $_SESSION['giren']
    ]);

    // Kategoriler değişti mi kontrol et
    $degisti = array_diff($secilen_kategoriler, $eski_kategoriler) || array_diff($eski_kategoriler, $secilen_kategoriler);

    if ($degisti) {
        // Eski eşleşmeleri sil
        $db->prepare("DELETE FROM urun_kategoriler WHERE urun_id = :urun_id")->execute([':urun_id' => $urun_id]);

        // Yenilerini ekle
        $kategori_ekle = $db->prepare("INSERT INTO urun_kategoriler (urun_id, kategori_id) VALUES (:urun_id, :kategori_id)");
        foreach ($secilen_kategoriler as $kategori_id) {
            $kategori_ekle->execute([
                ':urun_id' => $urun_id,
                ':kategori_id' => $kategori_id
            ]);
        }
    }

    header("Location: saticiindex.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container { max-width: 700px; margin: 50px auto; }
        a{
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">Satıcı Paneli</div>
    <div class="flex gap-4">
        <a href="saticiindex.php" class="text-gray-700 hover:text-black">Panel</a>
        <a href="../cikis.php" class="text-gray-700 hover:text-black">Çıkış Yap</a>
    </div>
</nav>

<div class="form-container bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-center text-xl font-bold mb-4">Ürün Düzenle</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block font-semibold">Ürün Adı</label>
            <input type="text" name="ad" class="w-full p-2 border rounded" value="<?= htmlspecialchars($urun['ad']) ?>" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Açıklama</label>
            <textarea name="aciklama" rows="3" class="w-full p-2 border rounded" required><?= htmlspecialchars($urun['aciklama']) ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Fiyat (₺)</label>
            <input type="number" step="0.01" name="fiyat" class="w-full p-2 border rounded" value="<?= $urun['fiyat'] ?>" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Stok</label>
            <input type="number" name="stok" class="w-full p-2 border rounded" value="<?= $urun['stok'] ?>" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Kategoriler</label>
            <select name="kategoriler[]" multiple class="w-full p-2 border rounded">
                <?php foreach ($kategoriler as $kategori): ?>
                    <option value="<?= $kategori['kategori_id'] ?>" <?= in_array($kategori['kategori_id'], $eski_kategoriler) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategori['ad']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-gray-500">Ctrl (veya Cmd) tuşuna basılı tutarak çoklu seçim yapabilirsiniz.</small>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Ürün Görseli</label>
            <?php if ($urun['resim_url']): ?>
                <div class="mb-2">
                    <img src="img/<?= $urun['resim_url'] ?>" alt="Ürün Resmi" style="max-height: 150px;">
                    <div class="form-check mt-1">
                        <input type="checkbox" name="resim_sil" class="form-check-input" id="resimSil">
                        <label class="form-check-label" for="resimSil">Mevcut resmi sil</label>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" name="resim" class="w-full p-2 border rounded" accept="image/*">
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Güncelle</button>
    </form>
</div>
</body>
</html>
