<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$file = isset($_GET['file']) ? $_GET['file'] : '';
if ($file) {
    $filepath = '../backup/' . $file;
    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) == 'sql') {
        unlink($filepath);
    }
}
header("Location: backup.php");
exit();
?>