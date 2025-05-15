<?php
session_start();
include '../baglanti.php';

if (!isset($_SESSION['giren'])) {
    header("Location: ../giris.php");
    exit;
}

if (!isset($_GET['urun_id']) || !is_numeric($_GET['urun_id'])) {
    header("Location: saticiindex.php");
    exit;
}

$urun_id = intval($_GET['urun_id']);
$kullanici_id = $_SESSION['giren'];

// Ürünü sadece kendi ürünü olan satıcı silebilir
$yorumsilSorgu = $db->prepare("DELETE FROM urunyorumlari WHERE urun_id = :urun_id");
$yorumsilSorgu->execute([
    ':urun_id' => $urun_id,
]);
$silSorgu = $db->prepare("DELETE FROM urunler WHERE urun_id = :urun_id AND satici_id = :satici_id");
$silSorgu->execute([
    ':urun_id' => $urun_id,
    ':satici_id' => $kullanici_id
]);

// İlgili kategorileri de sil
$db->prepare("DELETE FROM urun_kategoriler WHERE urun_id = :urun_id")->execute([':urun_id' => $urun_id]);
header("Location: saticiindex.php");
exit;
?>
