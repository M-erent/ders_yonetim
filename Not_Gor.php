<?php
// view_grades.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$sql = "
  SELECT 
    c.course_name,
    u.name       AS student_name,
    g.grade,
    g.created_at
  FROM grades g
  JOIN users   u ON g.student_id = u.id
  JOIN courses c ON g.course_id  = c.id
  WHERE c.teacher_id = ?
  ORDER BY c.course_name, u.name
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Notları Gör</title>
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
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #1f3c88;
      text-align: center;
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 16px;
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #1f3c88;
      color: white;
      font-weight: 600;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    p.no-data {
      text-align: center;
      font-size: 18px;
      color: #555;
      font-weight: 600;
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

<!-- Üst Bar -->
<div class="topbar">
  <div class="title">Notları Gör</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <!-- Mesajlar -->
  <?php if ($rows->num_rows === 0): ?>
    <p class="no-data">Henüz not girişi yapılmamış.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Ders</th>
          <th>Öğrenci</th>
          <th>Not</th>
          <th>Girildiği Tarih</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $rows->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['course_name']) ?></td>
          <td><?= htmlspecialchars($r['student_name']) ?></td>
          <td><?= htmlspecialchars($r['grade']) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="Gosterge_Paneli.php" class="back-link">Geri</a>
</div>

</body>
</html>
