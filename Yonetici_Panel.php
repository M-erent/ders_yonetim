<?php
// Yonetici_Panel.php
session_start();
require_once 'db/db_connect.php';

// Çıkış işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Yonetici_Giris.php");
    exit();
}

// Admin oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: Yonetici_Giris.php');
    exit();
}

// Silme işlemleri
if (isset($_GET['delete']) && isset($_GET['table']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $table = $_GET['table'];
    $allowed = [
        'users', 'courses', 'announcements', 'assignments', 'grades', 'messages',
        'student_courses', 'submissions', 'attendances', 'attendance_records'
    ];
    if (in_array($table, $allowed)) {
        $db->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
        header("Location: Yonetici_Panel.php?tab=$table&msg=silindi");
        exit();
    }
}

// Hangi içeriğin yükleneceği (sidebar linkinden)
$tab = $_GET['tab'] ?? 'dashboard';

// Sekme isimleri (sadece referans, artık üstte görünmüyor)
$tabInfo = [
    'users'               => 'Kullanıcılar',
    'courses'             => 'Dersler',
    'announcements'       => 'Duyurular',
    'assignments'         => 'Ödevler',
    'grades'              => 'Notlar',
    'attendances'         => 'Yoklamalar',
    'attendance_records'  => 'Yoklama Kayıtları',
    'student_courses'     => 'Öğrenci Dersleri',
    'submissions'         => 'Ödev Teslimleri',
    'messages'            => 'Mesajlar',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yönetici Paneli</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- FontAwesome (ikonlar) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    /* ---------------------------- */
    /* Reset & Temel Ayarlar        */
    /* ---------------------------- */
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f4f4f8;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #333;
    }
    a { text-decoration: none; color: inherit; }
    ul { list-style: none; padding: 0; }
    .hidden { display: none; }

    /* ---------------------------- */
    /* Üst Bar (Header)             */
    /* ---------------------------- */
    .topbar {
      background: #b21f1f;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .topbar .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1.4rem;
      font-weight: 600;
    }
    .topbar .logo .fa-shield-alt {
      font-size: 1.6rem;
    }
    .topbar .logout-btn {
      background: #e74c3c;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 500;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .topbar .logout-btn:hover {
      background: #c0392b;
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
    /* Sidebar Başlangıçta Geniş    */
    /* ---------------------------- */
    .sidebar {
      width: 280px;
      background: #8a1919;
      color: #fff;
      display: flex;
      flex-direction: column;
      transition: width 0.3s ease;
      position: relative;
      z-index: 2;
    }
    .sidebar-header {
      padding: 24px 16px;
      background: #661111;
      text-align: center;
    }
    .sidebar-header h2 {
      font-size: 1.5em;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      white-space: nowrap;
      overflow: hidden;
    }
    .sidebar-header .fa-shield-alt {
      font-size: 1.8em;
    }
    .sidebar-title {
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    /* ---------------------------- */
    /* Menü Linkleri                */
    /* ---------------------------- */
    .menu {
      flex: 1;
      overflow-y: auto;
      padding-top: 16px;
    }
    .menu h3 {
      margin: 12px 16px;
      font-size: 1.1em;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #f0f0f0;
    }
    .menu ul li {
      margin-bottom: 6px;
    }
    .menu ul li a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: #f0f0f0;
      font-size: 1.05em;
      border-left: 4px solid transparent;
      transition: background 0.3s, border-left-color 0.3s, padding-left 0.3s;
    }
    .menu ul li a:hover,
    .menu ul li a.active {
      background: rgba(255,255,255,0.1);
      border-left-color: #fff;
      padding-left: 24px;
    }
    .menu ul li a i {
      font-size: 1.3em;
      min-width: 24px;
      text-align: center;
    }

    /* ---------------------------- */
    /* Sidebar Footer                */
    /* ---------------------------- */
    .sidebar-footer {
      padding: 16px;
      text-align: center;
      border-top: 1px solid rgba(255,255,255,0.2);
    }
    .btn-logout {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 16px;
      background: #e74c3c;
      color: #fff;
      border-radius: 4px;
      font-size: 1em;
      transition: background 0.3s;
      font-weight: 500;
    }
    .btn-logout:hover {
      background: #c0392b;
      transform: translateY(-1px);
    }

    /* ---------------------------- */
    /* Ana İçerik Bölümü (Main)      */
    /* ---------------------------- */
    .main-content {
      flex: 1;
      overflow-y: auto;
      background: #fff;
    }
    .container {
      padding: 24px;
    }
    .main-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }
    .main-header .fa-home {
      font-size: 1.6em;
      color: #b21f1f;
    }
    .main-header h1 {
      font-size: 1.8em;
      color: #333;
    }

    /* ---------------------------- */
    /* Tablo Düzeni                 */
    /* ---------------------------- */
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      margin-top: 16px;
    }
    .admin-table th, .admin-table td {
      border: 1px solid #e0e0e0;
      padding: 12px 14px;
      text-align: left;
      font-size: 0.95em;
    }
    .admin-table th {
      background: #f9f9fb;
      color: #333;
    }
    .admin-table tr:hover {
      background: #f4f4f8;
    }
    .btn-delete {
      background: #e74c3c;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-delete i { font-size: 1em; }
    .btn-delete:hover {
      background: #c0392b;
    }
    .btn-edit {
      background: #b21f1f;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      margin-right: 6px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background 0.3s;
    }
    .btn-edit i { font-size: 1em; }
    .btn-edit:hover {
      background: #8a1919;
    }
    .msg {
      color: green;
      margin-top: 12px;
      font-weight: 600;
    }

    /* ---------------------------- */
    /* Responsive Ayarlar            */
    /* ---------------------------- */
    @media (max-width: 900px) {
      .sidebar { display: none; }
      .dashboard-container { flex-direction: column; }
      .main-content { padding: 0; }
    }
  </style>
</head>
<body>

  <!-- Üst Bar -->
  <div class="topbar">
    <div class="logo">
      <i class="fas fa-shield-alt"></i>
      <span>Yönetici Paneli</span>
    </div>
    <a href="?logout=1" class="logout-btn">
      <i class="fas fa-sign-out-alt"></i> Çıkış Yap
    </a>
  </div>

  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2><i class="fas fa-shield-alt"></i> <span class="sidebar-title">ADMİN</span></h2>
      </div>
      <nav class="menu">
        <h3>Yönetim</h3>
        <ul>
          <li>
            <a href="Yonetici_Panel.php?tab=users" class="<?= ($tab==='users')?'active':'' ?>">
              <i class="fas fa-users"></i>
              <span class="sidebar-title">Kullanıcılar</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=courses" class="<?= ($tab==='courses')?'active':'' ?>">
              <i class="fas fa-book"></i>
              <span class="sidebar-title">Dersler</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=announcements" class="<?= ($tab==='announcements')?'active':'' ?>">
              <i class="fas fa-bullhorn"></i>
              <span class="sidebar-title">Duyurular</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=assignments" class="<?= ($tab==='assignments')?'active':'' ?>">
              <i class="fas fa-tasks"></i>
              <span class="sidebar-title">Ödevler</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=grades" class="<?= ($tab==='grades')?'active':'' ?>">
              <i class="fas fa-graduation-cap"></i>
              <span class="sidebar-title">Notlar</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=attendances" class="<?= ($tab==='attendances')?'active':'' ?>">
              <i class="fas fa-calendar-check"></i>
              <span class="sidebar-title">Yoklamalar</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=attendance_records" class="<?= ($tab==='attendance_records')?'active':'' ?>">
              <i class="fas fa-clipboard-list"></i>
              <span class="sidebar-title">Yoklama Kayıtları</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=student_courses" class="<?= ($tab==='student_courses')?'active':'' ?>">
              <i class="fas fa-user-tag"></i>
              <span class="sidebar-title">Öğrenci Dersleri</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=submissions" class="<?= ($tab==='submissions')?'active':'' ?>">
              <i class="fas fa-file-upload"></i>
              <span class="sidebar-title">Ödev Teslimleri</span>
            </a>
          </li>
          <li>
            <a href="Yonetici_Panel.php?tab=messages" class="<?= ($tab==='messages')?'active':'' ?>">
              <i class="fas fa-envelope"></i>
              <span class="sidebar-title">Mesajlar</span>
            </a>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="?logout=1" class="btn-logout">
          <i class="fas fa-sign-out-alt"></i> Çıkış
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <div class="container">
        <?php if (isset($_GET['msg'])): ?>
          <div class="msg"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <?php
        // Dashboard’ta default bir karşılama sayfası (eğer tab=dashboard veya tab yoksa)
        if ($tab === 'dashboard') {
            echo '
            <div class="main-header">
              <i class="fas fa-home"></i>
              <h1>Hoş geldiniz, Yönetici</h1>
            </div>
            <p>Sol menüden bir başlık seçerek istediğiniz veriyi yönetebilirsiniz.</p>
            ';
        }

        /**
         * Genel tablo çizdirme fonksiyonu
         */
        function tableList($db, $query, $fields, $table, $extra = []) {
            echo '<table class="admin-table"><thead><tr>';
            foreach ($fields as $f) {
                echo '<th>' . htmlspecialchars(ucfirst(str_replace('_',' ',$f))) . '</th>';
            }
            echo '<th>İşlemler</th></tr></thead><tbody>';

            foreach ($db->query($query) as $row) {
                echo '<tr>';
                foreach ($fields as $f) {
                    echo '<td>' . htmlspecialchars($row[$f] ?? '') . '</td>';
                }
                echo '<td style="white-space: nowrap;">';
                if (!empty($extra['edit'])) {
                    echo '<a href="' . htmlspecialchars($extra['edit'])
                         . '?id=' . intval($row['id'])
                         . '" class="btn-edit"><i class="fas fa-edit"></i> Düzenle</a>';
                }
                echo '<a href="?tab=' . htmlspecialchars($table)
                     . '&delete=' . intval($row['id'])
                     . '&table=' . htmlspecialchars($table)
                     . '" class="btn-delete" onclick="return confirm(\'Silinsin mi?\')">'
                     . '<i class="fas fa-trash-alt"></i> Sil</a>';
                echo '</td></tr>';
            }

            echo '</tbody></table>';
        }

        // Tabloları, yalnızca seçilen tab’a göre göster:
        switch ($tab) {
            case 'users':
                echo '<div class="main-header">
                        <i class="fas fa-users"></i>
                        <h1>Kullanıcılar</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT id, name, email, role, profile_image FROM users",
                  ['id','name','email','role','profile_image'],
                  'users',
                  ['edit'=>'Kullanici_Duzenleme.php']
                );
                break;

            case 'courses':
                echo '<div class="main-header">
                        <i class="fas fa-book"></i>
                        <h1>Dersler</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT c.id, c.course_name, COALESCE(u.name,'-') AS teacher 
                   FROM courses c 
                   LEFT JOIN users u ON c.teacher_id=u.id",
                  ['id','course_name','teacher'],
                  'courses',
                  ['edit'=>'Ders_Duzenleme.php']
                );
                break;

            case 'announcements':
                echo '<div class="main-header">
                        <i class="fas fa-bullhorn"></i>
                        <h1>Duyurular</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT a.id, COALESCE(u.name,'-') AS teacher, a.title, a.content, a.created_at 
                   FROM announcements a 
                   LEFT JOIN users u ON a.teacher_id=u.id",
                  ['id','teacher','title','content','created_at'],
                  'announcements',
                  ['edit'=>'Duyuru_Duzenleme.php']
                );
                break;

            case 'assignments':
                echo '<div class="main-header">
                        <i class="fas fa-tasks"></i>
                        <h1>Ödevler</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT a.id, c.course_name, a.title, a.description, a.due_date 
                   FROM assignments a 
                   LEFT JOIN courses c ON a.course_id=c.id",
                  ['id','course_name','title','description','due_date'],
                  'assignments',
                  ['edit'=>'Odev_Duzenleme.php']
                );
                break;

            case 'grades':
                echo '<div class="main-header">
                        <i class="fas fa-graduation-cap"></i>
                        <h1>Notlar</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT g.id, u.name AS student, c.course_name, g.grade, g.created_at 
                   FROM grades g 
                   LEFT JOIN users u ON g.student_id=u.id 
                   LEFT JOIN courses c ON g.course_id=c.id",
                  ['id','student','course_name','grade','created_at'],
                  'grades',
                  ['edit'=>'Not_Duzenleme.php']
                );
                break;

            case 'attendances':
                echo '<div class="main-header">
                        <i class="fas fa-calendar-check"></i>
                        <h1>Yoklamalar</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT a.id, c.course_name, a.attendance_date, a.created_at 
                   FROM attendances a 
                   LEFT JOIN courses c ON a.course_id=c.id",
                  ['id','course_name','attendance_date','created_at'],
                  'attendances',
                  ['edit'=>'Yoklama_Duzenleme.php']
                );
                break;

            case 'attendance_records':
                echo '<div class="main-header">
                        <i class="fas fa-clipboard-list"></i>
                        <h1>Yoklama Kayıtları</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT ar.id, COALESCE(u.name,'-') AS student, a.attendance_date, ar.status, ar.recorded_at 
                   FROM attendance_records ar 
                   LEFT JOIN attendances a ON ar.attendance_id=a.id 
                   LEFT JOIN users u ON ar.student_id=u.id",
                  ['id','student','attendance_date','status','recorded_at'],
                  'attendance_records',
                  ['edit'=>'Yoklama_Kayit_Duzenleme.php']
                );
                break;

            case 'student_courses':
                echo '<div class="main-header">
                        <i class="fas fa-user-tag"></i>
                        <h1>Öğrenci Dersleri</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT sc.id, COALESCE(u.name,'-') AS student, c.course_name 
                   FROM student_courses sc 
                   LEFT JOIN users u ON sc.student_id=u.id 
                   LEFT JOIN courses c ON sc.course_id=c.id",
                  ['id','student','course_name'],
                  'student_courses',
                  ['edit'=>'Ogrenci_Ders_Duzenleme.php']
                );
                break;

            case 'submissions':
                echo '<div class="main-header">
                        <i class="fas fa-file-upload"></i>
                        <h1>Ödev Teslimleri</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT s.id, COALESCE(u.name,'-') AS student, a.title AS assignment, s.file_path, s.submitted_at 
                   FROM submissions s 
                   LEFT JOIN users u ON s.student_id=u.id 
                   LEFT JOIN assignments a ON s.assignment_id=a.id",
                  ['id','student','assignment','file_path','submitted_at'],
                  'submissions'
                );
                break;

            case 'messages':
                echo '<div class="main-header">
                        <i class="fas fa-envelope"></i>
                        <h1>Mesajlar</h1>
                      </div>';
                tableList(
                  $db,
                  "SELECT m.id, COALESCE(u.name,'-') AS sender, m.title, m.content, m.created_at 
                   FROM messages m 
                   LEFT JOIN users u ON m.sender_id=u.id",
                  ['id','sender','title','content','created_at'],
                  'messages',
                  ['edit'=>'Mesaj_Duzenleme.php']
                );
                break;

            default:
                // “dashboard” ya da tanımlı olmayan bir tab ise
                echo '<div class="main-header">
                        <i class="fas fa-home"></i>
                        <h1>Hoş geldiniz, Yönetici</h1>
                      </div>
                      <p>Sol menüden bir başlık seçerek istediğiniz veriyi yönetebilirsiniz.</p>';
        }
        ?>
      </div>
    </div>
  </div>

</body>
</html>
