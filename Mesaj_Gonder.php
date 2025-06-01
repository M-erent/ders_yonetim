<?php
// send_message.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$msg     = '';

// 1) Gönderici dışındaki kullanıcılar (bireysel)
$users = $conn->prepare("SELECT id, name FROM users WHERE id <> ? ORDER BY name");
$users->bind_param('i', $user_id);
$users->execute();
$userList = $users->get_result();

// 2) Öğretmenin dersleri (grup mesaj)
$courses = [];
if ($role === 'Öğretmen') {
    $stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE teacher_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $courses = $stmt->get_result();
}

// 3) Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type    = $_POST['message_type']; // 'individual' veya 'course'
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    $course_id = $type === 'course' ? intval($_POST['course_id']) : null;

    $insMsg = $conn->prepare("INSERT INTO messages (sender_id, title, content, course_id) VALUES (?, ?, ?, ?)");
    $insMsg->bind_param('isss', $user_id, $title, $content, $course_id);
    $insMsg->execute();
    $mid = $conn->insert_id;

    if ($type === 'individual') {
        $rid = intval($_POST['recipient_id']);
        $insR = $conn->prepare("INSERT INTO message_recipients (message_id, recipient_id) VALUES (?, ?)");
        $insR->bind_param('ii', $mid, $rid);
        $insR->execute();
    } else {
        $stmt2 = $conn->prepare("SELECT student_id FROM student_courses WHERE course_id = ?");
        $stmt2->bind_param('i', $course_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $insR = $conn->prepare("INSERT INTO message_recipients (message_id, recipient_id) VALUES (?, ?)");
        while ($row = $res2->fetch_assoc()) {
            $insR->bind_param('ii', $mid, $row['student_id']);
            $insR->execute();
        }
    }

    $msg = 'Mesaj gönderildi!';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Mesaj Gönder</title>
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
    label {
      font-weight: bold;
      display: block;
      margin-bottom: 6px;
      margin-top: 12px;
    }
    select, input[type="text"], textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 12px;
      font-size: 16px;
    }
    textarea {
      resize: vertical;
    }
    .radio-group {
      display: flex;
      gap: 20px;
      margin-bottom: 16px;
    }
    .radio-group label {
      font-weight: normal;
    }
    button[type="submit"] {
      background-color: #1f3c88;
      color: white;
      border: none;
      padding: 12px 20px;
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

<div class="topbar">
  <div class="title">Mesaj Gönder</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <?php if ($msg): ?>
    <div class="msg"><?= $msg ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Mesaj Türü:</label>
    <div class="radio-group">
      <label><input type="radio" name="message_type" value="individual" checked> Bireysel</label>
      <?php if ($role === 'Öğretmen'): ?>
        <label><input type="radio" name="message_type" value="course"> Ders Grubu</label>
      <?php endif; ?>
    </div>

    <div>
      <label for="recipient_id">Alıcı (Bireysel):</label>
      <select name="recipient_id" id="recipient_id">
        <?php while ($u = $userList->fetch_assoc()): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <?php if ($role === 'Öğretmen'): ?>
    <div>
      <label for="course_id">Ders (Grup):</label>
      <select name="course_id" id="course_id">
        <?php while ($c = $courses->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <?php endif; ?>

    <label for="title">Başlık:</label>
    <input type="text" name="title" id="title" required>

    <label for="content">Mesaj:</label>
    <textarea name="content" id="content" rows="5" required></textarea>

    <button type="submit">Gönder</button>
  </form>
</div>

</body>
</html>
