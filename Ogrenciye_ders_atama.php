<?php
// Ogrenciye_ders_atama.php
session_start();
include 'config.php';

// Sadece “Öğretmen” rolündeki kullanıcılar erişebilsin:
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Öğretmen') {
    // Yetkisi yoksa tekrar gösterge paneline dön:
    header('Location: Gosterge_Paneli.php');
    exit();
}

$error   = '';
$success = '';

// Form gönderildiyse:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $course_id  = (int)$_POST['course_id'];

    // 1) Aynı öğrenci-ders ilişkisi var mı kontrol et:
    $check = $db->prepare("
        SELECT COUNT(*) AS cnt
        FROM student_courses
        WHERE student_id = ? AND course_id = ?
    ");
    $check->execute([$student_id, $course_id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if ($row['cnt'] > 0) {
        $error = "Bu öğrenci zaten bu dersi alıyor.";
    } else {
        // 2) Öğrenciye dersi atama (assigned_by_teacher = 1):
        $insert = $db->prepare("
            INSERT INTO student_courses (student_id, course_id, assigned_by_teacher)
            VALUES (?, ?, 1)
        ");
        if ($insert->execute([$student_id, $course_id])) {
            $success = "Ders başarıyla öğrenciye atandı.";
        } else {
            $error = "Atama işleminde bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}

// Öğrenci listesini ve ders listesini çek:
$students = $db->query("
    SELECT id, name, email
    FROM users
    WHERE role = 'Öğrenci'
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$courses = $db->query("
    SELECT id, course_name
    FROM courses
    ORDER BY course_name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Öğrenciye Ders Atama</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f7fa;
      margin: 0; padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px;
    }
    h2 {
      margin-bottom: 20px;
      color: #1f3c88;
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
    form .form-group {
      margin-bottom: 18px;
    }
    form label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #333;
    }
    form select {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 1em;
      transition: border-color 0.3s;
    }
    form select:focus {
      outline: none;
      border-color: #1f3c88;
      box-shadow: 0 0 0 3px rgba(31,60,136,0.1);
    }
    .btn {
      display: inline-block;
      padding: 12px 20px;
      background: #1f3c88;
      color: #fff;
      font-size: 1em;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .btn:hover {
      background: #163066;
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
    <h2><i class="fa fa-book"></i> Öğrenciye Ders Atama</h2>

    <?php if ($error): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="student_id">Öğrenci Seç</label>
        <select id="student_id" name="student_id" required>
          <option value="">-- Öğrenci Seçiniz --</option>
          <?php foreach ($students as $stu): ?>
            <option value="<?= $stu['id'] ?>">
              <?= htmlspecialchars($stu['name']) ?> (<?= htmlspecialchars($stu['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="course_id">Ders Seç</label>
        <select id="course_id" name="course_id" required>
          <option value="">-- Ders Seçiniz --</option>
          <?php foreach ($courses as $crs): ?>
            <option value="<?= $crs['id'] ?>"><?= htmlspecialchars($crs['course_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn"><i class="fa fa-user-plus"></i> Dersi Ata</button>
    </form>

    <a href="Gosterge_Paneli.php" class="back-link">
      <i class="fa fa-arrow-left"></i> Gösterge Paneli’ne Dön
    </a>
  </div>
</body>
</html>
