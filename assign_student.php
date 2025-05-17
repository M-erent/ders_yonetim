<?php
// assign_student.php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

// Dersleri çek
$sql = "SELECT * FROM courses WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// Öğrencileri çek
$sql2 = "SELECT * FROM users WHERE role='Öğrenci'";
$res2 = $conn->query($sql2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id  = $_POST['course_id'];
    $student_id = $_POST['student_id'];

    $sql3 = "INSERT IGNORE INTO student_courses (student_id, course_id) VALUES (?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param('ii', $student_id, $course_id);
    if ($stmt3->execute()) {
        $msg = 'Öğrenci atandı.';
    } else {
        $msg = 'Hata: ' . $stmt3->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Öğrenci Ata</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Öğrenci Ata</h2>
  <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
  <form method="post">
    Ders:
    <select name="course_id">
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    Öğrenci:
    <select name="student_id">
      <?php while ($s = $res2->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    <button type="submit">Ata</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
