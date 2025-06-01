<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'student' || !isset($_GET['assignment_id'])) {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$aid = intval($_GET['assignment_id']);
$uid = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['submission'])) {
    $f      = $_FILES['submission'];
    $ext    = pathinfo($f['name'], PATHINFO_EXTENSION);
    $dest   = 'uploads/assignments/' . $aid . '_' . $uid . '_' . time() . '.' . $ext;
    if (move_uploaded_file($f['tmp_name'], __DIR__ . '/' . $dest)) {
        $ins = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
        $ins->bind_param('iis', $aid, $uid, $dest);
        if ($ins->execute()) {
            $msg = '✅ Ödev teslim edildi.';
        } else {
            $msg = 'Hata: ' . $ins->error;
        }
    } else {
        $msg = 'Dosya yüklenemedi.';
    }
}
?>
<!DOCTYPE html>
<html><head>
  <meta charset="utf-8"><title>Ödev Teslim</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head><body>
  <h2>Ödev Teslim Et</h2>
  <?php if ($msg) echo "<p>$msg</p>"; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="file" name="submission" required><br><br>
    <button type="submit">Gönder</button>
  </form>
  <p><a href="Odev.php">Geri</a></p>
</body></html>
