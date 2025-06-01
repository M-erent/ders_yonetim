<?php
// Gosterge_Paneli.php
session_start();
require_once __DIR__ . '/includes/auth_check.php';
include 'config.php'; // Veritabanı bağlantısı

// Eğer oturum açılmamışsa login sayfasına yönlendir:
if (!isset($_SESSION['user_id'])) {
    header('Location: Giris.php');
    exit();
}

// Kullanıcı verilerini veritabanından çek
$user_id = $_SESSION['user_id'];
$query = $db->prepare("SELECT name, profile_image, role FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Eğer role boşsa (örneğin eksik bir kayıt) zorunlu çık:
if (!$user || empty($user['role'])) {
    header('Location: Cikis.php');
    exit();
}

// Okunmamış duyuru sayısı
$stmt = $db->prepare("
    SELECT COUNT(*) FROM announcements a
    WHERE NOT EXISTS (
      SELECT 1 FROM announcement_reads ar
      WHERE ar.announcement_id = a.id AND ar.user_id = ?
    )
");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetchColumn();

// Öğrenciye özel veriler
$ortalama = null;
$devamsizliklar = [];
$danisman = null;
$ogr_bilgi = null;

if ($user['role'] === 'Öğrenci') {
    // Not ortalaması
    $notQuery = $db->prepare("SELECT AVG(CAST(grade AS DECIMAL(5,2))) AS ortalama FROM grades WHERE student_id = ?");
    $notQuery->execute([$user_id]);
    $not = $notQuery->fetch(PDO::FETCH_ASSOC);
    $ortalama = ($not && $not['ortalama'] !== null) ? round($not['ortalama'], 2) : null;

    // Devamsızlık
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kontrol Paneli</title>
  <!-- FontAwesome (ikonlar için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* ---------------------------- */
    /* Reset & Temel Ayarlar       */
    /* ---------------------------- */
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
    a {
      text-decoration: none;
      color: inherit;
    }
    ul {
      list-style: none;
    }
    button {
      cursor: pointer;
      border: none;
      background: none;
    }

    /* ---------------------------- */
    /* Üst Bar (Header)             */
    /* ---------------------------- */
    .topbar {
      width: 100%;
      height: 60px;
      background: #1f3c88;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      color: #fff;
    }
    .topbar .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      font-weight: 600;
    }
    .topbar .logo .fa-chalkboard-teacher {
      font-size: 1.4rem;
    }
    .topbar .profile-area {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .topbar .profile-area img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
    }
    .topbar .profile-area .name {
      font-size: 1rem;
      font-weight: 500;
    }

    /* ---------------------------- */
    /* Dashboard Container          */
    /* ---------------------------- */
    .dashboard-container {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

    /* ---------------------------- */
    /* Sidebar                       */
    /* ---------------------------- */
    .sidebar {
      width: 60px;
      background: #1f3c88;
      color: #fff;
      display: flex;
      flex-direction: column;
      transition: width 0.3s;
      position: relative;
      z-index: 2;
    }
    .sidebar:hover {
      width: 240px;
    }
    .sidebar .sidebar-header {
      height: 60px;
      background: #163066;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 0 15px;
    }
    .sidebar .sidebar-header .fa-chalkboard-teacher {
      font-size: 1.4rem;
    }
    .sidebar .sidebar-header .title {
      font-size: 1.1rem;
      font-weight: 600;
      opacity: 0;
      transition: opacity 0.3s;
      white-space: nowrap;
    }
    .sidebar:hover .sidebar-header .title {
      opacity: 1;
    }
    .sidebar nav {
      flex: 1;
      overflow-y: auto;
      margin-top: 15px;
    }
    .sidebar nav h3 {
      margin: 20px 15px 10px;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      padding-bottom: 4px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    .sidebar:hover nav h3 {
      opacity: 1;
    }

    /* Menü Grubu */
    .menu-group {
      margin-bottom: 8px;
    }
    .menu-group .group-title {
      display: flex;
      align-items: center;
      padding: 10px 15px;
      gap: 10px;
      color: #e5e7eb;
      transition: background 0.3s, padding-left 0.3s;
      border-left: 4px solid transparent;
    }
    .menu-group .group-title:hover {
      background: rgba(255,255,255,0.1);
      padding-left: 25px;
      border-left-color: #fff;
    }
    .menu-group .group-title .fa {
      min-width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }
    .menu-group .group-text {
      opacity: 0;
      white-space: nowrap;
      transition: opacity 0.3s;
    }
    .sidebar:hover .group-text {
      opacity: 1;
    }
    .menu-group .toggle-icon {
      margin-left: auto;
      transform: rotate(0deg);
      transition: transform 0.3s;
      opacity: 0;
      font-size: 0.8rem;
    }
    .sidebar:hover .toggle-icon {
      opacity: 1;
    }
    .menu-group .toggle-icon.open {
      transform: rotate(180deg);
    }

    /* Alt Menü (Submenu) */
    .submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
      background: rgba(255,255,255,0.05);
    }
    .submenu.open {
      max-height: 300px;
      transition: max-height 0.5s ease-in;
    }
    .submenu li a {
      display: flex;
      align-items: center;
      padding: 8px 40px;
      color: #e5e7eb;
      font-size: 0.9rem;
      transition: background 0.3s, padding-left 0.3s;
    }
    .submenu li + li a {
      margin-top: 2px;
    }
    .submenu li a .fa {
      margin-right: 8px;
      min-width: 16px;
      text-align: center;
      font-size: 0.9rem;
    }
    .submenu li a:hover {
      background: rgba(255,255,255,0.1);
      padding-left: 50px;
    }

    /* Sidebar Footer */
    .sidebar-footer {
      padding: 15px;
      border-top: 1px solid rgba(255,255,255,0.2);
      text-align: center;
    }
    .sidebar-footer .btn-logout {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      background: #e74c3c;
      border-radius: 6px;
      font-size: 0.85rem;
      transition: background 0.3s, transform 0.2s;
    }
    .sidebar-footer .btn-logout:hover {
      background: #c0392b;
      transform: translateY(-2px);
    }

    /* ---------------------------- */
    /* Main Content                 */
    /* ---------------------------- */
    .main-content {
      flex: 1;
      background: #fafbfd;
      padding: 30px 40px;
      overflow-y: auto;
      position: relative;
    }
    .main-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 30px;
    }
    .main-header .fa-home {
      font-size: 1.6rem;
      color: #1f3c88;
    }
    .main-header h1 {
      font-size: 1.8rem;
      color: #333;
      font-weight: 500;
    }

    /* ---------------------------- */
    /* Hoş Geldin Kartı             */
    /* ---------------------------- */
    .welcome-card {
      background: #fff;
      padding: 25px 30px;
      border-radius: 8px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
    }
    .welcome-card .icon {
      font-size: 2rem;
      color: #1f3c88;
    }
    .welcome-card .text {
      flex: 1;
    }
    .welcome-card .text h2 {
      font-size: 1.3rem;
      color: #1f3c88;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 5px;
    }
    .welcome-card .text p {
      font-size: 0.95rem;
      color: #555;
    }
    .welcome-card img.profile-img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #1f3c88;
    }

    /* ---------------------------- */
    /* Bilgi Kartları               */
    /* ---------------------------- */
    .info-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
    }
    .info-card {
      flex: 1;
      min-width: 240px;
      background: #fff;
      border-radius: 8px;
      padding: 20px 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      position: relative;
      transition: transform 0.3s;
    }
    .info-card:hover {
      transform: translateY(-4px);
    }
    .info-card .icon {
      position: absolute;
      top: 18px;
      right: 18px;
      font-size: 1.6rem;
      color: rgba(31,60,136,0.15);
    }
    .info-card strong {
      display: block;
      font-size: 1rem;
      margin-bottom: 12px;
      color: #1f3c88;
    }
    .info-card p, .info-card ul {
      margin-top: 6px;
      font-size: 0.95rem;
      color: #333;
      line-height: 1.4;
    }
    .info-card hr {
      border: none;
      border-top: 1px solid #e0e0e0;
      margin: 12px 0;
    }
    .info-card ul {
      padding-left: 18px;
    }
    .info-card ul li {
      margin-bottom: 6px;
    }

    /* ---------------------------- */
    /* Ortalama Kartı               */
    /* ---------------------------- */
    .average-card {
      margin-top: 24px;
      padding: 18px 22px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      display: inline-block;
      font-size: 1.1rem;
      font-weight: bold;
      color: #1f3c88;
    }

    /* ---------------------------- */
    /* Responsive                    */
    /* ---------------------------- */
    @media (max-width: 1024px) {
      .sidebar:hover {
        width: 180px;
      }
      .sidebar:hover .sidebar-header .title {
        opacity: 1;
      }
      .welcome-card {
        flex-direction: column;
        align-items: flex-start;
      }
      .welcome-card img.profile-img {
        margin-top: 15px;
      }
    }
    @media (max-width: 768px) {
      .dashboard-container {
        flex-direction: column;
      }
      .sidebar {
        width: 0;
        position: absolute;
        height: 100vh;
        transform: translateX(-100%);
        transition: transform 0.3s;
      }
      .sidebar.open {
        transform: translateX(0);
        width: 180px;
      }
      .topbar .logo {
        font-size: 1rem;
      }
      .main-content {
        padding: 20px;
      }
      .toggle-sidebar {
        display: block;
        position: absolute;
        top: 12px;
        left: 12px;
        background: #1f3c88;
        color: #fff;
        border: none;
        padding: 8px;
        border-radius: 4px;
        z-index: 5;
      }
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      // Alt menü aç/kapa işlemi
      document.querySelectorAll('.group-title').forEach(function(title){
        title.addEventListener('click', function(){
          this.classList.toggle('open');
          const submenu = this.nextElementSibling;
          submenu.classList.toggle('open');
        });
      });

      // Mobilde sidebar aç/kapa
      const toggleBtn = document.querySelector('.toggle-sidebar');
      if (toggleBtn) {
        toggleBtn.addEventListener('click', function(){
          document.querySelector('.sidebar').classList.toggle('open');
        });
      }
    });
  </script>
</head>
<body>
  <!-- Üst Bar -->
  <div class="topbar">
    <div class="logo">
      <i class="fa fa-chalkboard-teacher"></i>
      <span>Ders Yönetim Sistemi</span>
    </div>
    <div class="profile-area">
      <span class="name"><?= htmlspecialchars($user['name']); ?></span>
      <img src="<?= htmlspecialchars($user['profile_image']); ?>" alt="Profil Fotoğrafı">
    </div>
    <!-- Mobilde gözükecek sidebar toggle -->
    <button class="toggle-sidebar"><i class="fa fa-bars"></i></button>
  </div>

  <!-- Dashboard Container -->
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <i class="fa fa-chalkboard-teacher"></i>
        <span class="title">Ders Yönetim</span>
      </div>
      <nav>
        <?php if ($user['role'] === 'Öğretmen'): ?>
          <h3>Öğretmen</h3>
          <!-- Dersler Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-book"></i>
              <span class="group-text">Dersler</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Ders_Ekleme.php"><i class="fa fa-plus-circle"></i> Ders Ekle</a></li>
              <li><a href="Ders_Sil.php"><i class="fa fa-trash"></i> Ders Sil</a></li>
              <li><a href="Ogrenciye_ders_atama.php"><i class="fa fa-user-plus"></i> Öğrenciye Ders Ata</a></li>
            </ul>
          </div>
          <!-- Duyurular Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-bullhorn"></i>
              <span class="group-text">Duyurular</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li style="position:relative;">
                <a href="Duyuru_Gor.php" id="sidebarAnnouncements">
                  <i class="fa fa-bullhorn"></i> Tüm Duyurular
                  <?php if ($unread_count > 0): ?>
                    <span class="notif-count-sidebar" id="sidebarNotifCount"><?= $unread_count ?></span>
                  <?php endif; ?>
                </a>
              </li>
              <li><a href="Duyuru_Ekleme.php"><i class="fa fa-plus"></i> Duyuru Ekle</a></li>
            </ul>
          </div>
          <!-- Ödevler Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-tasks"></i>
              <span class="group-text">Ödevler</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Odev_Ekleme.php"><i class="fa fa-plus-circle"></i> Ödev Ekle</a></li>
              <li><a href="Odev_Gor.php"><i class="fa fa-clipboard-list"></i> Ödevler &amp; Teslim</a></li>
            </ul>
          </div>
          <!-- Notlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-pen"></i>
              <span class="group-text">Notlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Not_Ekleme.php"><i class="fa fa-plus-circle"></i> Not Ekle</a></li>
              <li><a href="Not_Gor.php"><i class="fa fa-graduation-cap"></i> Notları Gör</a></li>
            </ul>
          </div>
          <!-- Yoklama Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-calendar-check"></i>
              <span class="group-text">Yoklama</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Yoklama_Ekleme.php"><i class="fa fa-plus-circle"></i> Yoklama Al</a></li>
              <li><a href="Yoklama_Gor.php"><i class="fa fa-eye"></i> Yoklamalar</a></li>
            </ul>
          </div>
          <!-- Mesajlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-envelope"></i>
              <span class="group-text">Mesajlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Mesaj_Gonder.php"><i class="fa fa-paper-plane"></i> Mesaj Gönder</a></li>
              <li><a href="Gelen_Kutusu.php"><i class="fa fa-inbox"></i> Gelen Kutusu</a></li>
            </ul>
          </div>
          <!-- Profil & Ayarlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-user-cog"></i>
              <span class="group-text">Profil & Ayarlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Profil.php"><i class="fa fa-user"></i> Profil Bilgisi</a></li>
            </ul>
          </div>
        <?php else: /* Öğrenci Menüsü */ ?>
          <h3>Öğrenci</h3>
          <!-- Dersler Grubu (Öğrenci ders seçimi için) -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-book"></i>
              <span class="group-text">Dersler</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Ogrenci_ders_secme_iptal.php"><i class="fa fa-list-alt"></i> Ders Seç / İptal Et</a></li>
            </ul>
          </div>
          <!-- Ders Programı Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-calendar-alt"></i>
              <span class="group-text">Ders Programı</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Ogrenci_Ders_Programi.php"><i class="fa fa-calendar"></i> Ders Programım</a></li>
            </ul>
          </div>
          <!-- Duyurular Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-bullhorn"></i>
              <span class="group-text">Duyurular</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li style="position:relative;">
                <a href="Duyuru_Gor.php" id="sidebarAnnouncements">
                  <i class="fa fa-bullhorn"></i> Tüm Duyurular
                  <?php if ($unread_count > 0): ?>
                    <span class="notif-count-sidebar" id="sidebarNotifCount"><?= $unread_count ?></span>
                  <?php endif; ?>
                </a>
              </li>
            </ul>
          </div>
          <!-- Ödevler Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-tasks"></i>
              <span class="group-text">Ödevler</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Odev.php"><i class="fa fa-clipboard"></i> Ödevler &amp; Teslim</a></li>
            </ul>
          </div>
          <!-- Notlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-graduation-cap"></i>
              <span class="group-text">Notlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Ogrenci_Notlari.php"><i class="fa fa-clipboard-list"></i> Notlarım</a></li>
            </ul>
          </div>
          <!-- Yoklama Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-calendar-check"></i>
              <span class="group-text">Yoklamalar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Ogrenci_Yoklama.php"><i class="fa fa-user-check"></i> Yoklamalarım</a></li>
            </ul>
          </div>
          <!-- Mesajlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-envelope"></i>
              <span class="group-text">Mesajlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Mesaj_Gonder.php"><i class="fa fa-paper-plane"></i> Mesaj Gönder</a></li>
              <li><a href="Gelen_Kutusu.php"><i class="fa fa-inbox"></i> Gelen Kutusu</a></li>
            </ul>
          </div>
          <!-- Profil & Ayarlar Grubu -->
          <div class="menu-group">
            <div class="group-title">
              <i class="fa fa-user-cog"></i>
              <span class="group-text">Profil & Ayarlar</span>
              <i class="fa fa-chevron-down toggle-icon"></i>
            </div>
            <ul class="submenu">
              <li><a href="Profil.php"><i class="fa fa-user"></i> Profil Bilgisi</a></li>
            </ul>
          </div>
        <?php endif; ?>
      </nav>
      <div class="sidebar-footer">
        <a href="Cikis.php" class="btn-logout">
          <i class="fa fa-sign-out-alt"></i>
          <span>Çıkış</span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="main-header">
        <i class="fa fa-home"></i>
        <h1>Ana Sayfa</h1>
      </header>

      <!-- Hoşgeldin Kartı -->
      <section class="welcome-card">
        <div class="icon"><i class="fa fa-hand-point-right"></i></div>
        <div class="text">
          <h2>Hoş Geldin, <?= htmlspecialchars($user['name']); ?></h2>
          <p>Yandaki menüden yapmak istediğin işlemi seçebilirsin.</p>
        </div>
        <img class="profile-img" src="<?= htmlspecialchars($user['profile_image']); ?>" alt="Profil Fotoğrafı">
      </section>

      <?php if ($user['role'] === 'Öğrenci'): ?>
        <!-- Bilgi Kartları -->
        <div class="info-cards">
          <!-- Not Ortalaması Kartı -->
          <div class="info-card">
            <i class="icon fa fa-chart-line"></i>
            <strong>Ders Notu Ortalamanız:</strong>
            <?php if ($ortalama !== null): ?>
              <p style="margin-top: 6px; font-size:1.3rem; color:#1f3c88;"><?= $ortalama ?></p>
            <?php else: ?>
              <p style="margin-top: 6px; color:#666;">Henüz notunuz yok.</p>
            <?php endif; ?>
          </div>

          <!-- Devamsızlık Kartı -->
          <div class="info-card">
            <i class="icon fa fa-user-clock"></i>
            <strong>Ders Bazında Devamsızlık</strong>
            <hr>
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

          <!-- Danışman & Öğrenim Bilgisi Kartı -->
          <div class="info-card">
            <i class="icon fa fa-user-tie"></i>
            <strong>Danışman</strong>
            <hr>
            <?php if ($danisman && $danisman['name']): ?>
              <p><?= htmlspecialchars($danisman['name']) ?></p>
              <a href="mailto:<?= htmlspecialchars($danisman['email']) ?>" style="color:#1f3c88;"><?= htmlspecialchars($danisman['email']) ?></a>
            <?php else: ?>
              <p style="color:#666;">Tanımlı değil.</p>
            <?php endif; ?>
            <hr>
            <i class="icon fa fa-university"></i>
            <strong>Öğrenim</strong>
            <hr>
            <?php if ($ogr_bilgi): ?>
              <p><?= htmlspecialchars($ogr_bilgi['faculty'] ?? '') ?></p>
              <p><?= htmlspecialchars($ogr_bilgi['department'] ?? '') ?></p>
              <p><?= htmlspecialchars($ogr_bilgi['class'] ?? '') ?>. Sınıf</p>
            <?php else: ?>
              <p style="color:#666;">Bilgi yok.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Orta Bölüm: Yeni Özellikler İçin Alan -->
      <section style="margin-top: 40px;">
        <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
          <h2 style="font-size:1.2rem; color:#1f3c88; margin-bottom:15px;">
            <i class="fa fa-star"></i> Yeni Özellikler
          </h2>
          <p style="color:#555; font-size:1rem;">
            Bu alana ilerleyen dönemde ekleyeceğiniz raporlar, grafikler veya bildiriler gibi yenilikleri entegre edebilirsiniz.
          </p>
        </div>
      </section>

    </main>
  </div>
</body>
</html>
