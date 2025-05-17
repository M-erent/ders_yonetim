<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_id = $_POST['attendance_id']; $student_id = $_POST['student_id']; $status = $_POST['status'];
    $stmt = $db->prepare("UPDATE attendance_records SET attendance_id=?, student_id=?, status=? WHERE id=?");
    $stmt->execute([$attendance_id, $student_id, $status, $id]);
    header("Location: admin_panel.php?tab=attendance_records&msg=Kayıt güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM attendance_records WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Yoklama kaydı bulunamadı!");

$attendances = $db->query("SELECT id, attendance_date FROM attendances")->fetchAll(PDO::FETCH_ASSOC);
$students = $db->query("SELECT id, name FROM users WHERE role='Öğrenci'")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Yoklama Tarihi:
    <select name="attendance_id">
        <?php foreach($attendances as $a): ?>
        <option value="<?=$a['id']?>" <?=$row['attendance_id']==$a['id']?'selected':''?>><?=$a['attendance_date']?></option>
        <?php endforeach; ?>
    </select><br>
    Öğrenci:
    <select name="student_id">
        <?php foreach($students as $s): ?>
        <option value="<?=$s['id']?>" <?=$row['student_id']==$s['id']?'selected':''?>><?=$s['name']?></option>
        <?php endforeach; ?>
    </select><br>
    Durum:
    <select name="status">
        <option value="present" <?=$row['status']=='present'?'selected':''?>>Var</option>
        <option value="absent" <?=$row['status']=='absent'?'selected':''?>>Yok</option>
    </select><br>
    <button type="submit">Kaydet</button>
</form>