<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$bulanList = $conn->query("SELECT DISTINCT DATE_FORMAT(tanggal_masuk, '%Y-%m') as bulan FROM transaksi ORDER BY bulan DESC");
$selectedBulan = isset($_GET['bulan']) ? $_GET['bulan'] : null;
$filter = $selectedBulan ? "WHERE DATE_FORMAT(t.tanggal_masuk, '%Y-%m') = '$selectedBulan'" : "";

$sql = "SELECT DATE_FORMAT(t.tanggal_masuk, '%Y-%m') as bulan, t.jenis as jenis_nama,
        COUNT(t.id_transaksi) as jumlah_transaksi, SUM(t.total_harga) as total_pendapatan, SUM(t.berat_kg) as total_berat
        FROM transaksi t $filter GROUP BY bulan, t.jenis ORDER BY bulan DESC, t.jenis ASC";
$result = $conn->query($sql);
$dataPerBulan = [];
while ($row = $result->fetch_assoc()) {
    $bulan = $row['bulan'];
    if (!isset($dataPerBulan[$bulan])) {
        $dataPerBulan[$bulan] = ['total_transaksi' => 0, 'total_pendapatan' => 0, 'total_berat' => 0, 'jenis' => []];
    }
    $dataPerBulan[$bulan]['total_transaksi'] += $row['jumlah_transaksi'];
    $dataPerBulan[$bulan]['total_pendapatan'] += $row['total_pendapatan'];
    $dataPerBulan[$bulan]['total_berat'] += $row['total_berat'];
    $dataPerBulan[$bulan]['jenis'][] = [
        'nama' => $row['jenis_nama'],
        'jumlah' => $row['jumlah_transaksi'],
        'pendapatan' => $row['total_pendapatan'],
        'berat' => $row['total_berat']
    ];
}

// Grafik pendapatan per bulan
$chartBulan = $conn->query("SELECT DATE_FORMAT(tanggal_masuk, '%Y-%m') as bulan, SUM(total_harga) as pendapatan FROM transaksi GROUP BY bulan ORDER BY bulan ASC");
$bulanLabels = [];
$bulanValues = [];
while ($row = $chartBulan->fetch_assoc()) {
    $bulanLabels[] = date('M Y', strtotime($row['bulan'] . '-01'));
    $bulanValues[] = $row['pendapatan'];
}

// Grafik pie per jenis
$jenisPie = $conn->query("SELECT jenis, SUM(total_harga) as total FROM transaksi GROUP BY jenis");
$jenisLabels = [];
$jenisValues = [];
while ($row = $jenisPie->fetch_assoc()) {
    $jenisLabels[] = $row['jenis'] ?: 'Tidak diketahui';
    $jenisValues[] = $row['total'];
}

// Detail transaksi
$detailSql = "SELECT t.id_transaksi, p.nama_pelanggan, t.tanggal_masuk, t.total_harga, t.status,
                     COALESCE(SUM(py.jumlah_bayar),0) as total_dibayar,
                     (t.total_harga - COALESCE(SUM(py.jumlah_bayar),0)) as sisa
              FROM transaksi t
              JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
              LEFT JOIN pembayaran py ON t.id_transaksi = py.id_transaksi
              GROUP BY t.id_transaksi
              ORDER BY t.tanggal_masuk DESC";
$detailResult = $conn->query($detailSql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - LaundryKu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 25px 30px;
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

        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-box select,
        .filter-box button {
            padding: 10px 20px;
            border-radius: 40px;
            border: 1px solid #cbd5e1;
            background: white;
        }

        .filter-box button {
            background: #0f172a;
            color: white;
            cursor: pointer;
        }

        .filter-box button:hover {
            background: #1e293b;
        }

        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
            justify-content: center;
        }

        .chart-box {
            background: white;
            border-radius: 20px;
            padding: 20px;
            flex: 1;
            min-width: 280px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .chart-box h3 {
            margin-bottom: 15px;
            color: #0f172a;
        }

        canvas {
            max-height: 300px;
            margin: auto;
            width: 100% !important;
        }

        .card-bulan {
            background: white;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            background: #f8fafc;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 1.2rem;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-body {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
        }

        .total-row {
            font-weight: bold;
            background: #f8fafc;
        }

        .detail-table-wrapper {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
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
            <li><a href="pembayaran.php">💰 Pembayaran</a></li>
            <li class="active"><a href="laporan.php">📊 Laporan</a></li>
            <li><a href="backup.php">💾 Backup & Restore</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Laporan Keuangan</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <div class="filter-box">
            <form method="get" style="display:flex; gap:15px; flex-wrap:wrap;">
                <select name="bulan">
                    <option value="">Semua Bulan</option>
                    <?php while ($b = $bulanList->fetch_assoc()): ?>
                        <option value="<?= $b['bulan'] ?>" <?= ($selectedBulan == $b['bulan']) ? 'selected' : '' ?>><?= date('F Y', strtotime($b['bulan'] . '-01')) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Filter</button>
                <?php if ($selectedBulan): ?>
                    <a href="laporan.php" style="background:#e2e8f0; padding:10px 20px; border-radius:40px; text-decoration:none; color:#1e293b;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="chart-container">
            <div class="chart-box">
                <h3>📈 Pendapatan per Bulan</h3><canvas id="revenueChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>🥧 Pendapatan per Jenis Laundry</h3><canvas id="jenisPieChart"></canvas>
            </div>
        </div>

        <?php if (empty($dataPerBulan)): ?>
            <p>Belum ada data transaksi.</p>
            <?php else: foreach ($dataPerBulan as $bulan => $data): ?>
                <div class="card-bulan">
                    <div class="card-header">📅 <?= date('F Y', strtotime($bulan . '-01')) ?></div>
                    <div class="card-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Jenis Laundry</th>
                                    <th>Jumlah Transaksi</th>
                                    <th>Total Berat (kg)</th>
                                    <th>Total Pendapatan (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['jenis'] as $jenis): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($jenis['nama'] ?: 'Tidak diketahui') ?></td>
                                        <td><?= $jenis['jumlah'] ?></td>
                                        <td><?= number_format($jenis['berat'], 1, ',', '.') ?> kg</td>
                                        <td>Rp <?= number_format($jenis['pendapatan'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="total-row">
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong><?= $data['total_transaksi'] ?></strong></td>
                                    <td><strong><?= number_format($data['total_berat'], 1, ',', '.') ?> kg</strong></td>
                                    <td><strong>Rp <?= number_format($data['total_pendapatan'], 0, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
        <?php endforeach;
        endif; ?>

        <div class="detail-table-wrapper">
            <h3 style="margin-bottom:15px;">📋 Detail Transaksi</h3>
            <?php if ($detailResult && $detailResult->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tgl Masuk</th>
                            <th>Total Harga (Rp)</th>
                            <th>Dibayar (Rp)</th>
                            <th>Sisa (Rp)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = $detailResult->fetch_assoc()): $id_pesanan = "PS" . str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT); ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $id_pesanan ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td><?= $row['tanggal_masuk'] ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['total_dibayar'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['sisa'], 0, ',', '.') ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
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

    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabels) ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($bulanValues) ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Rp ' + ctx.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('jenisPieChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($jenisLabels) ?>,
                datasets: [{
                    data: <?= json_encode($jenisValues) ?>,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec489a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': Rp ' + ctx.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>