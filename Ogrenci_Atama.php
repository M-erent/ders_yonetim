<?php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$msg = '';

// Dersleri çek
$sql = "SELECT * FROM courses WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// Öğrencileri çek
$sql2 = "SELECT * FROM users WHERE role='Öğrenci'";
$res2 = $conn->query($sql2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id  = $_POST['course_id'];
    $student_id = $_POST['student_id'];

    $sql3 = "INSERT IGNORE INTO student_courses (student_id, course_id) VALUES (?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param('ii', $student_id, $course_id);
    if ($stmt3->execute()) {
        $msg = '✅ Öğrenci başarıyla atandı.';
    } else {
        $msg = '❌ Hata: ' . $stmt3->error;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Öğrenci Ata</title>
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
  </style>
</head>
<body>

<!-- Üst Bar -->
<div class="topbar">
  <div class="title">Öğrenci Ata</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if (!empty($msg)): ?>
    <div class="<?= strpos($msg, 'Hata') !== false ? 'error' : 'msg' ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label for="course_id">Ders:</label>
    <select name="course_id" id="course_id" required>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="student_id">Öğrenci:</label>
    <select name="student_id" id="student_id" required>
      <?php while ($s = $res2->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <button type="submit">Öğrenciyi Ata</button>
  </form>
</div>

</body>
</html>
