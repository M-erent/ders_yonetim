<?php
// admin_olustur.php

try {
    $conn = new mysqli("localhost", "root", "", "ders_yonetim");
    if ($conn->connect_errno) {
        throw new Exception("Bağlantı hatası: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Yeni kullanıcının bilgileri:
    $yeni_kullanici_adi = "admin";
    $düz_metn_sifre      = "admin123"; // Dilediğiniz şifre

    // Şifreyi hash’le
    $hashlenmis_sifre = password_hash($düz_metn_sifre, PASSWORD_DEFAULT);

    // Veritabanına ekle
    $stmt = $conn->prepare("INSERT INTO yoneticiler (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $yeni_kullanici_adi, $hashlenmis_sifre);

    if ($stmt->execute()) {
        echo "Yeni yönetici eklendi:<br>";
        echo "  Kullanıcı Adı: <strong>$yeni_kullanici_adi</strong><br>";
        echo "  Şifre (düz metin): <strong>$düz_metn_sifre</strong><br>";
        echo "  Şifre Hash’i: <code>$hashlenmis_sifre</code>";
    } else {
        echo "Ekleme hatası: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?>
