<?php
include 'baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST["adsoyad"];
    $email = $_POST["email"];
    $dogum_tarihi = $_POST["dogum"];
    $telefon = $_POST["telefon"];
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT); // Şifreyi güvenli hale getir
    $rol = $_POST["rol"]; // "satici" veya "musteri"

    if ($rol === "satici") {
        $yetki = 1;
        $onay_kodu = rand(100000, 999999); // Rastgele 6 haneli kod
    } else {
        $yetki = 0;
        $onay_kodu = null;
    }
    
    // Veritabanına ekle
    try {
        $sorgu = $db->prepare("INSERT INTO Kullanicilar (`ad_soyad`, `eposta`, `sifre_hash`, `satici`, `kullaniciOnay`, `telefon`, `dogum_tarihi`) 
                               VALUES (:ad, :email, :sifre, :yetki, :onay_kodu, :telefon, :dogum_tarihi)");

        $sorgu->execute([
            ':ad' => $ad,
            ':email' => $email,
            ':dogum_tarihi' => $dogum_tarihi,
            ':telefon' => $telefon,
            ':sifre' => $sifre,
            ':yetki' => $yetki,
            ':onay_kodu' => $onay_kodu
        ]);

        // Eğer kullanıcı satıcıysa, onay sayfasına yönlendir
        if ($yetki == 1) {
            header("Location: onay.php?email=" . urlencode($email));
            exit();
        } else {
            echo "<p class='text-green-600'>Kayıt başarılı! Giriş yapabilirsiniz.</p>";
        }

    } catch (PDOException $e) {
        echo "<p class='text-red-600'>Hata: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Kayıt Ol - E-Ticaret</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    <div class="flex gap-4">
      <a href="index.php" class="text-gray-700 hover:text-black">Anasayfa</a>
      <a href="giris.php" class="text-gray-700 hover:text-black">Giriş Yap</a>
    </div>
  </nav>

  <div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-center">Kayıt Ol</h2>

    <?php if (!empty($hata)): ?>
      <p class="text-red-600 mb-4 font-semibold text-center"><?= $hata ?></p>
    <?php endif; ?>

    <form action="kayit.php" method="POST">
      <div class="mb-4">
        <label class="block font-semibold">Ad Soyad</label>
        <input type="text" name="adsoyad" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">E-posta</label>
        <input type="email" name="email" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Doğum Tarihi</label>
        <input type="date" name="dogum" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Telefon Numarası</label>
        <input type="tel" name="telefon" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Şifre</label>
        <input type="password" name="sifre" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Şifre Tekrar</label>
        <input type="password" name="sifre_tekrar" class="w-full p-2 border rounded" required>
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Rol</label>
        <select name="rol" class="w-full p-2 border rounded" required>
          <option value="musteri">Müşteri</option>
          <option value="satici">Satıcı</option>
        </select>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Devam Et</button>
    </form>
  </div>
</body>
</html>
