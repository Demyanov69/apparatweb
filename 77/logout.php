<?php
session_start();
session_destroy();
header("Content-Security-Policy: default-src 'self';");
header('Location: index.php');
exit;
?>