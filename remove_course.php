<?php
// remove_course.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: dashboard.php');
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
<html>
<head>
  <meta charset="utf-8">
  <title>Ders Sil</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Ders Sil</h2>
  <?php if ($msg):   ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
  <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

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

  <p><a href="dashboard.php">← Geri</a></p>
</body>
</html>
