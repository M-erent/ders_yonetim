<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION["user_id"] ?? null;

if(!$user_id) {
    echo '<div style="padding:10px;">Giriş yapmalısınız.</div>';
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.content, a.created_at,
        IF(ar.id IS NULL, 0, 1) as is_read
    FROM announcements a
    LEFT JOIN announcement_reads ar
      ON ar.announcement_id = a.id AND ar.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$announcements = $stmt->fetchAll();

if(!$announcements){
    echo '<div style="padding:10px;">Duyuru yok.</div>';
    exit;
}

foreach($announcements as $a){
    echo '<div style="padding:10px; border-bottom:1px solid #eee;'.($a['is_read'] ? 'background:#f4f5f7;' : 'background:#e5f7e5;').'">';
    echo "<b>".htmlspecialchars($a['title'])."</b><br>";
    echo "<small>".date('d.m.Y H:i', strtotime($a['created_at']))."</small><br>";
    echo "<span>".htmlspecialchars($a['content'])."</span>";
    echo '</div>';
}
?>