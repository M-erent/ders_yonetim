<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: dashboard.php');
    exit();
}

$uid = $_SESSION['user_id'];

$sql = "
  SELECT a.id, a.title, a.description, a.due_date,
         (SELECT COUNT(*) FROM submissions s
            WHERE s.assignment_id = a.id AND s.student_id = ?) AS submitted
  FROM assignments a
  JOIN student_courses sc ON a.course_id = sc.course_id
  WHERE sc.student_id = ?
  ORDER BY a.due_date ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $uid, $uid);
$stmt->execute();
$assigns = $stmt->get_result();
?>
<!DOCTYPE html>
<html><head>
  <meta charset="utf-8"><title>Ödevlerim</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head><body>
  <h2>Ödevler & Teslim</h2>
  <?php if ($assigns->num_rows === 0): ?>
    <p>Henüz ödevin yok.</p>
  <?php else: ?>
    <?php while ($row = $assigns->fetch_assoc()): ?>
      <div class="assignment">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
        <small>Son: <?= $row['due_date'] ?></small><br>
        <?php if ($row['submitted']): ?>
          <strong>✅ Teslim Edildi</strong>
        <?php else: ?>
          <a href="submit_assignment.php?assignment_id=<?= $row['id'] ?>">Teslim Et</a>
        <?php endif; ?>
        <hr>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body></html>
