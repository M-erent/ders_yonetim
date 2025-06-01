<?php
// student_grades.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$sql = "
  SELECT c.course_name, g.grade, g.created_at
  FROM grades g
  JOIN courses c ON g.course_id = c.id
  WHERE g.student_id = ?
  ORDER BY g.created_at DESC
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
  <title>Notlarım</title>
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
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 16px;
      color: #333;
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #e1e7f4;
      color: #1f3c88;
      font-weight: bold;
    }
    tr:hover {
      background-color: #f1f5fb;
    }
    p.no-grades {
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
    <div class="title">Notlarım</div>
    <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
  </div>

  <div class="container">
    <?php if ($rows->num_rows === 0): ?>
      <p class="no-grades">Henüz notunuz yok.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Ders</th>
            <th>Not</th>
            <th>Tarih</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($r = $rows->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['course_name']) ?></td>
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
