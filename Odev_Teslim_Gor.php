<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'teacher' || !isset($_GET['assignment_id'])) {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$aid = intval($_GET['assignment_id']);

$sql = "
  SELECT s.id, u.name AS student_name, s.file_path, s.submitted_at
  FROM submissions s
  JOIN users u ON s.student_id = u.id
  WHERE s.assignment_id = ?
  ORDER BY s.submitted_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $aid);
$stmt->execute();
$subs = $stmt->get_result();
?>
<!DOCTYPE html>
<html><head>
  <meta charset="utf-8"><title>Teslimler</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head><body>
  <h2>Ödev Teslimleri</h2>
  <?php if ($subs->num_rows === 0): ?>
    <p>Henüz hiçbir öğrenci teslim etmedi.</p>
  <?php else: ?>
    <ul>
      <?php while ($row = $subs->fetch_assoc()): ?>
        <li>
          <?= htmlspecialchars($row['student_name']) ?> —
          <?= $row['submitted_at'] ?> —
          <a href="<?= htmlspecialchars($row['file_path']) ?>" download>İndir</a>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
  <p><a href="Odev_Gor.php">Geri</a></p>
</body></html>
