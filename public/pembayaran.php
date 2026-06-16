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
        /* style sama */
    </style>
</head>

<body>
    <div class="sidebar">...</div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Pembayaran Laundry</h2>
            <div class="user-info">Halo, <?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <form method="post">
            <select name="id_transaksi" required>
                <option value="">Pilih Transaksi (Belum Lunas)</option>
                <?php while ($t = $transaksiList->fetch_assoc()): $sisa = $t['total_harga'] - $t['sudah_dibayar']; ?>
                    <option value="<?= $t['id_transaksi'] ?>"><?= htmlspecialchars($t['nama_pelanggan']) ?> - Total: Rp <?= number_format($t['total_harga'], 0, ',', '.') ?> (Sisa: Rp <?= number_format($sisa, 0, ',', '.') ?>)</option>
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
                </table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($row = $result->fetch_assoc()):
                        $id_pesanan = "PS" . str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT);
                    ?>
                        <tr>
                            <td style="text-align: center;"><?= $no++; ?></td>
                            <td><?= $id_pesanan ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td><?= $row['tanggal_bayar'] ?></td>
                            <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.') ?></td>
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