<?php
session_start(); // Oturum başlatıyoruz
include 'baglanti.php';
// Onay kodunu girmemiş bir kullanıcı, bu sayfaya erişemez.
if (!isset($_GET['email']) || !isset($_SESSION['onay_email'])) {
    // Email parametresi veya session'da onay email değeri yoksa, sayfaya erişim yasak.
    header("Location: giris.php"); // Giriş sayfasına yönlendir
    exit();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $onay_kodu_girilen = $_POST["onay_kodu"];

    // Veritabanından kullanıcıyı al
    $sorgu = $db->prepare("SELECT * FROM Kullanicilar WHERE eposta = :email");
    $sorgu->execute([':email' => $email]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        // Eğer kullanıcı mevcutsa ve onay kodu doğruysa
        if ($onay_kodu_girilen == $kullanici['kullaniciOnay']) {
            // Onay başarılı, kullanıcıyı 'approved' olarak işaretle
            $guncelle = $db->prepare("UPDATE Kullanicilar SET kullaniciOnay = NULL WHERE eposta = :email");
            $guncelle->execute([':email' => $email]);

            // Session başlat ve onaylı kullanıcının oturumunu aç
            $_SESSION['onay_email'] = $email; // Onaylı emaili session'a kaydet
            header("Location: giris.php"); // Giriş sayfasına yönlendir
            exit();
        } else {
            $hata = "Onay kodu hatalı!";
        }
    } else {
        $hata = "Kullanıcı bulunamadı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Onay Kodu - Satıcı Kaydı</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
  </nav>

  <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-center">Satıcı Onayı</h2>
    <p class="mb-4 text-sm text-gray-600 text-center">Satıcı olarak devam etmek için onay kodunu girin.</p>

    <?php if (isset($hata)): ?>
      <p class="text-red-600 text-center mb-4 font-semibold"><?= $hata ?></p>
    <?php endif; ?>

    <form action="onay.php" method="POST">
      <div class="mb-4">
        <label class="block font-semibold">Onay Kodu</label>
        <input type="text" name="onay_kodu" class="w-full p-2 border rounded" required>
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Kaydı Tamamla</button>
    </form>
  </div>
</body>
</html>
