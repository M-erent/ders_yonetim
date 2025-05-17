<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_POST['sender_id']; $title = $_POST['title']; $content = $_POST['content'];
    $stmt = $db->prepare("UPDATE messages SET sender_id=?, title=?, content=? WHERE id=?");
    $stmt->execute([$sender_id, $title, $content, $id]);
    header("Location: admin_panel.php?tab=messages&msg=Mesaj güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM messages WHERE id=?"); $stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) die("Mesaj bulunamadı!");

$users = $db->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post">
    Gönderen:
    <select name="sender_id">
        <?php foreach($users as $u): ?>
        <option value="<?=$u['id']?>" <?=$row['sender_id']==$u['id']?'selected':''?>><?=$u['name']?></option>
        <?php endforeach; ?>
    </select><br>
    Başlık: <input type="text" name="title" value="<?=htmlspecialchars($row['title'])?>"><br>
    Mesaj: <textarea name="content"><?=htmlspecialchars($row['content'])?></textarea><br>
    <button type="submit">Kaydet</button>
</form>