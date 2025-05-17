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
                // Dizin mevcut değilse oluşturuyoruz
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                // Benzersiz bir dosya adı oluşturuyoruz
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Eski fotoğrafı, varsayılan dosya dışında varsa, silelim.
                    if ($user['profile_image'] != 'uploads/profile_images/default_profile.png' && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    // Güncelleme sorgusunda yeni dosya yolunu kullanabilmek için değişkene atıyoruz.
                    $profile_image = $dest_path;
                } else {
                    $error = "Profil fotoğrafı yüklenirken bir hata oluştu.";
                }
            } else {
                $error = "Sadece JPG, JPEG, PNG, GIF dosyalarına izin verilmektedir.";
            }
        }
        
        // Hata oluşmadıysa veritabanında güncelleme yapıyoruz
        if (!$error) {
            if (isset($profile_image)) {
                // Yeni fotoğraf yüklendiyse; profile_image sütununu da güncelliyoruz.
                $upd = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
                $upd->bind_param('sssi', $name, $email, $profile_image, $user_id);
            } else {
                // Sadece isim ve e-posta güncellenecek.
                $upd = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $upd->bind_param('ssi', $name, $email, $user_id);
            }
            if ($upd->execute()) {
                $msg = 'Profil güncellendi.';
                $_SESSION['user_name'] = $name;
                // Güncel verileri tekrar çekiyoruz
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
<html>
<head>
  <meta charset="utf-8">
  <title>Profili Düzenle</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .profile-img-preview {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #1f3c88;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <h2>Profili Düzenle</h2>
  <?php if ($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
  <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

  <!-- Mevcut profil fotoğrafı önizlemesi -->
  <?php if (!empty($user['profile_image'])): ?>
    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Fotoğrafı" class="profile-img-preview">
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    İsim:<br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>
    Email:<br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
    
    Profil Fotoğrafı Seç:<br>
    <input type="file" name="profile_image" accept="image/*"><br><br>
    
    <button type="submit">Güncelle</button>
  </form>
  <p><a href="profile.php">Geri</a></p>
</body>
</html>
