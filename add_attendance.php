<?php
// add_attendance.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php'); exit();
}

$msg = '';

// 1) Öğretmenin dersleri
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $date      = $_POST['attendance_date'];
    // 2) attendances tablosuna ekle (varsa ignore)
    $ins1 = $conn->prepare("
      INSERT IGNORE INTO attendances (course_id, attendance_date)
      VALUES (?, ?)
    ");
    $ins1->bind_param('is', $course_id, $date);
    $ins1->execute();
    // 3) o oturumun id'sini al
    $aid = $conn->insert_id ?: 
           // eğer INSERT IGNORE olduysa mevcut kaydı al
           $conn->query("
             SELECT id FROM attendances 
             WHERE course_id=$course_id AND attendance_date='$date'
           ")->fetch_object()->id;
    // 4) Önceki kayıtları sil, sonra yenilerini ekle
    $conn->query("DELETE FROM attendance_records WHERE attendance_id = $aid");
    foreach ($_POST['status'] as $student_id => $st) {
        $rec = $conn->prepare("
          INSERT INTO attendance_records 
            (attendance_id, student_id, status) 
          VALUES (?, ?, ?)
        ");
        $rec->bind_param('iis', $aid, $student_id, $st);
        $rec->execute();
    }
    $msg = 'Yoklama kaydedildi.';
}

// 5) Ders seçiminden sonra öğrencileri AJAX veya sayfa yenilemesiyle çekmek basittir.
// Burada hemen ilk dersi seçip formda göstereceğiz:
$firstCourse = $courses->fetch_assoc();
$studentsRes = $conn->prepare("
  SELECT u.id, u.name 
  FROM student_courses sc
  JOIN users u ON sc.student_id=u.id
  WHERE sc.course_id=?
");
$studentsRes->bind_param('i', $firstCourse['id']);
$studentsRes->execute();
$students = $studentsRes->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Yoklama Al</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Yoklama Al</h2>
  <?php if ($msg) echo "<p style='color:green;'>$msg</p>"; ?>
  <form method="post">
    Ders:<br>
    <select name="course_id" required onchange="this.form.submit()">
      <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>"
          <?= $c['id']==($firstCourse['id']??0)?'selected':'' ?>>
          <?= htmlspecialchars($c['course_name']) ?>
        </option>
      <?php endforeach; ?>
    </select><br><br>

    Tarih:<br>
    <input type="date" name="attendance_date"
           value="<?= date('Y-m-d') ?>" required><br><br>

    <?php if ($students->num_rows): ?>
      <?php while ($s = $students->fetch_assoc()): ?>
        <label>
          <input type="radio" 
                 name="status[<?= $s['id'] ?>]" 
                 value="present" checked>
          <?= htmlspecialchars($s['name']) ?> — Gelmiş
        </label><br>
        <label>
          <input type="radio" 
                 name="status[<?= $s['id'] ?>]" 
                 value="absent">
          Gelmemiş
        </label><br><br>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Bu derse kayıtlı öğrenci yok.</p>
    <?php endif; ?>

    <button type="submit">Kaydet</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
