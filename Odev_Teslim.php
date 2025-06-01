<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id']; $student_id = $_POST['student_id']; $file_path = $_POST['file_path'];
    $stmt = $db->prepare("UPDATE submissions SET assignment_id=?, student_id=?, file_path=? WHERE id=?");
    $stmt->execute([$assignment_id, $student_id, $file_path, $id]);
    header("Location: Yonetici_Paneli.php?tab=submissions&msg=Teslim güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM submissions WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Teslim bulunamadı!");

$assignments = $db->query("SELECT id, title FROM assignments")->fetchAll(PDO::FETCH_ASSOC);
$students = $db->query("SELECT id, name FROM users WHERE role='Öğrenci'")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Ödev:
    <select name="assignment_id">
        <?php foreach($assignments as $a): ?>
        <option value="<?=$a['id']?>" <?=$row['assignment_id']==$a['id']?'selected':''?>><?=$a['title']?></option>
        <?php endforeach; ?>
    </select><br>
    Öğrenci:
    <select name="student_id">
        <?php foreach($students as $s): ?>
        <option value="<?=$s['id']?>" <?=$row['student_id']==$s['id']?'selected':''?>><?=$s['name']?></option>
        <?php endforeach; ?>
    </select><br>
    Dosya Yolu: <input type="text" name="file_path" value="<?=htmlspecialchars($row['file_path'])?>"><br>
    <button type="submit">Kaydet</button>
</form>