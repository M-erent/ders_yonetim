<?php
// ders_ekle.php
require_once 'includes/auth_check.php';
require_once 'db/db_connect.php';

// Sadece "Öğretmen" rolündeki kullanıcı erişebilsin
if ($_SESSION['role'] !== 'Öğretmen') {
    header('Location: Gosterge_Paneli.php');
    exit();
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name  = trim($_POST['course_name']);
    $day_of_week  = $_POST['day_of_week'];
    $start_time   = $_POST['start_time'];
    $teacher_id   = $_SESSION['user_id'];

    $sql = "
      INSERT INTO courses (course_name, day_of_week, start_time, teacher_id)
      VALUES (?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $course_name, $day_of_week, $start_time, $teacher_id);
    if ($stmt->execute()) {
        $msg = 'Ders başarıyla eklendi.';
    } else {
        $msg = 'Hata oluştu: ' . $stmt->error;
    }
    $stmt->close();
}

// Ders günü seçenekleri
$days = [
    'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma'
];

// 09:00'dan 15:00'e kadar 50'er dakikalık dilimler  
// 09:00 → 09:50 → 10:40 → 11:30 → 12:20 → 13:10 → 14:00
$times = [
    '09:00', '09:50', '10:40', '11:30', '12:20', '13:10', '14:00'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Ders Ekle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <!-- Google Fonts: Poppins --> 
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f4f7fa;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #333;
    }
    a { text-decoration: none; color: inherit; }
    input, select, button { font-family: inherit; }

    /* Üst Bar */
    .topbar {
      width: 100%;
      background: #1f3c88;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar .title {
      font-size: 1.4rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .topbar .back-btn {
      background: #e74c3c;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      transition: background 0.3s ease;
    }
    .topbar .back-btn:hover {
      background: #c0392b;
    }

    /* Form Kartı */
    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .card {
      width: 100%;
      max-width: 500px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .card-header {
      background: #1f3c88;
      color: #fff;
      padding: 30px 20px;
      text-align: center;
      position: relative;
    }
    .card-header i {
      font-size: 2.5rem;
      margin-bottom: 10px;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    .card-header h2 {
      font-size: 1.6rem;
      margin-bottom: 5px;
      font-weight: 600;
    }
    .card-header p {
      font-size: 0.9rem;
      opacity: 0.8;
      font-weight: 300;
    }

    .card-body {
      padding: 30px 25px;
    }
    .msg {
      text-align: center;
      font-weight: 600;
      color: green;
      margin-bottom: 16px;
    }
    .error {
      text-align: center;
      font-weight: 600;
      color: red;
      margin-bottom: 16px;
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #333;
    }
    .input-icon {
      position: relative;
    }
    .input-icon i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
    }
    .form-control {
      width: 100%;
      padding: 12px 14px 12px 40px;
      border: 2px solid #e0e7ff;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-control:focus {
      outline: none;
      border-color: #1f3c88;
      box-shadow: 0 0 0 3px rgba(31,60,136,0.1);
    }

    select.form-control {
      appearance: none;
      background: url("data:image/svg+xml;charset=UTF-8,<svg fill='%238a8a8a' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>") no-repeat calc(100% - 12px) center;
      background-size: 16px;
    }

    .btn-submit {
      width: 100%;
      padding: 12px;
      background: #1f3c88;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.3s ease;
    }
    .btn-submit:hover {
      background: #16306a;
      transform: translateY(-2px);
    }

    @media (max-width: 480px) {
      .card-header h2 { font-size: 1.4rem; }
      .form-control { padding: 10px 12px 10px 36px; }
      .btn-submit { padding: 10px; font-size: 0.95rem; }
    }
  </style>
</head>
<body>

  <!-- Üst Bar -->
  <div class="topbar">
    <div class="title"><i class="fas fa-book-open"></i> Ders Ekle</div>
    <a href="Gosterge_Paneli.php" class="back-btn"><i class="fas fa-arrow-left"></i> Geri</a>
  </div>

  <!-- Form Kartı -->
  <div class="container">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-plus-circle"></i>
        <h2>Yeni Ders Oluştur</h2>
        <p>Ders adı, günü ve saati seçin</p>
      </div>
      <div class="card-body">
        <?php if (!empty($msg)): ?>
          <div class="<?= strpos($msg, 'Hata') !== false ? 'error' : 'msg' ?>">
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="">
          <!-- Ders Adı -->
          <div class="form-group">
            <label for="course_name">Ders Adı</label>
            <div class="input-icon">
              <i class="fas fa-book"></i>
              <input
                type="text"
                id="course_name"
                name="course_name"
                class="form-control"
                placeholder="Ders adı girin"
                required
              />
            </div>
          </div>

          <!-- Ders Günü -->
          <div class="form-group">
            <label for="day_of_week">Ders Günü</label>
            <div class="input-icon">
              <i class="fas fa-calendar-day"></i>
              <select id="day_of_week" name="day_of_week" class="form-control" required>
                <option value="">-- Gün Seçiniz --</option>
                <?php foreach ($days as $d): ?>
                  <option value="<?= $d ?>"><?= $d ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Başlangıç Saati -->
          <div class="form-group">
            <label for="start_time">Başlangıç Saati</label>
            <div class="input-icon">
              <i class="fas fa-clock"></i>
              <select id="start_time" name="start_time" class="form-control" required>
                <option value="">-- Saat Seçiniz --</option>
                <?php foreach ($times as $t): ?>
                  <option value="<?= $t ?>"><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" class="btn-submit"><i class="fas fa-check-circle"></i> Dersi Ekle</button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
