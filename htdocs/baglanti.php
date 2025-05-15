<?php
$host = "localhost";
$dbname = "proje"; 
$username = "root";          
$password = "";              

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // PDO hata modu: Exception (hatalar daha okunaklı olur)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>
