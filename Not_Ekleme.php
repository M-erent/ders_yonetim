<?php
// add_grade.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
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
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Not Ekle / Güncelle</title>
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
      max-width: 500px;
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
    select, input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 16px;
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
  <div class="title">Not Ekle / Güncelle</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <!-- Mesajlar -->
  <?php if ($msg)   echo "<div class='msg'>$msg</div>"; ?>
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>

  <form method="post">
    <label for="course_id">Ders:</label>
    <select name="course_id" id="course_id" required>
      <option value="">Seçiniz</option>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="student_id">Öğrenci:</label>
    <select name="student_id" id="student_id" required>
      <option value="">Seçiniz</option>
      <?php while ($s = $students->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="grade">Not (örn. 85, A+):</label>
    <input type="text" name="grade" id="grade" required>

    <button type="submit">Kaydet</button>
  </form>

</div>

</body>
</html>
