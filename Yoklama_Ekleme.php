<?php
// add_attendance.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php'); exit();
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
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yoklama Al</title>
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
      max-width: 600px;
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
    select, input[type="date"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 16px;
    }
    label {
      font-size: 16px;
      margin-right: 10px;
    }
    .radio-group {
      margin-bottom: 15px;
    }
    button[type="submit"] {
      background-color: #1f3c88;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
    }
    button[type="submit"]:hover {
      background-color: #16306a;
    }
    .msg {
      color: green;
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
    }
    .error {
      color: red;
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
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
  <div class="title">Yoklama Al</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <!-- Mesajlar -->
  <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>

  <form method="post">
    <label for="course_id">Ders:</label>
    <select name="course_id" id="course_id" required onchange="this.form.submit()">
      <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>"
          <?= $c['id']==($firstCourse['id']??0)?'selected':'' ?> >
          <?= htmlspecialchars($c['course_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="attendance_date">Tarih:</label>
    <input type="date" name="attendance_date" value="<?= date('Y-m-d') ?>" required>

    <?php if ($students->num_rows): ?>
      <?php while ($s = $students->fetch_assoc()): ?>
        <div class="radio-group">
          <label>
            <input type="radio" name="status[<?= $s['id'] ?>]" value="present" checked>
            <?= htmlspecialchars($s['name']) ?> — Gelmiş
          </label><br>
          <label>
            <input type="radio" name="status[<?= $s['id'] ?>]" value="absent">
            Gelmemiş
          </label>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Bu derse kayıtlı öğrenci yok.</p>
    <?php endif; ?>

    <button type="submit">Kaydet</button>
  </form>
</div>

</body>
</html>
