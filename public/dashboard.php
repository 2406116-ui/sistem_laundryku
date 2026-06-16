<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$totalPelanggan = $conn->query("SELECT COUNT(*) as total FROM pelanggan")->fetch_assoc()['total'] ?? 0;
$totalTransaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;
$totalPendapatan = $conn->query("SELECT SUM(total_harga) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;
$transaksiProses = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE status='proses'")->fetch_assoc()['total'] ?? 0;

$kritis = $conn->query("SELECT t.id_transaksi, p.nama_pelanggan, t.tanggal_masuk, t.berat_kg, t.jenis
                        FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                        WHERE t.status = 'proses' AND t.tanggal_masuk <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
                        ORDER BY t.tanggal_masuk ASC LIMIT 5");
$jumlahKritis = $kritis->num_rows;

$aktivitas = $conn->query("SELECT t.id_transaksi, p.nama_pelanggan, t.tanggal_masuk, t.status
                           FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                           ORDER BY t.tanggal_masuk DESC LIMIT 5");

$topBerat = $conn->query("SELECT p.nama_pelanggan, t.berat_kg, t.jenis 
                          FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                          WHERE t.status != 'diambil' ORDER BY t.berat_kg DESC LIMIT 1");
$topItem = $topBerat->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - LaundryKu</title>
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
        /* Sidebar biru gelap */
        .sidebar {
            width: 260px;
            background: #1e2a3a;
            color: #e2e8f0;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.06);
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
            align-items: center;
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
            font-size: 14px;
        }
        .alert {
            background: #fff3e0;
            border-left: 5px solid #f59e0b;
            padding: 14px 20px;
            border-radius: 16px;
            margin-bottom: 28px;
            font-size: 14px;
            color: #92400e;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .stat-card p {
            color: #475569;
            font-size: 0.9rem;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .panel {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }
        .panel h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
            border-left: 4px solid #3b82f6;
            padding-left: 12px;
        }
        .panel ul {
            list-style: none;
        }
        .panel li {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-warning {
            background: #fef3c7;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        .item-detail {
            background: #f8fafc;
            padding: 16px;
            border-radius: 16px;
            margin-top: 10px;
            border: 1px solid #e2e8f0;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
        }
        .item-desc {
            font-size: 13px;
            color: #475569;
            margin-top: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .two-columns { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo span { display: none; }
            .sidebar ul li a { text-align: center; padding: 12px 0; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><span>🧺 LaundryKu</span></div>
    <ul>
        <li class="active"><a href="dashboard.php">🏠 Beranda</a></li>
        <li><a href="pelanggan.php">👥 Data Pelanggan</a></li>
        <li><a href="transaksi.php">📦 Data Transaksi</a></li>
        <li><a href="pembayaran.php">💰 Pembayaran</a></li>
        <li><a href="laporan.php">📊 Laporan</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>
<div class="main-content">
    <div class="top-bar">
        <h2>Dashboard</h2>
        <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
    </div>

    <?php if ($jumlahKritis > 0): ?>
        <div class="alert">
            ⚠️ Terdapat <?= $jumlahKritis ?> transaksi yang sudah lebih dari 3 hari dalam status PROSES.
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card"><h3><?= $totalPelanggan ?></h3><p>Total Pelanggan</p></div>
        <div class="stat-card"><h3><?= $totalTransaksi ?></h3><p>Total Transaksi</p></div>
        <div class="stat-card"><h3>Rp <?= number_format($totalPendapatan,0,',','.') ?></h3><p>Total Pendapatan</p></div>
        <div class="stat-card"><h3><?= $transaksiProses ?></h3><p>Transaksi Proses</p></div>
    </div>

    <div class="two-columns">
        <div class="panel">
            <h3>⚠️ Transaksi Perlu Diproses</h3>
            <?php if ($kritis && $kritis->num_rows > 0): ?>
                <ul>
                    <?php while($row = $kritis->fetch_assoc()): ?>
                    <li>
                        <span><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong><br><small><?= $row['jenis'] ?> - <?= $row['berat_kg'] ?> kg</small></span>
                        <span class="badge-warning">>3 hari</span>
                    </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Tidak ada transaksi yang tertunda lama.</p>
            <?php endif; ?>
        </div>
        <div class="panel">
            <h3>🕒 Aktivitas Terbaru</h3>
            <?php if ($aktivitas && $aktivitas->num_rows > 0): ?>
                <ul>
                    <?php while($row = $aktivitas->fetch_assoc()): ?>
                    <li>
                        <span><?= htmlspecialchars($row['nama_pelanggan']) ?> - <?= ucfirst($row['status']) ?></span>
                        <span><?= date('d M Y', strtotime($row['tanggal_masuk'])) ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada aktivitas transaksi.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <h3>📦 Transaksi Berat Tertinggi (Belum Diambil)</h3>
        <?php if ($topItem): ?>
        <div class="item-detail">
            <div class="item-name"><?= htmlspecialchars($topItem['nama_pelanggan']) ?></div>
            <div class="item-desc">Jenis: <?= $topItem['jenis'] ?> — Berat: <?= $topItem['berat_kg'] ?> kg</div>
        </div>
        <?php else: ?>
        <div class="item-detail">Tidak ada data.</div>
        <?php endif; ?>
    </div>

    <div class="footer">Administrator | LaundryKu v2.0</div>
</div>
</body>
</html>