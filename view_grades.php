<?php
// view_grades.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

$sql = "
  SELECT 
    c.course_name,
    u.name       AS student_name,
    g.grade,
    g.created_at
  FROM grades g
  JOIN users   u ON g.student_id = u.id
  JOIN courses c ON g.course_id  = c.id
  WHERE c.teacher_id = ?
  ORDER BY c.course_name, u.name
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notları Gör</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Notlar &amp; Öğrenciler</h2>
  <?php if ($rows->num_rows === 0): ?>
    <p>Henüz not girişi yapılmamış.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Ders</th>
        <th>Öğrenci</th>
        <th>Not</th>
        <th>Girildiği Tarih</th>
      </tr>
      <?php while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['course_name']) ?></td>
        <td><?= htmlspecialchars($r['student_name']) ?></td>
        <td><?= htmlspecialchars($r['grade']) ?></td>
        <td><?= $r['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
