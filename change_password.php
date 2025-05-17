<?php
// change_password.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$error = $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Eski şifreyi al
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $hashed = $stmt->get_result()->fetch_assoc()['password'];

    if (!password_verify($current, $hashed)) {
        $error = 'Mevcut şifre hatalı!';
    } elseif (strlen($new) < 6) {
        $error = 'Yeni şifre en az 6 karakter olmalı.';
    } elseif ($new !== $confirm) {
        $error = 'Yeni şifreler uyuşmuyor.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param('si', $newHash, $user_id);
        if ($upd->execute()) {
            $msg = 'Şifre başarıyla güncellendi.';
        } else {
            $error = 'Hata: ' . $upd->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Şifre Değiştir</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Şifre Değiştir</h2>
  <?php if ($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
  <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

  <form method="post">
    Mevcut Şifre:<br>
    <input type="password" name="current_password" required><br><br>
    Yeni Şifre:<br>
    <input type="password" name="new_password" required><br><br>
    Yeni Şifre (Tekrar):<br>
    <input type="password" name="confirm_password" required><br><br>
    <button type="submit">Değiştir</button>
  </form>
  <p><a href="profile.php">Geri</a></p>
</body>
</html>
