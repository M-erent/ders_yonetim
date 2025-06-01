<?php
// view_message.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$uid = $_SESSION['user_id'];
$mid = intval($_GET['id']);

// 1) Mesajı ve göndereni al
$sql = "
  SELECT m.title, m.content, m.created_at, u.name AS sender_name
  FROM messages m
  JOIN users u ON m.sender_id = u.id
  WHERE m.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $mid);
$stmt->execute();
$msgRow = $stmt->get_result()->fetch_assoc();

// 2) Okundu bilgisi güncelle
$upd = $conn->prepare("
  UPDATE message_recipients 
  SET is_read = TRUE 
  WHERE message_id = ? AND recipient_id = ?
");
$upd->bind_param('ii', $mid, $uid);
$upd->execute();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mesajı Görüntüle</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2><?= htmlspecialchars($msgRow['title']) ?></h2>
  <small>Gönderen: <?= htmlspecialchars($msgRow['sender_name']) ?> —
        <?= $msgRow['created_at'] ?></small>
  <hr>
  <p><?= nl2br(htmlspecialchars($msgRow['content'])) ?></p>
  <p><a href="Gelen_Kutusu.php">Geri</a></p>
</body>
</html>
