<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file'];
    if ($file['error'] != 0) {
        $message = "❌ Error upload file!";
        $messageType = 'danger';
    } else {
        $fileContent = file_get_contents($file['tmp_name']);
        if ($fileContent === false) {
            $message = "❌ Gagal membaca file!";
            $messageType = 'danger';
        } else {
            $queries = explode(";\n", $fileContent);
            $conn->query("SET FOREIGN_KEY_CHECKS=0");
            $error = false;
            $count = 0;

            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    if (!$conn->query($query)) {
                        $error = true;
                        $message = "❌ Error pada query: " . $conn->error;
                        $messageType = 'danger';
                        break;
                    }
                    $count++;
                }
            }

            $conn->query("SET FOREIGN_KEY_CHECKS=1");

            if (!$error) {
                $message = "✅ Restore berhasil! $count query dijalankan.";
                $messageType = 'success';
            }
        }
    }
} else {
    header("Location: backup.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Restore Database - LaundryKu</title>
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
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .alert {
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            background: #0f172a;
            border: none;
            padding: 12px 30px;
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
    </style>
</head>

<body>
    <div class="card">
        <h2>📥 Restore Database</h2>
        <div style="width:60px; height:60px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; margin:20px auto;">
            <?= $messageType == 'success' ? '✅' : '❌' ?>
        </div>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <a href="backup.php" class="btn">← Kembali ke Backup</a>
        <?php if ($messageType == 'success'): ?>
            <a href="dashboard.php" class="btn" style="background:#10b981; margin-left:10px;">🏠 Dashboard</a>
        <?php endif; ?>
    </div>
</body>

</html>