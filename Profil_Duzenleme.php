<?php
// edit_profile.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$error = $msg = '';

// Mevcut verileri çekiyoruz (profil fotoğrafı bilgisi de dahil)
$stmt = $conn->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Email benzersiz mi kontrol edelim
    $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $chk->bind_param('si', $email, $user_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $error = 'Bu email zaten kullanımda!';
    } else {
        // Dosya yüklemesi kontrolü: Eğer yeni bir fotoğraf yüklendiyse
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['profile_image']['tmp_name'];
            $fileName      = $_FILES['profile_image']['name'];
            // Dosya adını parçalayarak uzantıyı alalım
            $fileNameCmps  = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedFileExtensions = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileExtension, $allowedFileExtensions)) {
                $uploadFileDir = 'uploads/profile_images/';  // Yeni dizin burada
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if ($user['profile_image'] != 'uploads/profile_images/default_profile.png' && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    $profile_image = $dest_path;
                } else {
                    $error = "Profil fotoğrafı yüklenirken bir hata oluştu.";
                }
            } else {
                $error = "Sadece JPG, JPEG, PNG, GIF dosyalarına izin verilmektedir.";
            }
        }
        
        if (!$error) {
            if (isset($profile_image)) {
                $upd = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
                $upd->bind_param('sssi', $name, $email, $profile_image, $user_id);
            } else {
                $upd = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $upd->bind_param('ssi', $name, $email, $user_id);
            }
            if ($upd->execute()) {
                $msg = 'Profil güncellendi.';
                $_SESSION['user_name'] = $name;
                $stmt = $conn->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Hata: ' . $upd->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Profili Düzenle</title>
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
    .profile-img-preview {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #1f3c88;
      margin: 0 auto 20px;
      display: block;
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
    input[type="text"],
    input[type="email"],
    input[type="file"] {
      padding: 10px;
      font-size: 15px;
      border: 1.5px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="file"]:focus {
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
  <div class="title">Profili Düzenle</div>
  <a href="Profil.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if ($msg): ?><p class="msg success"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="msg error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <?php if (!empty($user['profile_image'])): ?>
    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Fotoğrafı" class="profile-img-preview">
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <label for="name">İsim:</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label for="profile_image">Profil Fotoğrafı Seç:</label>
    <input type="file" id="profile_image" name="profile_image" accept="image/*">

    <button type="submit">Güncelle</button>
  </form>
</div>

</body>
</html>
