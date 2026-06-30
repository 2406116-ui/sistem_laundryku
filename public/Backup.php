<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$message = '';
$messageType = '';

// Proses Backup
if (isset($_POST['backup'])) {
    $filename = 'backup_laundryku_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = '../backup/' . $filename;

    if (!file_exists('../backup')) {
        mkdir('../backup', 0777, true);
    }

    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    $sqlContent = "-- Backup Database LaundryKu\n";
    $sqlContent .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
    $sqlContent .= "-- Database: " . $conn->database . "\n\n";
    $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $createResult = $conn->query("SHOW CREATE TABLE $table");
        $createRow = $createResult->fetch_assoc();
        $sqlContent .= "DROP TABLE IF EXISTS `$table`;\n";
        $sqlContent .= $createRow['Create Table'] . ";\n\n";

        $dataResult = $conn->query("SELECT * FROM $table");
        if ($dataResult->num_rows > 0) {
            $sqlContent .= "INSERT INTO `$table` VALUES\n";
            $rows = [];
            while ($row = $dataResult->fetch_assoc()) {
                $values = [];
                foreach ($row as $key => $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                $rows[] = "(" . implode(', ', $values) . ")";
            }
            $sqlContent .= implode(",\n", $rows) . ";\n\n";
        }
    }

    $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

    if (file_put_contents($filepath, $sqlContent)) {
        $message = "✅ Backup berhasil! File: " . $filename;
        $messageType = 'success';
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        $message = "❌ Gagal menyimpan file backup!";
        $messageType = 'danger';
    }
}

// Daftar file backup
$backupFiles = [];
if (file_exists('../backup')) {
    $files = scandir('../backup');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backupFiles[] = [
                'name' => $file,
                'size' => filesize('../backup/' . $file),
                'date' => date('d M Y H:i:s', filemtime('../backup/' . $file))
            ];
        }
    }
    rsort($backupFiles);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Backup Database - LaundryKu</title>
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

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .card h3 {
            margin-bottom: 15px;
            color: #0f172a;
        }

        .btn {
            background: #0f172a;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #1e293b;
        }

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .alert {
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
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
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .flex {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
            <li><a href="laporan.php">📊 Laporan</a></li>
            <li class="active"><a href="backup.php">💾 Backup</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2>💾 Backup & Restore Database</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <!-- Backup -->
        <div class="card">
            <h3>📤 Backup Database</h3>
            <p style="color:#475569; margin-bottom:15px;">Membuat cadangan seluruh database dalam format SQL.</p>
            <form method="post">
                <button type="submit" name="backup" class="btn btn-success">🔽 Backup Sekarang</button>
            </form>
        </div>

        <!-- Restore -->
        <div class="card">
            <h3>📥 Restore Database</h3>
            <p style="color:#475569; margin-bottom:15px;">Upload file SQL backup untuk mengembalikan database.</p>
            <form method="post" enctype="multipart/form-data" action="restore.php">
                <div style="display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
                    <input type="file" name="sql_file" accept=".sql" required style="padding:10px; border:1px solid #cbd5e1; border-radius:14px;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('⚠️ Restore akan menghapus data saat ini! Lanjutkan?')">📥 Restore</button>
                </div>
            </form>
        </div>

        <!-- Daftar Backup -->
        <div class="card">
            <h3>📋 Daftar Backup Tersimpan</h3>
            <?php if (count($backupFiles) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backupFiles as $file): ?>
                            <tr>
                                <td><?= htmlspecialchars($file['name']) ?></td>
                                <td><?= number_format($file['size'] / 1024, 2) ?> KB</td>
                                <td><?= $file['date'] ?></td>
                                <td>
                                    <a href="../backup/<?= $file['name'] ?>" download class="btn" style="background:#3b82f6; padding:4px 12px; font-size:12px;">Download</a>
                                    <a href="backup_delete.php?file=<?= urlencode($file['name']) ?>" onclick="return confirm('Hapus file backup ini?')" class="btn" style="background:#ef4444; padding:4px 12px; font-size:12px;">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada backup tersimpan.</p>
            <?php endif; ?>
        </div>

        <div class="footer">Copyright &copy; 2025 LaundryKu</div>
    </div>
</body>

</html>