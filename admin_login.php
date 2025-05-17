<?php
session_start();
require_once 'db/db_connect.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($admin = $res->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: admin_panel.php');
            exit();
        } else {
            $error = 'Şifre yanlış!';
        }
    } else {
        $error = 'Kullanıcı bulunamadı!';
    }
}
?>
<form method="post">
    Kullanıcı Adı: <input type="text" name="username" required><br>
    Şifre: <input type="password" name="password" required><br>
    <button type="submit">Giriş</button>
    <?= $error ?>
</form>