<?php
// add_grade.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
    exit();
}

$msg   = '';
$error = '';

// 1) Öğretmenin dersleri
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// 2) Tüm öğrenciler (not girişi için)
$students = $conn->query("SELECT id, name FROM users WHERE role='Öğrenci'");

// 3) Form gönderildiğinde işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id  = intval($_POST['course_id']);
    $student_id = intval($_POST['student_id']);
    $grade      = trim($_POST['grade']);

    // Öğrencinin derse kayıtlı olup olmadığını kontrol et
    $chk = $conn->prepare(
      "SELECT 1 FROM student_courses WHERE student_id = ? AND course_id = ?"
    );
    $chk->bind_param('ii', $student_id, $course_id);
    $chk->execute();
    $res = $chk->get_result();
    if ($res->num_rows === 0) {
        $error = 'Bu öğrenci bu derse kayıtlı değil!';
    } else {
        // Not ekle veya güncelle
        $sql = "
          INSERT INTO grades (student_id, course_id, grade)
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE grade = VALUES(grade)
        ";
        $ins = $conn->prepare($sql);
        $ins->bind_param('iis', $student_id, $course_id, $grade);
        if ($ins->execute()) {
            $msg = 'Not kaydedildi.';
        } else {
            $error = 'Hata: ' . $ins->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Not Ekle / Güncelle</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Not Ekle / Güncelle</h2>
  <?php if ($msg)   echo "<p style='color:green;'>$msg</p>"; ?>
  <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

  <form method="post">
    Ders:<br>
    <select name="course_id" required>
      <option value="">Seçiniz</option>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    Öğrenci:<br>
    <select name="student_id" required>
      <option value="">Seçiniz</option>
      <?php while ($s = $students->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endwhile; ?>
    </select><br><br>

    Not (örn. 85, A+):<br>
    <input type="text" name="grade" required><br><br>

    <button type="submit">Kaydet</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
