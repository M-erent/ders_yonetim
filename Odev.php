<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: Gosterge_Paneli.php');
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
    .assignment {
      background: #e1e7f4;
      padding: 16px 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      font-size: 16px;
      color: #333;
    }
    .assignment h3 {
      margin-top: 0;
      color: #1f3c88;
    }
    .assignment p {
      white-space: pre-line;
      margin: 10px 0;
    }
    .assignment small {
      color: #555;
      font-size: 14px;
    }
    .assignment a, .assignment strong {
      display: inline-block;
      margin-top: 10px;
      font-weight: bold;
      font-size: 16px;
      text-decoration: none;
      color: #1f3c88;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .assignment a:hover {
      color: #e67e22;
    }
    hr {
      border: none;
      border-bottom: 1px solid #ccc;
      margin-top: 20px;
    }
    p.no-assignments {
      text-align: center;
      font-size: 16px;
      color: #666;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 30px;
      text-decoration: none;
      color: #1f3c88;
      font-weight: bold;
      font-size: 18px;
      transition: color 0.3s ease;
    }
    .back-link:hover {
      color: #e67e22;
    }
  </style>
</head>
<body>

  <div class="topbar">
    <div class="title">Ödevler & Teslim</div>
    <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
  </div>

  <div class="container">
    <?php if ($assigns->num_rows === 0): ?>
      <p class="no-assignments">Henüz ödevin yok.</p>
    <?php else: ?>
      <?php while ($row = $assigns->fetch_assoc()): ?>
        <div class="assignment">
          <h3><?= htmlspecialchars($row['title']) ?></h3>
          <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
          <small>Son: <?= htmlspecialchars($row['due_date']) ?></small><br>
          <?php if ($row['submitted']): ?>
            <strong>✅ Teslim Edildi</strong>
          <?php else: ?>
            <a href="submit_assignment.php?assignment_id=<?= $row['id'] ?>">Teslim Et</a>
          <?php endif; ?>
          <hr>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
    <a href="Gosterge_Paneli.php" class="back-link">Geri</a>
  </div>

</body>
</html>
