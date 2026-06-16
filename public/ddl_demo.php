<?php
// File: ddl_demo.php
// Demonstrasi eksekusi DDL (CREATE, ALTER, DROP) dari source code PHP
session_start();
if (!isset($_SESSION['login'])) {
     Jika belum login, arahkan ke login (opsional, biar aman)
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

$output = "";

// 1. CREATE TABLE (tabel sementara)
$sql_create = "CREATE TABLE IF NOT EXISTS demo_ddl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
)";
if ($conn->query($sql_create)) {
    $output .= "✅ CREATE TABLE berhasil: tabel `demo_ddl` dibuat.<br>";
} else {
    $output .= "❌ Gagal CREATE: " . $conn->error . "<br>";
}

// 2. ALTER TABLE (tambah kolom)
$sql_alter = "ALTER TABLE demo_ddl ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
if ($conn->query($sql_alter)) {
    $output .= "✅ ALTER TABLE berhasil: kolom `created_at` ditambahkan.<br>";
} else {
    $output .= "❌ Gagal ALTER: " . $conn->error . "<br>";
}

// 3. DROP TABLE (hapus tabel demo) - dengan konfirmasi via GET
if (isset($_GET['drop']) && $_GET['drop'] == 'yes') {
    $sql_drop = "DROP TABLE demo_ddl";
    if ($conn->query($sql_drop)) {
        $output .= "✅ DROP TABLE berhasil: tabel `demo_ddl` dihapus.<br>";
    } else {
        $output .= "❌ Gagal DROP: " . $conn->error . "<br>";
    }
} else {
    $output .= "⚠️ DROP TABLE belum dijalankan. <a href='?drop=yes'>Klik di sini untuk menghapus tabel demo</a><br>";
}

// Tampilkan struktur tabel demo jika masih ada
$struktur = "";
$cek_tabel = $conn->query("SHOW TABLES LIKE 'demo_ddl'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $struktur .= "<h3>Struktur tabel `demo_ddl` (saat ini):</h3>";
    $struktur .= "<pre>";
    $desc = $conn->query("DESCRIBE demo_ddl");
    while ($row = $desc->fetch_assoc()) {
        $struktur .= "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }
    $struktur .= "</pre>";
} else {
    $struktur = "<p>Tabel `demo_ddl` tidak ada (sudah dihapus atau belum dibuat).</p>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Demo DDL - Eksekusi Source Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }

        h2 {
            color: #0f172a;
        }

        .output {
            background: #f8fafc;
            padding: 15px;
            border-radius: 15px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }

        .info {
            font-size: 14px;
            color: #475569;
            margin-top: 20px;
        }

        pre {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 10px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>📌 Demonstrasi DDL dari Source Code PHP</h2>
        <p>Perintah DDL (CREATE, ALTER, DROP) dijalankan melalui script PHP menggunakan objek <code>mysqli</code>.</p>
        <div class="output">
            <?= $output ?>
        </div>
        <div>
            <?= $struktur ?>
        </div>
        <div class="info">
            <strong>Keterangan:</strong><br>
            - <strong>CREATE TABLE</strong> → membuat tabel sementara <code>demo_ddl</code>.<br>
            - <strong>ALTER TABLE</strong> → menambah kolom <code>created_at</code>.<br>
            - <strong>DROP TABLE</strong> → menghapus tabel demo (perlu konfirmasi klik).<br>
            <br>
            ✅ Ini membuktikan bahwa perintah DDL dapat dieksekusi langsung dari source code PHP (bukan hanya dari CMD/phpMyAdmin).
        </div>
    </div>
</body>

</html>