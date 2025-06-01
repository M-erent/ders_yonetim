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
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Şifre Değiştir</title>
  <link rel="stylesheet" href="assets/css/style.css?v=2">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f7fa; margin: 0; }
    .topbar {
      background-color: #1f3c88;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar .title {
      font-size: 24px;
      font-weight: bold;
    }
    .topbar .back-btn {
      background-color: #e67e22;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .topbar .back-btn:hover {
      background-color: #d35400;
    }
    .container {
      max-width: 480px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #1f3c88;
      margin-bottom: 20px;
      text-align: center;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      font-weight: bold;
      margin-top: 12px;
      margin-bottom: 6px;
      color: #333;
    }
    input[type="password"] {
      padding: 10px;
      font-size: 15px;
      border: 1.5px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }
    input[type="password"]:focus {
      border-color: #1f3c88;
      outline: none;
    }
    button {
      margin-top: 24px;
      background-color: #1f3c88;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #16326f;
    }
    .msg {
      text-align: center;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .msg.success {
      color: green;
    }
    .msg.error {
      color: red;
    }
    p.back-link {
      text-align: center;
      margin-top: 25px;
    }
    p.back-link a {
      color: #1f3c88;
      font-weight: bold;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    p.back-link a:hover {
      color: #e67e22;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="title">Şifre Değiştir</div>
  <a href="Profil.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if ($msg): ?><p class="msg success"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="msg error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post" novalidate>
    <label for="current_password">Mevcut Şifre:</label>
    <input type="password" id="current_password" name="current_password" required>

    <label for="new_password">Yeni Şifre:</label>
    <input type="password" id="new_password" name="new_password" required>

    <label for="confirm_password">Yeni Şifre (Tekrar):</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <button type="submit">Değiştir</button>
  </form>
</div>

</body>
</html>
