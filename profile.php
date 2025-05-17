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
<html>
<head>
  <meta charset="utf-8">
  <title>Profil & Ayarlar</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Profil fotoğrafı için stil */
    .profile-img {
      width: 150px;
      height: 150px;
      border-radius: 50%; /* Yuvarlak görünüm */
      object-fit: cover;  /* Resmin bozulmasını engeller */
      border: 2px solid #1f3c88;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <h2>Profil & Ayarlar</h2>
  <div>
    <!-- Profil fotoğrafı görüntüleniyor -->
    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Fotoğrafı" class="profile-img">
  </div>
  <p><strong>İsim:</strong> <?= htmlspecialchars($user['name']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
  <p><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
  <p>
    <a href="edit_profile.php">Profili Düzenle</a> |
    <a href="change_password.php">Şifre Değiştir</a> |
    <a href="dashboard.php">Geri</a>
  </p>
</body>
</html>
