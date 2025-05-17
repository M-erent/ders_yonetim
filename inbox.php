<?php
// inbox.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$uid = $_SESSION['user_id'];

// 1) Gelen tüm mesajlar
$sql = "
  SELECT m.id, m.title, m.content, m.created_at, u.name AS sender_name,
         mr.is_read
  FROM message_recipients mr
  JOIN messages m ON mr.message_id = m.id
  JOIN users u    ON m.sender_id = u.id
  WHERE mr.recipient_id = ?
  ORDER BY m.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $uid);
$stmt->execute();
$inbox = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Gelen Kutusu</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Gelen Kutusu</h2>
  <?php if ($inbox->num_rows === 0): ?>
    <p>Yeni mesaj yok.</p>
  <?php else: ?>
    <ul>
      <?php while ($m = $inbox->fetch_assoc()): ?>
      <li style="<?= $m['is_read'] ? '' : 'font-weight:bold;' ?>">
        <a href="view_message.php?id=<?= $m['id'] ?>">
          <?= htmlspecialchars($m['title']) ?>
        </a>
        — Gönderen: <?= htmlspecialchars($m['sender_name']) ?>
        (<?= $m['created_at'] ?>)
      </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
