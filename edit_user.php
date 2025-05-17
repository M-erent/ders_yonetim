<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }
if (!isset($_GET['id'])) die("ID yok!");
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name']; $email = $_POST['email']; $role = $_POST['role'];
    $stmt = $db->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->execute([$name, $email, $role, $id]);
    header("Location: admin_panel.php?tab=users&msg=Kullanıcı güncellendi");
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC); if (!$user) die("Kullanıcı bulunamadı!");
?>
<form method="post">
    İsim: <input type="text" name="name" value="<?=htmlspecialchars($user['name'])?>"><br>
    Email: <input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>"><br>
    Rol:
    <select name="role">
        <option value="Öğrenci" <?=$user['role']=='Öğrenci'?'selected':''?>>Öğrenci</option>
        <option value="Öğretmen" <?=$user['role']=='Öğretmen'?'selected':''?>>Öğretmen</option>
    </select><br>
    <button type="submit">Kaydet</button>
</form>