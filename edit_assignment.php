<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id']; $title = $_POST['title']; $desc = $_POST['description']; $due = $_POST['due_date'];
    $stmt = $db->prepare("UPDATE assignments SET course_id=?, title=?, description=?, due_date=? WHERE id=?");
    $stmt->execute([$course_id, $title, $desc, $due, $id]);
    header("Location: admin_panel.php?tab=assignments&msg=Ödev güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM assignments WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Ödev bulunamadı!");

$courses = $db->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Ders:
    <select name="course_id">
        <?php foreach($courses as $c): ?>
        <option value="<?=$c['id']?>" <?=$row['course_id']==$c['id']?'selected':''?>><?=$c['course_name']?></option>
        <?php endforeach; ?>
    </select><br>
    Başlık: <input type="text" name="title" value="<?=htmlspecialchars($row['title'])?>"><br>
    Açıklama: <textarea name="description"><?=htmlspecialchars($row['description'])?></textarea><br>
    Teslim Tarihi: <input type="date" name="due_date" value="<?=$row['due_date']?>"><br>
    <button type="submit">Kaydet</button>
</form>