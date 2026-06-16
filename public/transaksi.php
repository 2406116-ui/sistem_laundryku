<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

if (isset($_GET['ubah_status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $conn->query("UPDATE transaksi SET status='$status' WHERE id_transaksi=$id");
    header("Location: transaksi.php");
}
$sql = "SELECT t.*, p.nama_pelanggan FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan ORDER BY t.tanggal_masuk DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Transaksi - LaundryKu</title>
    <style>
        /* sama seperti di pelanggan.php, copy style dari atas */
    </style>
</head>

<body>
    <div class="sidebar">...</div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Data Transaksi Laundry</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <div style="margin-bottom:20px;"><a href="tambah_transaksi.php" class="btn">+ Tambah Transaksi</a></div>
        <div class="table-wrapper">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Jenis</th>
                            <th>Tgl Masuk</th>
                            <th>Berat (kg)</th>
                            <th>Harga/Kg</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody><?php $no = 1;
                            while ($row = $result->fetch_assoc()):
                                $id_transaksi_custom = "PS" . str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td style="text-align:center;"><?= $no++; ?></td>
                                <td><?= $id_transaksi_custom ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['jenis'] ?? 'Baju') ?></td>
                                <td><?= $row['tanggal_masuk'] ?></td>
                                <td><?= $row['berat_kg'] ?> kg</td>
                                <td>Rp <?= number_format($row['harga_perkg'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td>
                                    <a href="transaksi_update_form.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-update">Update</a>
                                    <?php if ($row['status'] == 'proses'): ?>
                                        <a href="?ubah_status=selesai&id=<?= $row['id_transaksi'] ?>" class="btn">Selesai</a>
                                    <?php elseif ($row['status'] == 'selesai'): ?>
                                        <a href="?ubah_status=diambil&id=<?= $row['id_transaksi'] ?>" class="btn">Diambil</a>
                                    <?php else: ?>
                                        <a href="transaksi_hapus.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr><?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?><p>Belum ada transaksi.</p><?php endif; ?>
        </div>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>