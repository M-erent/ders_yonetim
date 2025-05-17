<?php
session_start();
require_once 'config.php';

// Çıkış işlemi (ilk kontrol!)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Admin oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
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
        $db->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$id]);
        header("Location: admin_panel.php?tab=$table&msg=silindi");
        exit();
    }
}

// Aktif sekme
$tab = $_GET['tab'] ?? 'users';

// Tablo başlıkları ve isimleri
$tabInfo = [
    'users' => 'Kullanıcılar',
    'courses' => 'Dersler',
    'announcements' => 'Duyurular',
    'assignments' => 'Ödevler',
    'grades' => 'Notlar',
    'attendances' => 'Yoklamalar',
    'attendance_records' => 'Yoklama Kayıtları',
    'student_courses' => 'Öğrenci Dersleri',
    'submissions' => 'Ödev Teslimleri',
    'messages' => 'Mesajlar'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Admin Paneli</title>
  <link rel="stylesheet" href="assets/css/style.css?v=2">
  <style>
    body { font-family:Arial; background:#f4f7fa; }
    .admin-nav { background:#1f3c88; color:#fff; padding:12px 24px; }
    .admin-nav h1 { display:inline; margin:0; font-size:24px;}
    .admin-nav a { color:#fff; margin-left:24px; text-decoration:none; font-weight:bold; }
    .admin-tabs { margin:24px 0 12px 0; }
    .admin-tabs a { background:#e5e7eb; color:#222; padding:8px 16px; margin-right:8px; border-radius:6px 6px 0 0; text-decoration:none; }
    .admin-tabs a.active { background:#fff; color:#1f3c88; font-weight:bold; border-bottom:2px solid #1f3c88;}
    .admin-table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; }
    .admin-table th, .admin-table td { border:1px solid #e0e0e0; padding:8px 10px; }
    .admin-table th { background:#f4f4fb; }
    .admin-table tr:hover { background:#f9fafe; }
    .btn-delete { background:#e74c3c; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; }
    .btn-edit { background:#1f3c88; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; }
    .msg { color:green; margin-bottom:16px;}
    .admin-logout {float:right;background:#c0392b;padding:8px 16px;color:#fff;text-decoration:none;border-radius:6px;}
    .admin-logout:hover { background:#a93226;}
  </style>
</head>
<body>
<div class="admin-nav">
    <h1>Admin Paneli</h1>
    <a href="admin_panel.php">Ana Sayfa</a>
    <a href="?tab=users">Kullanıcılar</a>
    <a href="?tab=courses">Dersler</a>
    <a href="?tab=announcements">Duyurular</a>
    <a href="?tab=assignments">Ödevler</a>
    <a href="?tab=grades">Notlar</a>
    <a href="?tab=attendances">Yoklamalar</a>
    <a href="?tab=attendance_records">Yoklama Kayıtları</a>
    <a href="?tab=student_courses">Öğrenci Dersleri</a>
    <a href="?tab=submissions">Teslimler</a>
    <a href="?tab=messages">Mesajlar</a>
    <a href="admin_panel.php?logout=1" class="admin-logout">Çıkış Yap</a>
</div>
<div class="admin-tabs">
    <?php foreach($tabInfo as $t => $title): ?>
        <a href="?tab=<?=$t?>" class="<?=($tab==$t)?'active':''?>"><?=$title?></a>
    <?php endforeach; ?>
</div>
<div class="container" style="padding:24px;">
<?php if(isset($_GET['msg'])): ?>
    <div class="msg"><?=htmlspecialchars($_GET['msg'])?></div>
<?php endif; ?>

<?php
function tableList($db, $query, $fields, $table, $extra = []) {
    echo '<table class="admin-table"><tr>';
    foreach ($fields as $f) echo '<th>' . $f . '</th>';
    echo '<th>İşlem</th></tr>';
    foreach ($db->query($query) as $row) {
        echo '<tr>';
        foreach ($fields as $f) echo '<td>' . htmlspecialchars($row[$f]) . '</td>';
        echo '<td>';
        if (!empty($extra['edit'])) echo '<a href="'.$extra['edit'].'?id='.$row['id'].'" class="btn-edit">Düzenle</a> ';
        echo '<a href="?tab='.$table.'&delete='.$row['id'].'&table='.$table.'" class="btn-delete" onclick="return confirm(\'Silinsin mi?\')">Sil</a>';
        echo '</td></tr>';
    }
    echo '</table>';
}

// Her sekme için tablo ve alanlar
switch($tab) {
case 'users':
    tableList($db, "SELECT * FROM users", ['id','name','email','role','profile_image'], 'users', ['edit'=>'edit_user.php']);
    break;
case 'courses':
    tableList($db, "SELECT c.id, c.course_name, u.name as teacher FROM courses c LEFT JOIN users u ON c.teacher_id=u.id", ['id','course_name','teacher'], 'courses', ['edit'=>'edit_course.php']);
    break;
case 'announcements':
    tableList($db, "SELECT a.id, u.name as teacher, a.title, a.content, a.created_at FROM announcements a LEFT JOIN users u ON a.teacher_id=u.id", ['id','teacher','title','content','created_at'], 'announcements', ['edit'=>'edit_announcement.php']);
    break;
case 'assignments':
    tableList($db, "SELECT a.id, c.course_name, a.title, a.description, a.due_date FROM assignments a LEFT JOIN courses c ON a.course_id=c.id", ['id','course_name','title','description','due_date'], 'assignments', ['edit'=>'edit_assignment.php']);
    break;
case 'grades':
    tableList($db, "SELECT g.id, u.name as student, c.course_name, g.grade, g.created_at FROM grades g LEFT JOIN users u ON g.student_id=u.id LEFT JOIN courses c ON g.course_id=c.id", ['id','student','course_name','grade','created_at'], 'grades', ['edit'=>'edit_grade.php']);
    break;
case 'attendances':
    tableList($db, "SELECT a.id, c.course_name, a.attendance_date, a.created_at FROM attendances a LEFT JOIN courses c ON a.course_id=c.id", ['id','course_name','attendance_date','created_at'], 'attendances', ['edit'=>'edit_attendance.php']);
    break;
case 'attendance_records':
    tableList($db, "SELECT ar.id, u.name as student, a.attendance_date, ar.status, ar.recorded_at FROM attendance_records ar LEFT JOIN attendances a ON ar.attendance_id=a.id LEFT JOIN users u ON ar.student_id=u.id", ['id','student','attendance_date','status','recorded_at'], 'attendance_records', ['edit'=>'edit_attendance_record.php']);
    break;
case 'student_courses':
    tableList($db, "SELECT sc.id, u.name as student, c.course_name FROM student_courses sc LEFT JOIN users u ON sc.student_id=u.id LEFT JOIN courses c ON sc.course_id=c.id", ['id','student','course_name'], 'student_courses', ['edit'=>'edit_student_course.php']);
    break;
case 'submissions':
    tableList($db, "SELECT s.id, u.name as student, a.title as assignment, s.file_path, s.submitted_at FROM submissions s LEFT JOIN users u ON s.student_id=u.id LEFT JOIN assignments a ON s.assignment_id=a.id", ['id','student','assignment','file_path','submitted_at'], 'submissions', ['edit'=>'edit_submission.php']);
    break;
case 'messages':
    tableList($db, "SELECT m.id, u.name as sender, m.title, m.content, m.created_at FROM messages m LEFT JOIN users u ON m.sender_id=u.id", ['id','sender','title','content','created_at'], 'messages', ['edit'=>'edit_message.php']);
    break;
default:
    echo "<h2>Hoş geldiniz!</h2><p>Yukarıdan bir sekme seçerek yönetim yapabilirsiniz.</p>";
}
?>
</div>
</body>
</html>