<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $teacher_id = $_POST['teacher_id'];
    $stmt = $db->prepare("UPDATE courses SET course_name=?, teacher_id=? WHERE id=?");
    $stmt->execute([$course_name, $teacher_id, $id]);
    header("Location: admin_panel.php?tab=courses&msg=Ders güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM courses WHERE id=?"); $stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC); if (!$course) die("Ders bulunamadı!");

// Öğretmenleri çek
$teachers = $db->query("SELECT id, name FROM users WHERE role='Öğretmen'")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Ders Adı: <input type="text" name="course_name" value="<?=htmlspecialchars($course['course_name'])?>"><br>
    Öğretmen:
    <select name="teacher_id">
        <?php foreach($teachers as $t): ?>
        <option value="<?=$t['id']?>" <?=$course['teacher_id']==$t['id']?'selected':''?>><?=$t['name']?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Kaydet</button>
</form>