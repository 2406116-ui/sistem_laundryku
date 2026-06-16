<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit(); }
require_once '../config/database.php';
$id = (int)$_GET['id'];
$conn->query("DELETE FROM transaksi WHERE id_transaksi = $id");
header("Location: transaksi.php");
exit();