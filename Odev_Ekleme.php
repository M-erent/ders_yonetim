<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

// Öğretmenin derslerini çek
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id   = intval($_POST['course_id']);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date    = $_POST['due_date'];

    $sql = "INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)";
    $ins = $conn->prepare($sql);
    $ins->bind_param('isss', $course_id, $title, $description, $due_date);
    $msg = $ins->execute() ? 'Ödev oluşturuldu.' : 'Hata: ' . $ins->error;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Ödev Ekle</title>
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
      max-width: 800px;
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
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      font-weight: bold;
      color: #333;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      background: #fafafa;
      transition: border-color 0.3s ease;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #1f3c88;
      outline: none;
    }
    .form-group textarea {
      resize: vertical;
    }
    .submit-btn {
      background-color: #1f3c88;
      color: #fff;
      padding: 10px 20px;
      border-radius: 6px;
      border: none;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .submit-btn:hover {
      background-color: #1a2b6c;
    }
    .msg {
      text-align: center;
      margin: 20px 0;
      font-size: 18px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<!-- Üst Mavi Bar -->
<div class="topbar">
  <div class="title">Ödev Ekle</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<!-- Form İçeriği -->
<div class="container">
  <h2>Ödev Oluştur</h2>

  <!-- Mesaj Gösterimi -->
  <?php if (!empty($msg)): ?>
    <p class="msg"><?= $msg ?></p>
  <?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label for="course_id">Ders Seç:</label>
      <select name="course_id" id="course_id" required>
        <?php while ($c = $courses->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="title">Başlık:</label>
      <input type="text" id="title" name="title" required>
    </div>

    <div class="form-group">
      <label for="description">Açıklama:</label>
      <textarea id="description" name="description" rows="4" required></textarea>
    </div>

    <div class="form-group">
      <label for="due_date">Son Teslim Tarihi:</label>
      <input type="date" id="due_date" name="due_date" required>
    </div>

    <button type="submit" class="submit-btn">Ödev Oluştur</button>
  </form>
</div>

</body>
</html>
