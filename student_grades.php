<?php
// student_grades.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: dashboard.php');
    exit();
}

$sql = "
  SELECT c.course_name, g.grade, g.created_at
  FROM grades g
  JOIN courses c ON g.course_id = c.id
  WHERE g.student_id = ?
  ORDER BY g.created_at DESC
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
  <title>Notlarım</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Notlarım</h2>
  <?php if ($rows->num_rows === 0): ?>
    <p>Henüz notunuz yok.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Ders</th>
        <th>Not</th>
        <th>Tarih</th>
      </tr>
      <?php while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['course_name']) ?></td>
        <td><?= htmlspecialchars($r['grade']) ?></td>
        <td><?= $r['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
