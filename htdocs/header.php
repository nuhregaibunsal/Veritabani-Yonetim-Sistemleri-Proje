<?php session_start(); // Oturum başlatıyoruz ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>E-Ticaret</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <nav class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
    <div class="text-xl font-bold">E-Ticaret</div>
    
    <!-- Arama Çubuğu -->
    <div class="w-1/2">
      <input type="text" placeholder="Ürün Ara..." class="w-full p-2 border rounded">
    </div>

    <!-- Kullanıcı Menüsü -->
    <div class="flex gap-4">
      <?php if (isset($_SESSION['user_email'])): ?>
        <!-- Eğer kullanıcı giriş yaptıysa -->
        <div class="flex gap-4">
          <span class="font-semibold"><?= $_SESSION['user_ad'] ?></span> <!-- Kullanıcının adı -->
          <a href="sepet.php" class="text-gray-700 hover:text-black">
            <?php echo ($_SESSION['user_rol'] == 'satici') ? 'Satıcı Paneli' : 'Sepetim'; ?>
          </a>
          <a href="cikis.php" class="text-gray-700 hover:text-black">Çıkış Yap</a>
        </div>
      <?php else: ?>
        <!-- Eğer kullanıcı giriş yapmadıysa -->
        <a href="giris.php" class="text-gray-700 hover:text-black">Giriş Yap</a>
      <?php endif; ?>
    </div>
  </nav>
</body>
</html>
