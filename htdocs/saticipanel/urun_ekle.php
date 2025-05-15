<?php
session_start();
include '../baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: ../giris.php");
    exit();
}

// Kategorileri çek
$kategori_sorgu = $db->query("SELECT * FROM kategoriler", PDO::FETCH_ASSOC);
$kategoriler = $kategori_sorgu->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];
    $resim_url = null;
    $kullanici_id = $_SESSION['giren'];
    $secilen_kategoriler = $_POST['kategoriler'] ?? [];

    // Resim yükleme
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] === 0) {
        $dosyaAdi = basename($_FILES["resim"]["name"]);
        $uzanti = strtolower(pathinfo($dosyaAdi, PATHINFO_EXTENSION));
        $yeniDosyaAdi = uniqid() . "." . $uzanti;
        $hedefKlasor = "img/" . $yeniDosyaAdi;

        if (in_array($uzanti, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["resim"]["tmp_name"], $hedefKlasor)) {
                $resim_url = "img/" .$yeniDosyaAdi;
            }
        }
    }

    // Ürün ekle
    $ekle = $db->prepare("INSERT INTO urunler (ad, aciklama, fiyat, stok, aktif, satici_id, resim_url) 
                          VALUES (:ad, :aciklama, :fiyat, :stok, 1, :kullanici_id, :resim_url)");
    $ekle->execute([
        ':ad' => $ad,
        ':aciklama' => $aciklama,
        ':fiyat' => $fiyat,
        ':stok' => $stok,
        ':kullanici_id' => $kullanici_id,
        ':resim_url' => $resim_url
    ]);

    $urun_id = $db->lastInsertId(); // eklenen ürünün ID’si

    // Kategorileri eşleştir
    $kategori_ekle = $db->prepare("INSERT INTO urun_kategoriler (urun_id, kategori_id) VALUES (:urun_id, :kategori_id)");
    foreach ($secilen_kategoriler as $kategori_id) {
        $kategori_ekle->execute([
            ':urun_id' => $urun_id,
            ':kategori_id' => $kategori_id
        ]);
    }

    header("Location: saticiindex.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ürün Ekle</title>
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

<!-- Navbar -->
<nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
  <div class="text-xl font-bold">Satıcı Paneli</div>
  <div class="flex gap-4">
    <a href="saticiindex.php" class="text-gray-700 hover:text-black">Panel</a>
    <a href="../cikis.php" class="text-gray-700 hover:text-black">Çıkış Yap</a>
  </div>
</nav>

<!-- Ürün Ekleme Formu -->
<div class="form-container bg-white p-6 rounded-lg shadow-md">
  <h2 class="text-center text-xl font-bold mb-4">Yeni Ürün Ekle</h2>
  <form method="POST" action="" enctype="multipart/form-data">
    <div class="mb-4">
      <label class="block font-semibold">Ürün Adı</label>
      <input type="text" name="ad" class="w-full p-2 border rounded" required>
    </div>

    <div class="mb-4">
      <label class="block font-semibold">Açıklama</label>
      <textarea name="aciklama" rows="3" class="w-full p-2 border rounded" required></textarea>
    </div>

    <div class="mb-4">
      <label class="block font-semibold">Fiyat (₺)</label>
      <input type="number" step="0.01" name="fiyat" class="w-full p-2 border rounded" required>
    </div>

    <div class="mb-4">
      <label class="block font-semibold">Stok</label>
      <input type="number" name="stok" class="w-full p-2 border rounded" required>
    </div>

    <div class="mb-4">
      <label class="block font-semibold">Kategori Seç (Birden Fazla Seçilebilir)</label>
      <select name="kategoriler[]" multiple class="w-full p-2 border rounded">
        <?php foreach ($kategoriler as $kategori): ?>
          <option value="<?= $kategori['kategori_id'] ?>"><?= htmlspecialchars($kategori['ad']) ?></option>
        <?php endforeach; ?>
      </select>
      <small class="text-gray-500">Ctrl (veya Cmd) tuşuna basılı tutarak çoklu seçim yapabilirsiniz.</small>
    </div>

    <div class="mb-4">
      <label class="block font-semibold">Ürün Görseli</label>
      <input type="file" name="resim" class="w-full p-2 border rounded" accept="image/*">
    </div>

    <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Ürünü Kaydet</button>
  </form>
</div>
</body>
</html>
