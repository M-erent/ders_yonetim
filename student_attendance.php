<?php
// student_attendance.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: dashboard.php'); exit();
}

// 1) Öğrencinin dersleri
$stmt = $conn->prepare("
  SELECT c.id, c.course_name 
  FROM student_courses sc
  JOIN courses c ON sc.course_id=c.id
  WHERE sc.student_id = ?
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// 2) Varsayılan seçim
$course_id = $_GET['course_id'] ?? $courses->fetch_assoc()['id'];

// 3) Bu derse ait tüm yoklama oturumları
$dateRes = $conn->prepare("
  SELECT a.id, a.attendance_date 
  FROM attendances a
  WHERE a.course_id = ?
  ORDER BY a.attendance_date DESC
");
$dateRes->bind_param('i', $course_id);
$dateRes->execute();
$dates = $dateRes->get_result();

// 4) Kayıtları çek
$records = [];
if (isset($_GET['attendance_id'])) {
    $aid = intval($_GET['attendance_id']);
    $stmt2 = $conn->prepare("
      SELECT status FROM attendance_records
      WHERE attendance_id = ? AND student_id = ?
    ");
    $stmt2->bind_param('ii', $aid, $_SESSION['user_id']);
    $stmt2->execute();
    $records = $stmt2->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Yoklamalarım</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Yoklamalarım</h2>
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
    <select name="attendance_id" onchange="this.form.submit()">
      <?php while ($d = $dates->fetch_assoc()): ?>
        <option value="<?= $d['id'] ?>">
          <?= $d['attendance_date'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($records): ?>
    <p>Durum: <?= $records['status']=='present'?'✅ Gelmiş':'❌ Gelmemiş' ?></p>
  <?php else: ?>
    <p>Bu tarih için yoklama kaydın yok.</p>
  <?php endif; ?>

  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
