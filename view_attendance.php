<?php
// view_attendance.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php'); exit();
}

// 1) Öğretmenin dersleri
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// 2) Seçili ders ve tarih (GET)
$course_id = $_GET['course_id'] ?? $courses->fetch_assoc()['id'];
$date      = $_GET['date']      ?? date('Y-m-d');

// 3) Yoklama oturumunu al
$stmt2 = $conn->prepare("
  SELECT a.id 
  FROM attendances a
  WHERE a.course_id = ? AND a.attendance_date = ?
");
$stmt2->bind_param('is', $course_id, $date);
$stmt2->execute();
$att = $stmt2->get_result()->fetch_assoc();

// 4) Kayıtları çek
$records = [];
if ($att) {
    $stmt3 = $conn->prepare("
      SELECT u.name, ar.status 
      FROM attendance_records ar
      JOIN users u ON ar.student_id = u.id
      WHERE ar.attendance_id = ?
    ");
    $stmt3->bind_param('i', $att['id']);
    $stmt3->execute();
    $records = $stmt3->get_result();
}

// 5) Tarih listesini çek
$dateRes = $conn->prepare("
  SELECT attendance_date FROM attendances 
  WHERE course_id = ? ORDER BY attendance_date DESC
");
$dateRes->bind_param('i', $course_id);
$dateRes->execute();
$dates = $dateRes->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Yoklamaları Gör</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Yoklamaları Gör</h2>
  <form method="get">
    Ders:
    <select name="course_id" onchange="this.form.submit()">
      <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>"
          <?= $c['id']==$course_id?'selected':''?>>
          <?= htmlspecialchars($c['course_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    Tarih:
    <select name="date" onchange="this.form.submit()">
      <?php while ($d = $dates->fetch_assoc()): ?>
        <option value="<?= $d['attendance_date'] ?>"
          <?= $d['attendance_date']==$date?'selected':''?>>
          <?= $d['attendance_date'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($records && $records->num_rows): ?>
    <table>
      <tr><th>Öğrenci</th><th>Durum</th></tr>
      <?php while ($r = $records->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= $r['status']=='present'?'✅ Gelmiş':'❌ Gelmemiş' ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>Bu tarih için yoklama alınmamış.</p>
  <?php endif; ?>

  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
