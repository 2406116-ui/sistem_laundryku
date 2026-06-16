<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$id = (int)$_GET['id'];
$res = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = $id");
$trans = $res->fetch_assoc();
if (!$trans) {
    header("Location: transaksi.php");
    exit();
}

$pelanggan = $conn->query("SELECT * FROM pelanggan");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Update Transaksi - LaundryKu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
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
        }

        .sidebar ul li a:hover {
            background: #2d3e50;
            color: white;
        }

        .sidebar ul li.active {
            background: #2d3e50;
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
            max-width: 600px;
            border: 1px solid #e2e8f0;
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

        label {
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: 600;
            color: #334155;
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

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
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
            <h2>Update Transaksi</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <form method="post" action="transaksi_update_proses.php">
            <input type="hidden" name="id" value="<?= $trans['id_transaksi'] ?>">
            <select name="id_pelanggan" required>
                <option value="">Pilih Pelanggan</option>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                    <option value="<?= $p['id_pelanggan'] ?>" <?= ($p['id_pelanggan'] == $trans['id_pelanggan']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_pelanggan']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="jenis" required>
                <option value="Baju" <?= ($trans['jenis'] == 'Baju') ? 'selected' : '' ?>>Baju (Rp 8.000/kg)</option>
                <option value="Selimut" <?= ($trans['jenis'] == 'Selimut') ? 'selected' : '' ?>>Selimut (Rp 12.000/kg)</option>
                <option value="Sepatu" <?= ($trans['jenis'] == 'Sepatu') ? 'selected' : '' ?>>Sepatu (Rp 15.000/kg)</option>
                <option value="Karpet" <?= ($trans['jenis'] == 'Karpet') ? 'selected' : '' ?>>Karpet (Rp 20.000/kg)</option>
            </select>
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" value="<?= $trans['tanggal_masuk'] ?>" required>
            <label>Tanggal Selesai (opsional)</label>
            <input type="date" name="tanggal_selesai" value="<?= $trans['tanggal_selesai'] ?>">
            <input type="number" step="0.01" name="berat_kg" value="<?= $trans['berat_kg'] ?>" placeholder="Berat (kg)" required>
            <select name="status">
                <option value="proses" <?= ($trans['status'] == 'proses') ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= ($trans['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                <option value="diambil" <?= ($trans['status'] == 'diambil') ? 'selected' : '' ?>>Diambil</option>
            </select>
            <button type="submit">Update Transaksi</button>
            <a href="transaksi.php" style="margin-left:10px; color:#64748b;">Batal</a>
        </form>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>