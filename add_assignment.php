<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

// Öğretmenin derslerini çek
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id   = intval($_POST['course_id']);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date    = $_POST['due_date'];

    $sql = "INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)";
    $ins = $conn->prepare($sql);
    $ins->bind_param('isss', $course_id, $title, $description, $due_date);
    $msg = $ins->execute() ? 'Ödev oluşturuldu.' : 'Hata: ' . $ins->error;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ödev Ekle</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Ödev Ekle</h2>
  <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
  <form method="post">
    Ders:<br>
    <select name="course_id" required>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    Başlık:<br>
    <input type="text" name="title" required><br><br>

    Açıklama:<br>
    <textarea name="description" rows="4" required></textarea><br><br>

    Son Teslim Tarihi:<br>
    <input type="date" name="due_date" required><br><br>

    <button type="submit">Oluştur</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
