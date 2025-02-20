<?php
session_start();

// Temel Ayarlar
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('UPLOAD_PATH', SITE_ROOT . '/uploads/');

// Veritabanı Ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'pide_firma');
define('DB_USER', 'root');
define('DB_PASS', '');

// Hata Gösterimi Açık (Geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);