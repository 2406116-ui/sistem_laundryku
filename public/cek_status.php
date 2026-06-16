<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

$hasil = null;
$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cari = trim($_POST['cari']);
    if (is_numeric($cari)) {
        // Cari berdasarkan ID transaksi
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp 
                FROM transaksi t 
                JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                WHERE t.id_transaksi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cari);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        if (!$hasil) $pesanError = "Transaksi dengan ID $cari tidak ditemukan.";
    } else {
        // Cari berdasarkan nomor HP
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp 
                FROM transaksi t 
                JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                WHERE p.no_hp LIKE ? 
                ORDER BY t.tanggal_masuk DESC";
        $search = "%$cari%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (empty($hasil)) $pesanError = "Tidak ada transaksi dengan nomor HP '$cari'.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Laundry - LaundryKu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #fdf2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 32px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border: 1px solid #ffe0e5;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo span {
            background: #ff9eb5;
            color: white;
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: bold;
            font-size: 1.5rem;
            display: inline-block;
        }
        h2 {
            color: #d9534f;
            text-align: center;
            margin-bottom: 10px;
        }
        .sub {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        input {
            flex: 1;
            padding: 12px 18px;
            border: 1px solid #ffcdd4;
            border-radius: 40px;
            font-size: 14px;
            background: #fffafa;
        }
        button {
            background: #ff9eb5;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }
        button:hover {
            background: #ff85a1;
        }
        .error {
            background: #ffe6e6;
            color: #d9534f;
            padding: 12px;
            border-radius: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        .card {
            background: #fffafc;
            border-radius: 28px;
            padding: 20px;
            margin-top: 20px;
            border-left: 6px solid #ff9eb5;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ffe0e5;
        }
        .info-label {
            font-weight: 600;
            color: #d14c6e;
        }
        .info-value {
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 40px;
            font-weight: 600;
        }
        .status-proses { background: #ffe6ec; color: #d9534f; }
        .status-selesai { background: #d4edda; color: #28a745; }
        .status-diambil { background: #cfe2ff; color: #0d6efd; }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ffe0e5;
        }
        th {
            color: #d14c6e;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #b0a0a0;
        }
        a {
            color: #ff85a1;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <span>🧺 LaundryKu</span>
    </div>
    <h2>Cek Status Laundry Anda</h2>
    <div class="sub">Masukkan ID Transaksi atau Nomor HP</div>

    <form method="post">
        <input type="text" name="cari" placeholder="Contoh: 12345 atau 081234567890" required>
        <button type="submit">Cek Sekarang</button>
    </form>

    <?php if ($pesanError): ?>
        <div class="error"><?= htmlspecialchars($pesanError) ?></div>
    <?php endif; ?>

    <?php if ($hasil && !$pesanError): ?>
        <?php if (isset($hasil['id_transaksi']) && !is_array($hasil)): ?>
            <!-- Hasil satu transaksi (berdasarkan ID) -->
            <div class="card">
                <div class="info-row">
                    <span class="info-label">ID Transaksi</span>
                    <span class="info-value"><?= $hasil['id_transaksi'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Pelanggan</span>
                    <span class="info-value"><?= htmlspecialchars($hasil['nama_pelanggan']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis Laundry</span>
                    <span class="info-value"><?= ucfirst($hasil['jenis'] ?? 'Baju') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Masuk</span>
                    <span class="info-value"><?= $hasil['tanggal_masuk'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Berat</span>
                    <span class="info-value"><?= $hasil['berat_kg'] ?> kg</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Harga</span>
                    <span class="info-value">Rp <?= number_format($hasil['total_harga'], 0, ',', '.') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= $hasil['status'] ?>"><?= ucfirst($hasil['status']) ?></span>
                    </span>
                </div>
            </div>
        <?php elseif (is_array($hasil) && count($hasil) > 0): ?>
            <!-- Beberapa transaksi (berdasarkan nomor HP) -->
            <div class="table-wrapper">
                <h3 style="margin-bottom: 10px;">📋 Daftar Transaksi untuk <?= htmlspecialchars($hasil[0]['nama_pelanggan']) ?></h3>
                <table>
                    <thead>
                        <tr><th>ID Transaksi</th><th>Tanggal Masuk</th><th>Berat</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil as $row): ?>
                        <tr>
                            <td><?= $row['id_transaksi'] ?></td>
                            <td><?= $row['tanggal_masuk'] ?></td>
                            <td><?= $row['berat_kg'] ?> kg</td>
                            <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="footer">
        <a href="login.php">Admin Login</a> | &copy; 2025 LaundryKu
    </div>
</div>
</body>
</html>