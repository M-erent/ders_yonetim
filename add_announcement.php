<?php
// add_announcement.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

// Öğretmen dersleri
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title']);
    $content   = trim($_POST['content']);
    $course_id = $_POST['course_id'] === '' ? null : intval($_POST['course_id']);
    $teacher_id = $_SESSION['user_id'];

    $sql = "INSERT INTO announcements (teacher_id, course_id, title, content) VALUES (?, ?, ?, ?)";
    $ins = $conn->prepare($sql);
    $ins->bind_param('iiss', $teacher_id, $course_id, $title, $content);
    if ($ins->execute()) {
        $msg = 'Duyuru eklendi.';
    } else {
        $msg = 'Hata: ' . $ins->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Duyuru Ekle</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Duyuru Ekle</h2>
  <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
  <form method="post">
    Başlık:<br>
    <input type="text" name="title" required><br><br>

    İçerik:<br>
    <textarea name="content" rows="5" required></textarea><br><br>

    Hedef Ders (boş = tüm öğrencilere):<br>
    <select name="course_id">
      <option value="">Tümü</option>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    <button type="submit">Ekle</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
