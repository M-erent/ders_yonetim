<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION["user_id"] ?? null;
if(!$user_id) exit;

$stmt = $pdo->prepare("
    INSERT IGNORE INTO announcement_reads (announcement_id, user_id, read_at)
    SELECT a.id, ?, NOW()
    FROM announcements a
    LEFT JOIN announcement_reads ar
      ON ar.announcement_id = a.id AND ar.user_id = ?
    WHERE ar.id IS NULL
");
$stmt->execute([$user_id, $user_id]);
?>