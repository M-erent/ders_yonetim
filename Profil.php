<?php
// profile.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

// Kullanıcı bilgilerini alıyoruz.
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, role, profile_image FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Profil & Ayarlar</title>
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
      max-width: 500px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      text-align: center;
    }
    h2 {
      color: #1f3c88;
      margin-bottom: 20px;
    }
    .profile-img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #1f3c88;
      margin-bottom: 20px;
    }
    p {
      font-size: 16px;
      margin: 10px 0;
      color: #333;
    }
    .links a {
      color: #1f3c88;
      text-decoration: none;
      font-weight: bold;
      margin: 0 10px;
      transition: color 0.3s ease;
    }
    .links a:hover {
      color: #e67e22;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="title">Profil & Ayarlar</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Fotoğrafı" class="profile-img">
  <p><strong>İsim:</strong> <?= htmlspecialchars($user['name']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
  <p><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
  <p class="links">
    <a href="Profil_Duzenleme.php">Profili Düzenle</a> |
    <a href="Parola_Degiştirme.php">Şifre Değiştir</a>
  </p>
</div>

</body>
</html>
