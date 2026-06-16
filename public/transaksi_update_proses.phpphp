<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit(); }
require_once '../config/database.php';

$hargaJenis = ['Baju'=>8000, 'Selimut'=>12000, 'Sepatu'=>15000, 'Karpet'=>20000];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $jenis = $_POST['jenis'];
    $tgl_masuk = $_POST['tanggal_masuk'];
    $tgl_selesai = $_POST['tanggal_selesai'] ?: null;
    $berat = (float)$_POST['berat_kg'];
    $harga = $hargaJenis[$jenis];
    $status = $_POST['status'];

    $conn->query("UPDATE transaksi SET 
                  id_pelanggan=$id_pelanggan, jenis='$jenis', 
                  tanggal_masuk='$tgl_masuk', tanggal_selesai='$tgl_selesai', 
                  berat_kg=$berat, harga_perkg=$harga, status='$status' 
                  WHERE id_transaksi=$id");
    header("Location: transaksi.php");
    exit();
}
?>