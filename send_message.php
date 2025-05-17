<?php
// send_message.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$msg     = '';

// 1) Gönderici dışındaki kullanıcılar (bireysel)
$users = $conn->prepare("
  SELECT id, name FROM users 
  WHERE id <> ? 
  ORDER BY name
");
$users->bind_param('i', $user_id);
$users->execute();
$userList = $users->get_result();

// 2) Öğretmenin dersleri (grup mesaj)
$courses = [];
if ($role === 'Öğretmen') {
    $stmt = $conn->prepare("
      SELECT id, course_name FROM courses 
      WHERE teacher_id = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $courses = $stmt->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type    = $_POST['message_type'];           // 'individual' ya da 'course'
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    // 3) messages tablosuna ekle
    $course_id = $type === 'course' 
               ? intval($_POST['course_id']) 
               : null;
    $insMsg = $conn->prepare("
      INSERT INTO messages (sender_id, title, content, course_id)
      VALUES (?, ?, ?, ?)
    ");
    $insMsg->bind_param('isss', $user_id, $title, $content, $course_id);
    $insMsg->execute();
    $mid = $conn->insert_id;

    // 4) message_recipients ekle
    if ($type === 'individual') {
        $rid = intval($_POST['recipient_id']);
        $insR = $conn->prepare("
          INSERT INTO message_recipients (message_id, recipient_id)
          VALUES (?, ?)
        ");
        $insR->bind_param('ii', $mid, $rid);
        $insR->execute();
    } else {
        // dersteki tüm öğrencilere
        $stmt2 = $conn->prepare("
          SELECT student_id FROM student_courses 
          WHERE course_id = ?
        ");
        $stmt2->bind_param('i', $course_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $insR = $conn->prepare("
          INSERT INTO message_recipients (message_id, recipient_id)
          VALUES (?, ?)
        ");
        while ($row = $res2->fetch_assoc()) {
            $insR->bind_param('ii', $mid, $row['student_id']);
            $insR->execute();
        }
    }

    $msg = 'Mesaj gönderildi!';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mesaj Gönder</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Mesaj Gönder</h2>
  <?php if ($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>

  <form method="post">
    Mesaj Türü:<br>
    <label><input type="radio" name="message_type" value="individual" checked> Bireysel</label>
    <?php if ($role === 'Öğretmen'): ?>
      <label><input type="radio" name="message_type" value="course"> Ders Grubu</label>
    <?php endif; ?>
    <br><br>

    <!-- Bireysel için -->
    <div>
      Alıcı:
      <select name="recipient_id">
        <?php while ($u = $userList->fetch_assoc()): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <br>

    <!-- Ders grubu için -->
    <?php if ($role === 'Öğretmen'): ?>
    <div>
      Ders:
      <select name="course_id">
        <?php while ($c = $courses->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <br>
    <?php endif; ?>

    Başlık:<br>
    <input type="text" name="title" required><br><br>

    Mesaj:<br>
    <textarea name="content" rows="5" required></textarea><br><br>

    <button type="submit">Gönder</button>
  </form>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
