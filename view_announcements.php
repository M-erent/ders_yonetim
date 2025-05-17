<?php
// view_announcements.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

if ($role === 'Öğretmen') {
    // Öğretmenin kendi duyuruları
    $sql = "
      SELECT a.*, c.course_name
      FROM announcements a
      LEFT JOIN courses c ON a.course_id = c.id
      WHERE a.teacher_id = ?
      ORDER BY a.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

} else {
    // Öğrenci: global duyurular + kayıtlı olduğu derslerin duyuruları
    $sql = "
      SELECT a.*, c.course_name
      FROM announcements a
      LEFT JOIN courses c ON a.course_id = c.id
      WHERE a.course_id IS NULL
         OR a.course_id IN (
           SELECT course_id FROM student_courses WHERE student_id = ?
         )
      ORDER BY a.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Duyurular</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Duyurular</h2>
  <?php if ($results->num_rows === 0): ?>
    <p>Henüz hiç duyuru yok.</p>
  <?php else: ?>
    <?php while ($row = $results->fetch_assoc()): ?>
      <div class="announcement">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <small>
          <?= htmlspecialchars($row['created_at']) ?>
          <?= $row['course_name'] ? "— Ders: ".htmlspecialchars($row['course_name']) : "— Genel" ?>
        </small>
        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
        <hr>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
  <p><a href="dashboard.php">Geri</a></p>
</body>
</html>
