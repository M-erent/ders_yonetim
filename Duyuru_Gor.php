<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

if ($role === 'Öğretmen') {
    $sql = "
      SELECT a.*, c.course_name
      FROM announcements a
      LEFT JOIN courses c ON a.course_id = c.id
      WHERE a.teacher_id = ?
      ORDER BY a.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
} else {
    $sql = "
      SELECT a.*, c.course_name
      FROM announcements a
      LEFT JOIN courses c ON a.course_id = c.id
      WHERE a.course_id IS NULL
         OR a.course_id IN (
           SELECT course_id FROM student_courses WHERE student_id = ?
         )
      ORDER BY a.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Duyurular</title>
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
      max-width: 800px;
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
    .announcement {
      border-left: 5px solid #1f3c88;
      padding: 16px;
      margin-bottom: 20px;
      background: #f9fafe;
      border-radius: 6px;
    }
    .announcement h3 {
      margin: 0 0 6px 0;
      color: #1f3c88;
    }
    .announcement small {
      color: #555;
      display: block;
      margin-bottom: 10px;
      font-size: 0.9em;
    }
    .announcement p {
      margin: 0;
      line-height: 1.6;
    }
    .no-announcement {
      text-align: center;
      color: #888;
      font-style: italic;
    }
  </style>
</head>
<body>

<!-- Üst Mavi Bar -->
<div class="topbar">
  <div class="title">Duyurular</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <h2>Duyurular</h2>

  <?php if ($results->num_rows === 0): ?>
    <p class="no-announcement">Henüz hiç duyuru yok.</p>
  <?php else: ?>
    <?php while ($row = $results->fetch_assoc()): ?>
      <div class="announcement">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <small>
          <?= date('d.m.Y H:i', strtotime($row['created_at'])) ?>
          — <?= $row['course_name'] ? "Ders: " . htmlspecialchars($row['course_name']) : "Genel Duyuru" ?>
        </small>
        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

</body>
</html>
