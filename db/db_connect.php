<?php
// db/db_connect.php

// Bulunduğun dizinden bir üst klasöre çık ve config.php'yi dahil et
require_once __DIR__ . '/../config.php';

// Yeni bir MySQLi nesnesi oluştur
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    die('Veritabanı bağlantı hatası: ' . $conn->connect_error);
}

// (İsteğe bağlı) Karakter setini UTF-8 olarak ayarla
$conn->set_charset('utf8mb4');
?>
