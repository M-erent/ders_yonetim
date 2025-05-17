<?php
// student_schedule.php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: dashboard.php');
    exit();
}

$sql = "
  SELECT c.course_name, u.name AS teacher_name
  FROM student_courses sc
  JOIN courses c ON sc.course_id = c.id
  JOIN users u ON c.teacher_id = u.id
  WHERE sc.student_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ders Programım</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Ders Programım</h2>
  <?php if ($res->num_rows === 0): ?>
    <p>Henüz hiçbir derse kayıtlı değilsin.</p>
  <?php else: ?>
    <ul>
      <?php while ($row = $res->fetch_assoc()): ?>
        <li>
          <?= htmlspecialchars($row['course_name']) ?>
          — Öğretmen: <?= htmlspecialchars($row['teacher_name']) ?>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
