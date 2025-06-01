<?php
// student_attendance.php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db/db_connect.php';

if ($_SESSION['role'] !== 'Öğrenci') {
    header('Location: Gosterge_Paneli.php'); 
    exit();
}

// 1) Öğrencinin dersleri
$stmt = $conn->prepare("
  SELECT c.id, c.course_name 
  FROM student_courses sc
  JOIN courses c ON sc.course_id=c.id
  WHERE sc.student_id = ?
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();

// 2) Varsayılan seçim
$course_id = $_GET['course_id'] ?? ($courses->fetch_assoc()['id'] ?? null);

// Eğer course_id hala boş ise (örneğin öğrenci derse kayıtlı değilse)
if (!$course_id) {
    $dates = null;
    $records = null;
} else {
    // 3) Bu derse ait tüm yoklama oturumları
    $dateRes = $conn->prepare("
      SELECT a.id, a.attendance_date 
      FROM attendances a
      WHERE a.course_id = ?
      ORDER BY a.attendance_date DESC
    ");
    $dateRes->bind_param('i', $course_id);
    $dateRes->execute();
    $dates = $dateRes->get_result();

    // 4) Kayıtları çek
    $records = null;
    if (isset($_GET['attendance_id'])) {
        $aid = intval($_GET['attendance_id']);
        $stmt2 = $conn->prepare("
          SELECT status FROM attendance_records
          WHERE attendance_id = ? AND student_id = ?
        ");
        $stmt2->bind_param('ii', $aid, $_SESSION['user_id']);
        $stmt2->execute();
        $records = $stmt2->get_result()->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yoklamalarım</title>
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
      margin-bottom: 24px;
    }
    form {
      margin-bottom: 24px;
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
    }
    select {
      padding: 10px;
      font-size: 16px;
      border-radius: 6px;
      border: 1px solid #ccc;
      min-width: 180px;
      cursor: pointer;
      background-color: #fff;
      transition: border-color 0.3s ease;
    }
    select:hover, select:focus {
      border-color: #1f3c88;
      outline: none;
    }
    p.status {
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      color: #333;
    }
    p.status span {
      font-size: 22px;
      margin-left: 8px;
    }
    p.no-records {
      text-align: center;
      color: #666;
      font-size: 16px;
      margin-top: 20px;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 30px;
      text-decoration: none;
      color: #1f3c88;
      font-weight: bold;
      font-size: 18px;
      transition: color 0.3s ease;
    }
    .back-link:hover {
      color: #e67e22;
    }
  </style>
</head>
<body>

  <div class="topbar">
    <div class="title">Yoklamalarım</div>
    <a href="Gosterge_Paneli.php" class="back-btn">Geri</a>
  </div>

  <div class="container">

    <?php if (!$course_id): ?>
      <p class="no-records">Henüz kayıtlı olduğunuz ders bulunmamaktadır.</p>
    <?php else: ?>
      <form method="get" action="">
        <label for="course_id" class="sr-only">Ders Seç</label>
        <select name="course_id" id="course_id" onchange="this.form.submit()">
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $course_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['course_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="attendance_id" class="sr-only">Tarih Seç</label>
        <select name="attendance_id" id="attendance_id" onchange="this.form.submit()">
          <?php if ($dates && $dates->num_rows > 0): ?>
            <?php while ($d = $dates->fetch_assoc()): ?>
              <option value="<?= $d['id'] ?>" <?= (isset($_GET['attendance_id']) && $_GET['attendance_id'] == $d['id']) ? 'selected' : '' ?>>
                <?= $d['attendance_date'] ?>
              </option>
            <?php endwhile; ?>
          <?php else: ?>
            <option disabled>Yoklama kaydı bulunamadı</option>
          <?php endif; ?>
        </select>
      </form>

      <?php if ($records): ?>
        <p class="status">Durum: 
          <?php if ($records['status'] === 'present'): ?>
            <span style="color:green;">✅ Gelmiş</span>
          <?php else: ?>
            <span style="color:red;">❌ Gelmemiş</span>
          <?php endif; ?>
        </p>
      <?php else: ?>
        <p class="no-records">Bu tarih için yoklama kaydın yok.</p>
      <?php endif; ?>
    <?php endif; ?>

    <a href="Gosterge_Paneli.php" class="back-link">Geri</a>
  </div>

</body>
</html>
