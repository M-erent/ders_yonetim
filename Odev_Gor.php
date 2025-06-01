<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
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
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Ödevlerim</title>
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
    ul {
      list-style: none;
      padding: 0;
    }
    li {
      background: #fafafa;
      padding: 15px;
      margin: 10px 0;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    li a {
      color: #e67e22;
      text-decoration: none;
      font-weight: bold;
    }
    li a:hover {
      color: #d35400;
    }
    .msg {
      text-align: center;
      margin: 20px 0;
      font-size: 18px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<!-- Üst Mavi Bar -->
<div class="topbar">
  <div class="title">Ödevlerim</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<!-- Ödev Listesi İçeriği -->
<div class="container">
  <h2>Ödevler & Teslimler</h2>

  <!-- Mesaj Gösterimi -->
  <?php if ($assignments->num_rows === 0): ?>
    <p class="msg">Henüz ödev eklemedin.</p>
  <?php else: ?>
    <ul>
      <?php while ($row = $assignments->fetch_assoc()): ?>
        <li>
          <strong><?= htmlspecialchars($row['course_name']) ?></strong> — 
          <?= htmlspecialchars($row['title']) ?> 
          <small>(Son: <?= $row['due_date'] ?>)</small>
          <br>
          <a href="view_submissions.php?assignment_id=<?= $row['id'] ?>">Teslimleri Gör</a>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
</div>

</body>
</html>
