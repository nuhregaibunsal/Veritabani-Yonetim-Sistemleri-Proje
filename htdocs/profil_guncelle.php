<?php
session_start();
include 'baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = $_SESSION['giren'];

function redirectWithMessage($mesaj, $tur = 'error') {
    $_SESSION['mesaj'] = $mesaj;
    $_SESSION['mesaj_turu'] = $tur;
    header("Location: profil.php");
    exit();
}

$ad_soyad = trim($_POST['ad_soyad']);
$e_posta = trim($_POST['e_posta']);
$telefon = trim($_POST['telefon']);
$adres_satiri1 = trim($_POST['adres_satiri1']);
$adres_satiri2 = trim($_POST['adres_satiri2']);
$sehir = trim($_POST['sehir']);
$posta_kodu = trim($_POST['posta_kodu']);
$ulke = trim($_POST['ulke']);

$yeni_sifre = $_POST['yeni_sifre'] ?? '';
$yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'] ?? '';

// Basit validasyonlar
if (empty($ad_soyad) || empty($e_posta) || empty($telefon)) {
    redirectWithMessage('Ad Soyad, E-posta ve Telefon zorunludur.');
}

if (!filter_var($e_posta, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('Geçerli bir e-posta adresi giriniz.');
}

if (!preg_match('/^\+?[0-9\s\-]{7,15}$/', $telefon)) {
    redirectWithMessage('Geçerli bir telefon numarası giriniz.');
}

if (!empty($yeni_sifre) || !empty($yeni_sifre_tekrar)) {
    if (strlen($yeni_sifre) < 6) {
        redirectWithMessage('Yeni şifre en az 6 karakter olmalı.');
    }
    if ($yeni_sifre !== $yeni_sifre_tekrar) {
        redirectWithMessage('Şifreler eşleşmiyor.');
    }
}

// Kullanıcı bilgilerini güncelle
$kullaniciGuncelle = $db->prepare("UPDATE kullanicilar SET ad_soyad = :ad, `eposta` = :eposta, telefon = :tel WHERE kullanici_id = :id");
$kullaniciGuncelle->execute([
    ':ad' => $ad_soyad,
    ':eposta' => $e_posta,
    ':tel' => $telefon,
    ':id' => $kullanici_id
]);

// Adres varsa güncelle, yoksa ekle
$adresKontrol = $db->prepare("SELECT * FROM adresler WHERE kullanici_id = :id");
$adresKontrol->execute([':id' => $kullanici_id]);
if ($adresKontrol->rowCount() > 0) {
    $adresGuncelle = $db->prepare("UPDATE adresler SET adres_satiri1 = :a1, adres_satiri2 = :a2, sehir = :sehir, posta_kodu = :pk, ulke = :ulke, ad_soyad = :ad, telefon = :tel WHERE kullanici_id = :id");
    $adresGuncelle->execute([
        ':a1' => $adres_satiri1,
        ':a2' => $adres_satiri2,
        ':sehir' => $sehir,
        ':pk' => $posta_kodu,
        ':ulke' => $ulke,
        ':ad' => $ad_soyad,
        ':tel' => $telefon,
        ':id' => $kullanici_id
    ]);
} else {
    $adresEkle = $db->prepare("INSERT INTO adresler (kullanici_id, ad_soyad, adres_satiri1, adres_satiri2, sehir, posta_kodu, ulke, telefon) VALUES (:id, :ad, :a1, :a2, :sehir, :pk, :ulke, :tel)");
    $adresEkle->execute([
        ':id' => $kullanici_id,
        ':ad' => $ad_soyad,
        ':a1' => $adres_satiri1,
        ':a2' => $adres_satiri2,
        ':sehir' => $sehir,
        ':pk' => $posta_kodu,
        ':ulke' => $ulke,
        ':tel' => $telefon
    ]);
}

// Şifre değiştirme
if (!empty($yeni_sifre)) {
    $sifre_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
    $sifreGuncelle = $db->prepare("UPDATE kullanicilar SET sifre_hash = :sifre WHERE kullanici_id = :id");
    $sifreGuncelle->execute([':sifre' => $sifre_hash, ':id' => $kullanici_id]);
}

redirectWithMessage('Profiliniz başarıyla güncellendi.', 'success');
