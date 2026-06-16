<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit(); }
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $conn->query("UPDATE pelanggan SET nama_pelanggan='$nama', alamat='$alamat', no_hp='$no_hp' WHERE id_pelanggan=$id");
    header("Location: pelanggan.php");
    exit();
} else {
    header("Location: pelanggan.php");
}
?>