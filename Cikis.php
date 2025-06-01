<?php
session_start();
session_destroy();
header("Location: Giris.php");
exit();
?>
