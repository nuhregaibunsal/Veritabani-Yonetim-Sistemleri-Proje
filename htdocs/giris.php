<?php
session_start(); // Oturum başlatıyoruz

include 'baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    // Veritabanından kullanıcıyı al
    $sorgu = $db->prepare("SELECT * FROM Kullanicilar WHERE eposta = :email");
    $sorgu->execute([':email' => $email]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
       if($kullanici['eposta'] = $_POST['email'] && password_verify($_POST['sifre'], $kullanici['sifre_hash'])){
            $_SESSION['giren'] = $kullanici['kullanici_id'];
            header("Location: index.php");
       }
    } 
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giriş Yap - E-Ticaret</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .form-container {
      max-width: 400px;
      margin: 50px auto;
    }
    a {
      text-decoration: none;
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Navbar -->
  <nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    <div class="flex gap-4">
      <a href="index.php" class="text-gray-700 hover:text-black">Anasayfa</a>
      <a href="kayit.php" class="text-gray-700 hover:text-black">Kayıt Ol</a>
    </div>
  </nav>

  <!-- Giriş Formu -->
  <div class="form-container bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-center text-xl font-bold mb-4">Giriş Yap</h2>
    
    <form action="giris.php" method="POST">
      <div class="mb-4">
        <label for="email" class="block font-semibold">E-posta</label>
        <input type="email" id="email" name="email" class="w-full p-2 border rounded" required>
      </div>

      <div class="mb-4">
        <label for="sifre" class="block font-semibold">Şifre</label>
        <input type="password" id="sifre" name="sifre" class="w-full p-2 border rounded" required>
      </div>

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Giriş Yap</button>
    </form>

    <p class="text-center mt-4">Hesabınız yok mu? <a href="kayit.php" class="text-blue-500 hover:underline">Kayıt Olun</a></p>
  </div>
</body>
</html>
