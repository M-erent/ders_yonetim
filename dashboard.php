<?php
// dashboard.php
require_once __DIR__ . '/includes/auth_check.php';
include 'config.php'; // Veritabanı bağlantısı

// Kullanıcı verilerini veritabanından çekme
$user_id = $_SESSION['user_id'];
$query = $db->prepare("SELECT name, profile_image FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Eğer öğrenci ise ek alanları çekiyoruz
$ortalama = null;
$devamsizliklar = [];
$danisman = null;
$ogr_bilgi = null;

if ($_SESSION['role'] === 'Öğrenci') {
    // Not ortalaması
    $notQuery = $db->prepare("SELECT AVG(CAST(grade AS DECIMAL(5,2))) AS ortalama FROM grades WHERE student_id = ?");
    $notQuery->execute([$user_id]);
    $not = $notQuery->fetch(PDO::FETCH_ASSOC);
    $ortalama = $not && $not['ortalama'] !== null ? round($not['ortalama'], 2) : null;

    // Ders bazında devamsızlık
    $dev_sorgu = $db->prepare("
        SELECT c.course_name,
               COUNT(CASE WHEN ar.status = 'absent' THEN 1 END) AS devamsizlik
        FROM student_courses sc
        JOIN courses c ON sc.course_id = c.id
        LEFT JOIN attendances a ON a.course_id = c.id
        LEFT JOIN attendance_records ar ON ar.attendance_id = a.id AND ar.student_id = ?
        WHERE sc.student_id = ?
        GROUP BY c.id
    ");
    $dev_sorgu->execute([$user_id, $user_id]);
    $devamsizliklar = $dev_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // Danışman bilgisi
    $danisman_sorgu = $db->prepare("
        SELECT u2.name, u2.email
        FROM users u1
        LEFT JOIN users u2 ON u1.advisor_id = u2.id
        WHERE u1.id = ?
    ");
    $danisman_sorgu->execute([$user_id]);
    $danisman = $danisman_sorgu->fetch(PDO::FETCH_ASSOC);

    // Öğrenim bilgisi
    $ogr_bilgi_sorgu = $db->prepare("SELECT faculty, department, class FROM users WHERE id = ?");
    $ogr_bilgi_sorgu->execute([$user_id]);
    $ogr_bilgi = $ogr_bilgi_sorgu->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kontrol Paneli</title>
  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .info-cards { display: flex; gap:24px; margin-top:24px; flex-wrap:wrap;}
    .info-card {flex:1; min-width:220px; background:#e6f7ff; border-radius:8px; color:#1f3c88; font-size:1.1em; font-weight:500; box-shadow:0 2px 8px rgba(31,60,136,0.08); padding:20px;}
    .info-card ul {margin:10px 0 0 18px; padding:0;}
    .info-card hr {border:none; border-top:1px solid #b0c4de; margin:8px 0;}
    @media (max-width:900px){.info-cards{flex-direction:column;}.info-card{min-width:unset;}}
  </style>
  <script>
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('collapsed');
    }
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.menu a').forEach(a => {
        a.addEventListener('mouseenter', () => a.classList.add('hovered'));
        a.addEventListener('mouseleave', () => a.classList.remove('hovered'));
      });
      document.querySelector('.btn-logout').addEventListener('mouseenter', e => {
        e.target.classList.add('hovered');
      });
      document.querySelector('.btn-logout').addEventListener('mouseleave', e => {
        e.target.classList.remove('hovered');
      });
    });
  </script>
  <style>
    /* Mevcut stillerin burada aynen devam edecek */
    .dashboard-page .dashboard-container {
      display: flex;
      min-height: 100vh;
      background: #f4f7fa;
    }
    .dashboard-page .sidebar {
      width: 260px;
      background: #1f3c88;
      color: #fff;
      display: flex;
      flex-direction: column;
      transition: width 0.3s;
    }
    .dashboard-page .sidebar.collapsed { width: 60px; }
    .dashboard-page .sidebar-header {
      padding: 20px;
      background: #163066;
      text-align: center;
    }
    .dashboard-page .sidebar-header h2 {
      margin: 0; font-size: 1.4em; display: flex; align-items: center; justify-content: center;
    }
    .dashboard-page .sidebar-header h2 .fa { margin-right: 8px; }
    .dashboard-page .menu { flex: 1; overflow-y: auto; padding-top: 10px; }
    .dashboard-page .menu h3 {
      margin: 10px 20px; font-size: 1.1em; text-transform: uppercase; letter-spacing: 1px;
      border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 5px;
    }
    .dashboard-page .menu ul { list-style: none; padding: 0; margin: 0; }
    .dashboard-page .menu li + li { margin-top: 5px; }
    .dashboard-page .menu a {
      display: flex; align-items: center; padding: 12px 20px; color: #e5e7eb; text-decoration: none;
      transition: background 0.3s, padding-left 0.3s; border-left: 4px solid transparent;
    }
    .dashboard-page .menu a .fa { margin-right: 12px; }
    .dashboard-page .menu a.hovered,
    .dashboard-page .menu a:hover {
      background: rgba(255,255,255,0.1); padding-left: 24px; border-left-color: #fff;
    }
    .dashboard-page .sidebar-footer {
      padding: 20px; text-align: center; border-top: 1px solid rgba(255,255,255,0.2);
    }
    .dashboard-page .btn-logout {
      display: inline-block; padding: 10px 20px; background: #e74c3c; color: #fff;
      border-radius: 4px; text-decoration: none; transition: background 0.3s, transform 0.2s;
    }
    .dashboard-page .btn-logout.hovered,
    .dashboard-page .btn-logout:hover {
      background: #c0392b; transform: translateY(-2px);
    }
    .dashboard-page .main-content { flex: 1; padding: 40px; }
    .dashboard-page .main-header { display: flex; align-items: center; margin-bottom: 30px; }
    .dashboard-page .main-header .header-icon { font-size: 1.5em; margin-right: 12px; color: #1f3c88; }
    .dashboard-page .main-header h1 { margin: 0; font-size: 2em; color: #333; }
    .dashboard-page .welcome-card {
      background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
      display: flex; flex-direction: column; align-items: flex-start; animation: fadeIn 0.5s ease-out;
    }
    .dashboard-page .welcome-card h2 {
      margin: 0 0 10px; font-size: 1.3em; color: #1f3c88;
      display: flex; align-items: center; gap: 10px;
    }
    .dashboard-page .welcome-card h2 .fa { margin-right: 10px; }
    .dashboard-page .welcome-card p { margin: 0; color: #555; font-size: 1em; }
    .average-card {
      margin: 24px 0 0 0; padding: 20px 24px; background: #e6f7ff; border-radius: 8px; color: #1f3c88;
      font-size: 1.2em; font-weight: bold; box-shadow: 0 2px 8px rgba(31,60,136,0.08); display: inline-block;
    }
    .profile-img {
      width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #1f3c88;
    }
    .dashboard-page .toggle-sidebar {
      display: none; position: absolute; top: 20px; left: 20px; background: #1f3c88; color: #fff;
      border: none; padding: 10px; border-radius: 4px; cursor: pointer;
    }
    @media (max-width: 768px) {
      .dashboard-page .dashboard-container { flex-direction: column; }
      .dashboard-page .sidebar { width: 100%; }
      .dashboard-page .main-content { padding: 20px; }
      .dashboard-page .toggle-sidebar { display: block; }
    }
  </style>
</head>
<body class="dashboard-page">
  <!-- Küçük ekranlarda sidebar'ı açıp kapatmak için toggle buton -->
  <button class="toggle-sidebar" onclick="toggleSidebar()"><i class="fa fa-bars"></i></button>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2><i class="fa fa-chalkboard-teacher"></i> Ders Yönetim Sayfası</h2>
      </div>
      <nav class="menu">
        <?php if ($_SESSION['role'] === 'Öğretmen'): ?>
          <h3>Öğretmen</h3>
          <ul>
            <li><a href="add_course.php"><i class="fa fa-plus-circle"></i> Ders Ekle</a></li>
            <li><a href="remove_course.php"><i class="fa fa-trash"></i> Ders Sil</a></li>
            <li><a href="assign_student.php"><i class="fa fa-user-plus"></i> Öğrenci Ata</a></li>
            <li><a href="add_announcement.php"><i class="fa fa-bullhorn"></i> Duyuru Ekle</a></li>
            <li><a href="view_announcements.php"><i class="fa fa-list"></i> Duyurularım</a></li>
            <li><a href="add_assignment.php"><i class="fa fa-tasks"></i> Ödev Ekle</a></li>
            <li><a href="view_assignments.php"><i class="fa fa-clipboard-list"></i> Ödevler &amp; Teslimler</a></li>
            <li><a href="add_grade.php"><i class="fa fa-pen"></i> Not Ekle</a></li>
            <li><a href="view_grades.php"><i class="fa fa-graduation-cap"></i> Notları Gör</a></li>
            <li><a href="add_attendance.php"><i class="fa fa-calendar-check"></i> Yoklama Al</a></li>
            <li><a href="view_attendance.php"><i class="fa fa-eye"></i> Yoklamalar</a></li>
            <li><a href="send_message.php"><i class="fa fa-envelope"></i> Mesaj Gönder</a></li>
            <li><a href="inbox.php"><i class="fa fa-inbox"></i> Gelen Kutusu</a></li>
            <li><a href="profile.php"><i class="fa fa-user-cog"></i> Profil &amp; Ayarlar</a></li>
          </ul>
        <?php else: ?>
          <h3>Öğrenci</h3>
          <ul>
            <li><a href="student_schedule.php"><i class="fa fa-calendar-alt"></i> Ders Programım</a></li>
            <li><a href="view_announcements.php"><i class="fa fa-bullhorn"></i> Duyurular</a></li>
            <li><a href="assignments.php"><i class="fa fa-tasks"></i> Ödevler &amp; Teslim</a></li>
            <li><a href="student_grades.php"><i class="fa fa-graduation-cap"></i> Notlarım</a></li>
            <li><a href="student_attendance.php"><i class="fa fa-calendar-check"></i> Yoklamalarım</a></li>
            <li><a href="send_message.php"><i class="fa fa-envelope"></i> Mesaj Gönder</a></li>
            <li><a href="inbox.php"><i class="fa fa-inbox"></i> Gelen Kutusu</a></li>
            <li><a href="profile.php"><i class="fa fa-user-cog"></i> Profil &amp; Ayarlar</a></li>
          </ul>
        <?php endif; ?>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out-alt"></i> Çıkış Yap</a>
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <i class="fa fa-home header-icon"></i>
        <h1><?= htmlspecialchars($_SESSION['user_name']); ?></h1>
      </header>
      <section class="welcome-card">
        <h2>
          <i class="fa fa-hand-point-right"></i> Hoş Geldin, 
          <span><?= htmlspecialchars($user['name']); ?></span>
          <img src="<?= htmlspecialchars($user['profile_image']); ?>" alt="Profil Fotoğrafı" class="profile-img">
        </h2> 
        <p>Yapmak istediğin işlemi yandaki menüden seçebilirsin.</p>
      </section>
      <?php if ($_SESSION['role'] === 'Öğrenci'): ?>
        <div class="info-cards">
          <!-- Not Ortalaması -->
          <div class="info-card">
            <i class="fa fa-chart-line"></i> Ders Notu Ortalamanız:<br>
            <?php if ($ortalama !== null): ?>
              <span style="font-size:1.4em; color:#1f3c88;"><?= $ortalama ?></span>
            <?php else: ?>
              <span style="color:#666;">Henüz notunuz yok.</span>
            <?php endif; ?>
          </div>
          <!-- Devamsızlık -->
          <div class="info-card">
            <i class="fa fa-user-clock"></i> <b>Ders Bazında Devamsızlık</b>
            <ul>
              <?php foreach($devamsizliklar as $d): ?>
                <li>
                  <?= htmlspecialchars($d['course_name']) ?>:
                  <span style="color:#e74c3c;"><?= intval($d['devamsizlik']) ?> yok</span>
                </li>
              <?php endforeach; ?>
              <?php if(empty($devamsizliklar)): ?>
                <li>Devamsızlık bilgisi yok.</li>
              <?php endif; ?>
            </ul>
          </div>
          <!-- Danışman ve Öğrenim Bilgisi -->
          <div class="info-card">
            <i class="fa fa-user-tie"></i> <b>Danışman:</b><br>
            <?php if ($danisman && $danisman['name']): ?>
              <?= htmlspecialchars($danisman['name']) ?><br>
              <a href="mailto:<?= htmlspecialchars($danisman['email']) ?>" style="color:#1f3c88;"><?= htmlspecialchars($danisman['email']) ?></a>
            <?php else: ?>
              <span style="color:#666;">Tanımlı değil.</span>
            <?php endif; ?>
            <hr>
            <i class="fa fa-university"></i> <b>Öğrenim:</b><br>
            <?php if ($ogr_bilgi): ?>
              <?= htmlspecialchars($ogr_bilgi['faculty']) ?><br>
              <?= htmlspecialchars($ogr_bilgi['department']) ?><br>
              <?= htmlspecialchars($ogr_bilgi['class']) ?>. Sınıf
            <?php else: ?>
              <span style="color:#666;">Bilgi yok.</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>