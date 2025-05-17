<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id']; $date = $_POST['attendance_date'];
    $stmt = $db->prepare("UPDATE attendances SET course_id=?, attendance_date=? WHERE id=?");
    $stmt->execute([$course_id, $date, $id]);
    header("Location: admin_panel.php?tab=attendances&msg=Yoklama güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM attendances WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Yoklama bulunamadı!");

$courses = $db->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Ders:
    <select name="course_id">
        <?php foreach($courses as $c): ?>
        <option value="<?=$c['id']?>" <?=$row['course_id']==$c['id']?'selected':''?>><?=$c['course_name']?></option>
        <?php endforeach; ?>
    </select><br>
    Tarih: <input type="date" name="attendance_date" value="<?=$row['attendance_date']?>"><br>
    <button type="submit">Kaydet</button>
</form>