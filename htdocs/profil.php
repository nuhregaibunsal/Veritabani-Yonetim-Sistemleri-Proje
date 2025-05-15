<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = $_SESSION['giren'];

$mesaj = $_SESSION['mesaj'] ?? null;
$mesaj_turu = $_SESSION['mesaj_turu'] ?? null;
unset($_SESSION['mesaj'], $_SESSION['mesaj_turu']);

// Kullanıcı bilgilerini al
$kullaniciSorgu = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_id = :id");
$kullaniciSorgu->execute([':id' => $kullanici_id]);
$kullanici = $kullaniciSorgu->fetch(PDO::FETCH_ASSOC);

if (isset($_SESSION['giren'])) {
    $sorgu2 = $db->prepare("SELECT * FROM Kullanicilar WHERE kullanici_id = :id");
    $sorgu2->execute([':id' => $_SESSION['giren']]);
    $kullanici2 = $sorgu2->fetch(PDO::FETCH_ASSOC);
}
// Adres bilgisi al
$adresSorgu = $db->prepare("SELECT * FROM adresler WHERE kullanici_id = :id LIMIT 1");
$adresSorgu->execute([':id' => $kullanici_id]);
$adres = $adresSorgu->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Profilim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
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

    <div class="container mx-auto py-6 px-4 max-w-4xl">

        <h1 class="text-3xl font-bold mb-6">Profil Bilgileri</h1>

        <?php if ($mesaj): ?>
            <div class="alert <?= $mesaj_turu === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($mesaj) ?>
            </div>
        <?php endif; ?>

        <form action="profil_guncelle.php" method="POST" class="bg-white shadow rounded-lg p-6 space-y-4" onsubmit="return validateForm();">
            <h2 class="text-xl font-semibold">Kişisel Bilgiler</h2>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="ad_soyad" class="form-label">Ad Soyad</label>
                    <input id="ad_soyad" type="text" name="ad_soyad" class="form-control" value="<?= htmlspecialchars($kullanici['ad_soyad']) ?>" required>
                </div>
                <div>
                    <label for="e_posta" class="form-label">E-posta</label>
                    <input id="e_posta" type="email" name="e_posta" class="form-control" value="<?= htmlspecialchars($kullanici['eposta']) ?>" required>
                </div>
                <div>
                    <label for="telefon" class="form-label">Telefon</label>
                    <input id="telefon" type="tel" name="telefon" class="form-control" value="<?= htmlspecialchars($kullanici['telefon']) ?>" required pattern="^\+?[0-9\s\-]{7,15}$" title="Geçerli bir telefon numarası giriniz.">
                </div>
            </div>

            <h2 class="text-xl font-semibold pt-6">Adres Bilgileri</h2>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="adres_satiri1" class="form-label">Adres Satırı 1</label>
                    <input id="adres_satiri1" type="text" name="adres_satiri1" class="form-control" value="<?= htmlspecialchars($adres['adres_satiri1'] ?? '') ?>">
                </div>
                <div>
                    <label for="adres_satiri2" class="form-label">Adres Satırı 2</label>
                    <input id="adres_satiri2" type="text" name="adres_satiri2" class="form-control" value="<?= htmlspecialchars($adres['adres_satiri2'] ?? '') ?>">
                </div>
                <div>
                    <label for="sehir" class="form-label">Şehir</label>
                    <input id="sehir" type="text" name="sehir" class="form-control" value="<?= htmlspecialchars($adres['sehir'] ?? '') ?>">
                </div>
                <div>
                    <label for="posta_kodu" class="form-label">Posta Kodu</label>
                    <input id="posta_kodu" type="text" name="posta_kodu" class="form-control" value="<?= htmlspecialchars($adres['posta_kodu'] ?? '') ?>">
                </div>
                <div>
                    <label for="ulke" class="form-label">Ülke</label>
                    <input id="ulke" type="text" name="ulke" class="form-control" value="<?= htmlspecialchars($adres['ulke'] ?? '') ?>">
                </div>
            </div>

            <h2 class="text-xl font-semibold pt-6">Şifre Değiştir</h2>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="yeni_sifre" class="form-label">Yeni Şifre</label>
                    <input id="yeni_sifre" type="password" name="yeni_sifre" class="form-control" minlength="6" placeholder="Yeni şifreniz (en az 6 karakter)">
                </div>
                <div>
                    <label for="yeni_sifre_tekrar" class="form-label">Yeni Şifre (Tekrar)</label>
                    <input id="yeni_sifre_tekrar" type="password" name="yeni_sifre_tekrar" class="form-control" minlength="6" placeholder="Yeni şifrenizi tekrar girin">
                </div>
            </div>

            <button type="submit" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Güncelle</button>
        </form>
    </div>

    <script>
        function validateForm() {
            const yeniSifre = document.getElementById('yeni_sifre').value;
            const yeniSifreTekrar = document.getElementById('yeni_sifre_tekrar').value;

            if (yeniSifre || yeniSifreTekrar) {
                if (yeniSifre.length < 6) {
                    alert('Yeni şifre en az 6 karakter olmalı.');
                    return false;
                }
                if (yeniSifre !== yeniSifreTekrar) {
                    alert('Şifreler eşleşmiyor.');
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>
