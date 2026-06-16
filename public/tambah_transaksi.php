<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';
$pelanggan = $conn->query("SELECT * FROM pelanggan");
$hargaPerJenis = [
    'Baju' => 8000,
    'Selimut' => 12000,
    'Sepatu' => 15000,
    'Karpet' => 20000
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $jenis = $_POST['jenis'];
    $tgl_masuk = $_POST['tanggal_masuk'];
    $tgl_selesai = $_POST['tanggal_selesai'] ?: null;
    $berat = (float)$_POST['berat_kg'];
    $harga = $hargaPerJenis[$jenis];
    $status = $_POST['status'];
    $sql = "INSERT INTO transaksi (id_pelanggan, jenis, tanggal_masuk, tanggal_selesai, berat_kg, harga_perkg, status)
            VALUES ($id_pelanggan, '$jenis', '$tgl_masuk', '$tgl_selesai', $berat, $harga, '$status')";
    if ($conn->query($sql)) {
        header("Location: transaksi.php");
        exit();
    } else {
        $error = "Gagal simpan: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi - LaundryKu</title>
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
            <h2>Tambah Transaksi</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <form method="post">
            <select name="id_pelanggan" required>
                <option value="">Pilih Pelanggan</option>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                    <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama_pelanggan']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="jenis" required>
                <option value="Baju">👕 Baju (Rp 8.000/kg)</option>
                <option value="Selimut">🛏️ Selimut (Rp 12.000/kg)</option>
                <option value="Sepatu">👟 Sepatu (Rp 15.000/kg)</option>
                <option value="Karpet">🧶 Karpet (Rp 20.000/kg)</option>
            </select>
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" required>
            <label>Tanggal Selesai (opsional)</label>
            <input type="date" name="tanggal_selesai">
            <input type="number" step="0.01" name="berat_kg" placeholder="Berat (kg)" required>
            <select name="status">
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
                <option value="diambil">Diambil</option>
            </select>
            <button type="submit">Simpan Transaksi</button>
        </form>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>