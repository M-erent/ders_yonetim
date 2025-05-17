<?php
// add_course.php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $teacher_id  = $_SESSION['user_id'];

    $sql = "INSERT INTO courses (course_name, teacher_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $course_name, $teacher_id);
    if ($stmt->execute()) {
        $msg = 'Ders eklendi.';
    } else {
        $msg = 'Hata: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ders Ekle</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Ders Ekle</h2>
  <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
  <form method="post">
    Ders Adı: <input type="text" name="course_name" required><br><br>
    <button type="submit">Ekle</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
