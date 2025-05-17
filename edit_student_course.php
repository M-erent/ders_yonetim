<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id']; $course_id = $_POST['course_id'];
    $stmt = $db->prepare("UPDATE student_courses SET student_id=?, course_id=? WHERE id=?");
    $stmt->execute([$student_id, $course_id, $id]);
    header("Location: admin_panel.php?tab=student_courses&msg=Kayıt güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM student_courses WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Kayıt bulunamadı!");

$students = $db->query("SELECT id, name FROM users WHERE role='Öğrenci'")->fetchAll(PDO::FETCH_ASSOC);
$courses = $db->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Öğrenci:
    <select name="student_id">
        <?php foreach($students as $s): ?>
        <option value="<?=$s['id']?>" <?=$row['student_id']==$s['id']?'selected':''?>><?=$s['name']?></option>
        <?php endforeach; ?>
    </select><br>
    Ders:
    <select name="course_id">
        <?php foreach($courses as $c): ?>
        <option value="<?=$c['id']?>" <?=$row['course_id']==$c['id']?'selected':''?>><?=$c['course_name']?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Kaydet</button>
</form>