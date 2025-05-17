<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

$sql = "
  SELECT a.id, a.title, a.due_date, c.course_name
  FROM assignments a
  JOIN courses c ON a.course_id = c.id
  WHERE c.teacher_id = ?
  ORDER BY a.due_date ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$assignments = $stmt->get_result();
?>
<!DOCTYPE html>
<html><head>
  <meta charset="utf-8"><title>Ödevlerim</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head><body>
  <h2>Ödevler & Teslimler</h2>
  <?php if ($assignments->num_rows === 0): ?>
    <p>Henüz ödev eklemedin.</p>
  <?php else: ?>
    <ul>
      <?php while ($row = $assignments->fetch_assoc()): ?>
        <li>
          <?= htmlspecialchars($row['course_name']) ?> —
          <?= htmlspecialchars($row['title']) ?>
          (Son: <?= $row['due_date'] ?>)
          [<a href="view_submissions.php?assignment_id=<?= $row['id'] ?>">Teslimleri Gör</a>]
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body></html>
