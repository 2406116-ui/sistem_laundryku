-- Backup Database LaundryKu
-- Tanggal: 2026-06-28 11:17:44
-- Database: 

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `demo_drop`;
CREATE TABLE `demo_drop` (
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `pelanggan`;
CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pelanggan` VALUES
('1', 'Budidu', 'Jl. Merpati 10', '081234567890'),
('2', 'Siti', 'Jl. Kenanga 5', '085678912345'),
('3', 'rio cahya', 'kerkof', '088123456789'),
('4', 'nana', 'jayaraga', '088120293204'),
('5', 'aldi', 'tarogong', '089765432190'),
('6', 'ijah', 'cibatu', '089876654321'),
('7', 'lala', 'kp.indah', '088120989898765'),
('8', 'samsul', 'jl.proklamasi', '081234567890'),
('9', 'bobon', 'arab', '0987654321'),
('10', 'korun', 'cicalengka', '0987654321234'),
('11', 'rapi', 'jl.proklamasi', '088120989898765'),
('12', 'isan', 'jayaraga', '085678912345');

DROP TABLE IF EXISTS `pembayaran`;
CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `metode` enum('Tunai','Transfer','E-Wallet') DEFAULT 'Tunai',
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_transaksi` (`id_transaksi`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pembayaran` VALUES
('1', '1', '2026-06-08', '10000.00', 'Tunai'),
('2', '2', '2026-06-08', '10000.00', 'Transfer'),
('3', '1', '2026-06-13', '99.81', 'Transfer'),
('4', '10', '2026-07-17', '0.04', 'E-Wallet'),
('5', '8', '2026-08-01', '40.00', 'Tunai'),
('6', '12', '2026-06-17', '120.00', 'Tunai'),
('7', '13', '2026-06-16', '32.00', 'Tunai'),
('8', '14', '2026-06-18', '120.00', 'Tunai');

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `jenis` varchar(50) NOT NULL DEFAULT 'Baju',
  `tanggal_masuk` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `berat_kg` decimal(5,2) NOT NULL,
  `harga_perkg` decimal(10,2) NOT NULL,
  `total_harga` decimal(10,2) GENERATED ALWAYS AS (`berat_kg` * `harga_perkg`) STORED,
  `status` enum('proses','selesai','diambil') DEFAULT 'proses',
  PRIMARY KEY (`id_transaksi`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transaksi` VALUES
('1', '1', 'Baju', '2026-06-08', '2026-06-11', '2.50', '8000.00', '20000.00', ''),
('2', '2', 'Baju', '2026-06-08', '2026-06-10', '1.00', '10000.00', '10000.00', ''),
('4', '4', 'Baju', '2026-06-02', '2026-06-20', '0.04', '7000.00', '280.00', ''),
('5', '4', 'Baju', '2026-06-12', '2026-06-19', '1.00', '8000.00', '8000.00', ''),
('6', '3', 'Baju', '2026-06-11', '2026-06-19', '2.00', '12000.00', '24000.00', ''),
('7', '1', 'Sepatu', '2026-06-14', '2026-06-21', '2.00', '15000.00', '30000.00', ''),
('8', '7', 'Baju', '2026-06-15', '2026-06-22', '5.00', '8000.00', '40000.00', ''),
('9', '7', 'Baju', '2026-06-14', '2026-06-21', '7.00', '8000.00', '56000.00', ''),
('10', '8', 'Karpet', '2026-07-10', '2026-07-17', '10.00', '20000.00', '200000.00', ''),
('11', '9', 'Karpet', '2026-06-16', '2026-06-17', '8.00', '20000.00', '160000.00', 'diambil'),
('12', '10', 'Selimut', '2026-06-16', '2026-06-17', '10.00', '12000.00', '120000.00', 'proses'),
('13', '11', 'Baju', '2026-06-16', '2026-06-16', '4.00', '8000.00', '32000.00', 'proses'),
('14', '12', 'Selimut', '2026-06-17', '2026-06-16', '10.00', '12000.00', '120000.00', 'proses');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` VALUES
('1', 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9');

SET FOREIGN_KEY_CHECKS=1;
