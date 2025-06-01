<?php
// Ogrenci_ders_secme_iptal.php
session_start();
include 'config.php';

// Sadece “Öğrenci” rolündeki kullanıcılar erişebilsin:
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Öğrenci') {
    // Yetkisi yoksa gösterge paneline dön:
    header('Location: Gosterge_Paneli.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Form gönderildiğinde: Öğrenci ders ekliyor veya iptal ediyor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'];       // 'add' ya da 'remove'
    $course_id = (int)$_POST['course_id'];

    if ($action === 'add') {
        // 1) Öğrenci zaten eklemiş mi kontrol et
        $check = $db->prepare("
            SELECT COUNT(*) AS cnt
            FROM student_courses
            WHERE student_id = ? AND course_id = ?
        ");
        $check->execute([$student_id, $course_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row['cnt'] > 0) {
            $error = "Bu dersi zaten seçmişsiniz.";
        } else {
            // 2) Ders seçme (assigned_by_teacher = 0)
            $insert = $db->prepare("
                INSERT INTO student_courses (student_id, course_id, assigned_by_teacher)
                VALUES (?, ?, 0)
            ");
            if ($insert->execute([$student_id, $course_id])) {
                $success = "Ders başarıyla seçildi.";
            } else {
                $error = "Ders seçme sırasında bir hata oluştu.";
            }
        }
    }
    elseif ($action === 'remove') {
        // 3) Kayıt varsa iptal et (sil)
        $del = $db->prepare("
            DELETE FROM student_courses
            WHERE student_id = ? AND course_id = ?
        ");
        if ($del->execute([$student_id, $course_id])) {
            $success = "Ders kaydınız silindi.";
        } else {
            $error = "Ders iptal işlemi sırasında bir hata oluştu.";
        }
    }
}

// 4) Tüm ders listesini çek
$allCourses = $db->query("
    SELECT id, course_name
    FROM courses
    ORDER BY course_name
")->fetchAll(PDO::FETCH_ASSOC);

// 5) Öğrencinin seçtiği derslerin ID’lerini çek
$myCoursesStmt = $db->prepare("
    SELECT course_id
    FROM student_courses
    WHERE student_id = ?
");
$myCoursesStmt->execute([$student_id]);
$myCourses = $myCoursesStmt->fetchAll(PDO::FETCH_COLUMN, 0);

// 6) Hızlı kontrol için dizi yapısı
$myCoursesMap = array_flip($myCourses);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ders Seçme / İptal Etme</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    body {
      background: #f4f7fa;
      font-family: 'Poppins', sans-serif;
      margin: 0; padding: 0;
    }
    .container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px;
    }
    h2 {
      color: #1f3c88;
      margin-bottom: 20px;
      display: flex; align-items: center; gap: 8px;
    }
    h2 .fa {
      color: #1f3c88;
    }
    .message {
      margin-bottom: 15px;
      padding: 10px 15px;
      border-radius: 6px;
    }
    .message.success {
      background: #e6ffed;
      color: #2e7d32;
      border-left: 4px solid #4caf50;
    }
    .message.error {
      background: #ffebee;
      color: #c62828;
      border-left: 4px solid #e53935;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      padding: 12px 10px;
      border-bottom: 1px solid #ddd;
      text-align: left;
      font-size: 0.95em;
    }
    table th {
      background: #1f3c88;
      color: #fff;
      font-weight: 500;
    }
    table tr:hover {
      background: #f1f5fa;
    }
    .btn {
      display: inline-block;
      padding: 7px 14px;
      border-radius: 4px;
      font-size: 0.9em;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s;
      border: none;
    }
    .btn-add {
      background: #1f3c88;
      color: #fff;
    }
    .btn-add:hover {
      background: #163066;
    }
    .btn-remove {
      background: #e74c3c;
      color: #fff;
    }
    .btn-remove:hover {
      background: #c0392b;
    }
    .back-link {
      display: inline-block;
      margin-top: 20px;
      color: #1f3c88;
      font-size: 0.95em;
    }
    .back-link i {
      margin-right: 6px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2><i class="fa fa-list-alt"></i> Ders Seçme / İptal Etme</h2>

    <?php if ($error): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Ders Adı</th>
          <th>Durum</th>
          <th>İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allCourses as $crs): ?>
          <?php
            $cid = $crs['id'];
            $hasChosen = isset($myCoursesMap[$cid]);
          ?>
          <tr>
            <td><?= htmlspecialchars($crs['course_name']) ?></td>
            <td>
              <?php if ($hasChosen): ?>
                <strong style="color: #2e7d32;">Seçildi</strong>
              <?php else: ?>
                <span style="color: #666;">Henüz seçilmedi</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="course_id" value="<?= $cid ?>">
                <?php if ($hasChosen): ?>
                  <input type="hidden" name="action" value="remove">
                  <button type="submit" class="btn btn-remove">
                    <i class="fa fa-times-circle"></i> İptal Et
                  </button>
                <?php else: ?>
                  <input type="hidden" name="action" value="add">
                  <button type="submit" class="btn btn-add">
                    <i class="fa fa-check-circle"></i> Seç
                  </button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="Gosterge_Paneli.php" class="back-link">
      <i class="fa fa-arrow-left"></i> Gösterge Paneli’ne Dön
    </a>
  </div>
</body>
</html>
