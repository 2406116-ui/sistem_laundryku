<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $hp = $_POST['no_hp'];
    $conn->query("INSERT INTO pelanggan (nama_pelanggan, alamat, no_hp) VALUES ('$nama', '$alamat', '$hp')");
    header("Location: pelanggan.php");
}
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $cek = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE id_pelanggan = $id")->fetch_assoc()['total'];
    if ($cek > 0) {
        $error = "Pelanggan tidak bisa dihapus karena masih memiliki transaksi!";
    } else {
        $conn->query("DELETE FROM pelanggan WHERE id_pelanggan = $id");
        header("Location: pelanggan.php");
    }
}
$data = $conn->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan - LaundryKu</title>
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

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: white;
        }

        button,
        .btn {
            background: #0f172a;
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }

        .btn-update {
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
            <li class="active"><a href="pelanggan.php">👥 Data Pelanggan</a></li>
            <li><a href="transaksi.php">📦 Data Transaksi</a></li>
            <li><a href="pembayaran.php">💰 Pembayaran</a></li>
            <li><a href="laporan.php">📊 Laporan</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Data Pelanggan</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:14px; margin-bottom:20px;"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <h3 style="margin-bottom:15px;">Tambah Pelanggan</h3><input type="text" name="nama" placeholder="Nama Pelanggan" required><input type="text" name="alamat" placeholder="Alamat"><input type="text" name="no_hp" placeholder="No HP"><button type="submit" name="tambah">Tambah</button>
        </form>
        <div class="table-wrapper">
            <h3>📋 Daftar Pelanggan</h3>
            <?php if ($data && $data->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pelanggan</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody><?php $no = 1;
                            while ($row = $data->fetch_assoc()):
                                $id_pelanggan = "PL" . str_pad($row['id_pelanggan'], 3, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $id_pelanggan ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                <td>
                                    <a href="pelanggan_update_form.php?id=<?= $row['id_pelanggan'] ?>" class="btn btn-update">Update</a>
                                    <a href="?hapus=<?= $row['id_pelanggan'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?><p>Belum ada data.</p><?php endif; ?>
        </div>
        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>