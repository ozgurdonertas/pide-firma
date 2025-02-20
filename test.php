<?php
require_once 'includes/config.php';

echo "SITE_URL: " . SITE_URL . "<br>";
echo "SITE_ROOT: " . SITE_ROOT . "<br>";
echo "UPLOAD_PATH: " . UPLOAD_PATH . "<br>";

try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Veritabanı bağlantısı başarılı!";
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
} 