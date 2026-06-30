<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $id_trans = $_POST['id_transaksi'];
    $tgl = $_POST['tanggal_bayar'];
    $jumlah = $_POST['jumlah_bayar'];
    $metode = $_POST['metode'];
    $conn->query("INSERT INTO pembayaran (id_transaksi, tanggal_bayar, jumlah_bayar, metode) VALUES ('$id_trans', '$tgl', '$jumlah', '$metode')");
    header("Location: pembayaran.php");
}

$transaksiList = $conn->query("SELECT t.id_transaksi, pel.nama_pelanggan, t.total_harga, 
                                      COALESCE(SUM(p.jumlah_bayar),0) as sudah_dibayar
                               FROM transaksi t 
                               JOIN pelanggan pel ON t.id_pelanggan = pel.id_pelanggan 
                               LEFT JOIN pembayaran p ON t.id_transaksi = p.id_transaksi
                               GROUP BY t.id_transaksi
                               HAVING sudah_dibayar < t.total_harga
                               ORDER BY t.tanggal_masuk DESC");

$sql = "SELECT p.*, t.total_harga, pel.nama_pelanggan 
        FROM pembayaran p 
        JOIN transaksi t ON p.id_transaksi = t.id_transaksi 
        JOIN pelanggan pel ON t.id_pelanggan = pel.id_pelanggan 
        ORDER BY p.tanggal_bayar DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pembayaran - LaundryKu</title>
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

        form {
            background: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            max-width: 500px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: white;
        }

        button {
            background: #0f172a;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
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
            <li><a href="transaksi.php">📦 Data Transaksi</a></li>
            <li class="active"><a href="pembayaran.php">💰 Pembayaran</a></li>
            <li><a href="laporan.php">📊 Laporan</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Pembayaran Laundry</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <form method="post">
            <select name="id_transaksi" required>
                <option value="">Pilih Transaksi (Belum Lunas)</option>
                <?php while ($t = $transaksiList->fetch_assoc()): $sisa = $t['total_harga'] - $t['sudah_dibayar']; ?>
                    <option value="<?= $t['id_transaksi'] ?>"><?= htmlspecialchars($t['nama_pelanggan']) ?> - Total: Rp <?= number_format($t['total_harga'], 2, ',', '.') ?> (Sisa: Rp <?= number_format($sisa, 2, ',', '.') ?>)</option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="tanggal_bayar" required>
            <input type="number" step="0.01" name="jumlah_bayar" placeholder="Jumlah Bayar (Rp)" required>
            <select name="metode">
                <option value="Tunai">Tunai</option>
                <option value="Transfer">Transfer</option>
                <option value="E-Wallet">E-Wallet</option>
            </select>
            <button type="submit" name="tambah">Simpan Pembayaran</button>
        </form>
        <div class="table-wrapper">
            <h3>📋 Riwayat Pembayaran</h3>
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal Bayar</th>
                            <th>Jumlah (Rp)</th>
                            <th>Metode</th>
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
                                <td><?= $row['tanggal_bayar'] ?></td>
                                <td>Rp <?= number_format($row['jumlah_bayar'], 2, ',', '.') ?></td>
                                <td><?= $row['metode'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada pembayaran.</p>
            <?php endif; ?>
        </div>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>