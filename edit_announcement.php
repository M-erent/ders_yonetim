<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title']; $content = $_POST['content']; $teacher_id = $_POST['teacher_id'];
    $stmt = $db->prepare("UPDATE announcements SET title=?, content=?, teacher_id=? WHERE id=?");
    $stmt->execute([$title, $content, $teacher_id, $id]);
    header("Location: admin_panel.php?tab=announcements&msg=Duyuru güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM announcements WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Duyuru bulunamadı!");

$teachers = $db->query("SELECT id, name FROM users WHERE role='Öğretmen'")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Başlık: <input type="text" name="title" value="<?=htmlspecialchars($row['title'])?>"><br>
    İçerik: <textarea name="content"><?=htmlspecialchars($row['content'])?></textarea><br>
    Öğretmen:
    <select name="teacher_id">
        <?php foreach($teachers as $t): ?>
        <option value="<?=$t['id']?>" <?=$row['teacher_id']==$t['id']?'selected':''?>><?=$t['name']?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Kaydet</button>
</form>