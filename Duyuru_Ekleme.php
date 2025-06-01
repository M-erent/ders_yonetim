<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$msg = '';

// Öğretmen dersleri
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title']);
    $content   = trim($_POST['content']);
    $course_id = $_POST['course_id'] === '' ? null : intval($_POST['course_id']);
    $teacher_id = $_SESSION['user_id'];

    $sql = "INSERT INTO announcements (teacher_id, course_id, title, content) VALUES (?, ?, ?, ?)";
    $ins = $conn->prepare($sql);
    $ins->bind_param('iiss', $teacher_id, $course_id, $title, $content);
    if ($ins->execute()) {
        $msg = '✅ Duyuru başarıyla eklendi.';
    } else {
        $msg = '❌ Hata: ' . $ins->error;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Duyuru Ekle</title>
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
    input[type="text"], textarea, select {
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
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
      color: green;
    }
    .error {
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
      color: red;
    }
  </style>
</head>
<body>

<!-- Üst Mavi Bar -->
<div class="topbar">
  <div class="title">Duyuru Ekle</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if (!empty($msg)): ?>
    <div class="<?= strpos($msg, 'Hata') !== false ? 'error' : 'msg' ?>">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label for="title">Başlık:</label>
    <input type="text" name="title" id="title" required>

    <label for="content">İçerik:</label>
    <textarea name="content" id="content" rows="5" required></textarea>

    <label for="course_id">Hedef Ders (boş = tüm öğrenciler):</label>
    <select name="course_id" id="course_id">
      <option value="">Tümü</option>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <button type="submit">Duyuruyu Ekle</button>
  </form>
</div>

</body>
</html>
