<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit('Yasak!');
require_once 'db/db_connect.php';

$app_id = (int)$_POST['app_id'];
$action = $_POST['action'];

if ($action == 'approve') {
    $stmt = $conn->prepare("SELECT name,email,password FROM teacher_applications WHERE id=?");
    $stmt->bind_param('i', $app_id); $stmt->execute(); $app = $stmt->get_result()->fetch_assoc();
    if ($app) {
        $name=$app['name']; $email=$app['email']; $pw=$app['password'];
        $role="Öğretmen";
        $stmt2 = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
        $stmt2->bind_param('ssss', $name, $email, $pw, $role); $stmt2->execute();
        $conn->query("UPDATE teacher_applications SET status='Onaylandı' WHERE id=$app_id");
    }
} elseif ($action == 'reject') {
    $conn->query("UPDATE teacher_applications SET status='Reddedildi' WHERE id=$app_id");
}
header("Location: admin_panel.php");
exit();
?>