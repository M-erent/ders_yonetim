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
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Gelen Kutusu</title>
  <link rel="stylesheet" href="assets/css/style.css?v=2">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f7fa; margin: 0; }
    .topbar {
      background-color: #1f3c88;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar .title {
      font-size: 24px;
      font-weight: bold;
    }
    .topbar .back-btn {
      background-color: #e67e22;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .topbar .back-btn:hover {
      background-color: #d35400;
    }
    .container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #1f3c88;
      text-align: center;
      margin-bottom: 20px;
    }
    ul.message-list {
      list-style-type: none;
      padding: 0;
    }
    ul.message-list li {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
      font-size: 16px;
    }
    ul.message-list li.unread {
      font-weight: bold;
      background-color: #e9f0ff;
    }
    ul.message-list li a {
      color: #1f3c88;
      text-decoration: none;
    }
    ul.message-list li a:hover {
      text-decoration: underline;
    }
    .meta {
      color: #666;
      font-size: 14px;
      margin-left: 8px;
    }
    .no-messages {
      text-align: center;
      font-style: italic;
      color: #555;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
      color: #1f3c88;
      font-weight: bold;
    }
    .back-link:hover {
      color: #e67e22;
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="title">Gelen Kutusu</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if ($inbox->num_rows === 0): ?>
    <p class="no-messages">Yeni mesaj yok.</p>
  <?php else: ?>
    <ul class="message-list">
      <?php while ($m = $inbox->fetch_assoc()): ?>
        <li class="<?= $m['is_read'] ? '' : 'unread' ?>">
          <a href="Mesaj_Gor.php?id=<?= $m['id'] ?>">
            <?= htmlspecialchars($m['title']) ?>
          </a>
          <span class="meta">— Gönderen: <?= htmlspecialchars($m['sender_name']) ?> (<?= $m['created_at'] ?>)</span>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
</div>

</body>
</html>
