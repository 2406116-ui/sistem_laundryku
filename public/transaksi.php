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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, 'Segoe UI', sans-serif;
        }

        body {
            background: #f1f5f9;
            display: flex;
        }

        .sidebar {
            width: 260px;
            background: #1e2a3a;
            color: #e2e8f0;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }

        .sidebar .logo {
            text-align: center;
            padding: 20px;
            font-size: 22px;
            font-weight: 600;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }

        .sidebar .logo span {
            background: #2d3e50;
            padding: 6px 14px;
            border-radius: 30px;
            color: white;
        }

        .sidebar ul {
            list-style: none;
            padding: 0 16px;
        }

        .sidebar ul li {
            margin: 8px 0;
            border-radius: 12px;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            transition: 0.2s;
            font-weight: 500;
        }

        .sidebar ul li a:hover {
            background: #2d3e50;
            color: white;
        }

        .sidebar ul li.active {
            background: #2d3e50;
        }

        .sidebar ul li.active a {
            color: white;
            font-weight: 600;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px 40px;
            width: calc(100% - 260px);
        }

        .top-bar {
            background: white;
            padding: 15px 25px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .top-bar h2 {
            color: #0f172a;
        }

        .user-info {
            background: #f1f5f9;
            padding: 6px 16px;
            border-radius: 40px;
            color: #1e293b;
        }

        .btn {
            background: #0f172a;
            border: none;
            padding: 6px 14px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            margin: 2px;
        }

        .btn-edit {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .table-wrapper {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #334155;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        @media (max-width:768px) {
            .sidebar {
                width: 80px;
            }

            .sidebar .logo span {
                display: none;
            }

            .sidebar ul li a {
                text-align: center;
                padding: 12px 0;
            }

            .main-content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo"><span>🧺 LaundryKu</span></div>
        <ul>
            <li><a href="dashboard.php">🏠 Beranda</a></li>
            <li><a href="pelanggan.php">👥 Data Pelanggan</a></li>
            <li class="active"><a href="transaksi.php">📦 Data Transaksi</a></li>
            <li><a href="pembayaran.php">💰 Pembayaran</a></li>
            <li><a href="laporan.php">📊 Laporan</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
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
                            <th>Harga/Kg (Rp)</th>
                            <th>Total (Rp)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = $result->fetch_assoc()):
                            $id_pesanan = "PS" . str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT);
                        ?>
                            <tr>
                                <td style="text-align:center;"><?= $no++; ?></td>
                                <td><?= $id_pesanan ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['jenis'] ?? 'Baju') ?></td>
                                <td><?= $row['tanggal_masuk'] ?></td>
                                <td><?= $row['berat_kg'] ?> kg</td>
                                <td>Rp <?= number_format($row['harga_perkg'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td>
                                    <a href="transaksi_update_form.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-edit">Update</a>
                                    <?php if ($row['status'] == 'proses'): ?>
                                        <a href="?ubah_status=selesai&id=<?= $row['id_transaksi'] ?>" class="btn">Selesai</a>
                                    <?php elseif ($row['status'] == 'selesai'): ?>
                                        <a href="?ubah_status=diambil&id=<?= $row['id_transaksi'] ?>" class="btn">Diambil</a>
                                    <?php else: ?>
                                        <a href="transaksi_hapus.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada transaksi.</p>
            <?php endif; ?>
        </div>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>