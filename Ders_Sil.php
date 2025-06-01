<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$msg   = '';
$error = '';

if (isset($_GET['delete_id'])) {
    $course_id = intval($_GET['delete_id']);

    // Transaction başlat
    $conn->begin_transaction();

    try {
        // 1) student_courses tablosundaki ilgili kayıtları sil
        $delSC = $conn->prepare(
            "DELETE FROM student_courses WHERE course_id = ?"
        );
        $delSC->bind_param('i', $course_id);
        $delSC->execute();

        // 2) courses tablosundan sil (diğer ilişkili tablolar ON DELETE CASCADE ile temizlenecek)
        $delC = $conn->prepare(
            "DELETE FROM courses WHERE id = ? AND teacher_id = ?"
        );
        $delC->bind_param('ii', $course_id, $_SESSION['user_id']);
        $delC->execute();

        if ($delC->affected_rows > 0) {
            $msg = '✅ Ders başarıyla silindi.';
        } else {
            $error = '⚠️ Ders bulunamadı veya yetkiniz yok.';
        }

        // Commit işlemi
        $conn->commit();
    } catch (Exception $e) {
        // Hata varsa rollback
        $conn->rollback();
        $error = 'Hata: ' . $e->getMessage();
    }
}

// Kalan dersleri çek
$stmt2 = $conn->prepare(
  "SELECT id, course_name FROM courses WHERE teacher_id = ?"
);
$stmt2->bind_param('i', $_SESSION['user_id']);
$stmt2->execute();
$courses = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Ders Sil</title>
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
    ul {
      padding-left: 20px;
    }
    ul li {
      margin-bottom: 10px;
      font-size: 16px;
    }
    a {
      color: #e74c3c;
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      color: #c0392b;
    }
    .msg, .error {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
    }
    .msg {
      color: green;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>

<!-- Üst Bar -->
<div class="topbar">
  <div class="title">Ders Sil</div>
  <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
</div>

<div class="container">
  <!-- Mesajlar -->
  <?php if ($msg): ?>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Kalan Dersler -->
  <?php if ($courses->num_rows): ?>
    <ul>
      <?php while ($c = $courses->fetch_assoc()): ?>
        <li>
          <?= htmlspecialchars($c['course_name']) ?>
          &nbsp;
          <a href="remove_course.php?delete_id=<?= $c['id'] ?>"
             onclick="return confirm('<?= addslashes(htmlspecialchars($c['course_name'])) ?> dersini silmek istediğine emin misin?');">
            [Sil]
          </a>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>Henüz eklediğiniz bir ders yok.</p>
  <?php endif; ?>
</div>

</body>
</html>
