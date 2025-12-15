-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 15, 2025 at 02:05 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mentara_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda_dokter`
--

CREATE TABLE `agenda_dokter` (
  `id` int NOT NULL,
  `id_dokter` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `durasi` int DEFAULT '60',
  `tipe` enum('konseling','assessment','rapor','other') DEFAULT 'konseling',
  `status` enum('terjadwal','berlangsung','selesai','dibatalkan') DEFAULT 'terjadwal',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);
--
-- Dumping data for table `agenda_dokter`
--

INSERT INTO `agenda_dokter` (`id`, `id_dokter`, `judul`, `deskripsi`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `durasi`, `tipe`, `status`, `dibuat_pada`) VALUES
(1, 2, 'Konseling Online', 'Sesi konseling dengan pasien baru', '2025-12-09', '09:00:00', '10:00:00', 60, 'konseling', 'selesai', '2025-12-09 03:19:12'),
(2, 2, 'Assessment Pasien', 'Assesment psikologis untuk diagnosis', '2025-12-09', '11:00:00', '12:00:00', 60, 'assessment', 'berlangsung', '2025-12-09 03:19:12'),
(3, 2, 'Rapat Tim', 'Diskusi kasus dengan tim psikolog', '2025-12-09', '14:00:00', '15:00:00', 60, 'rapor', 'terjadwal', '2025-12-09 03:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int NOT NULL,
  `nama_pengguna` varchar(50) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `peran` enum('admin','dokter') NOT NULL,
  `nama` varchar(100) NOT NULL,
  `spesialisasi` varchar(100) DEFAULT NULL,
  `jadwal` text,
  `aktif` tinyint(1) DEFAULT '1',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);
--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama_pengguna`, `kata_sandi`, `peran`, `nama`, `spesialisasi`, `jadwal`, `aktif`, `dibuat_pada`) VALUES
(1, 'admin', '$2a$12$Cb3HlQ/9AQXcgPMeChXedebVf.NgP/62.VlShtKOr4c7VuINWnBuW', 'admin', 'Administrator', NULL, NULL, 1, '2025-11-10 15:06:24'),
(2, 'dr.luthfi', '$2a$12$5z2zb/ckqDm3sjC/z37lau6g7JxPPOlO/fbsmY4N4LGVaMmk/LLBO', 'dokter', 'Dr. Luthfi Mubarok', 'Psikologi Klinis', 'Senin-Jumat: 09:00-17:00', 1, '2025-11-10 15:06:24'),
(3, 'dr.safira', '$2a$12$urNkdlphnGjd7FjvouSOCuzAf2LYWzPhFABmO.aWxpMl/UalbtVve', 'dokter', 'Dr. Safira Maulidia', 'Psikiatri', 'Senin-Rabu: 08:00-16:00, Jumat: 10:00-14:00', 1, '2025-11-10 15:06:24'),
(4, 'dr.chris', '$2a$12$JIUQhJwAHHFJEj5JD5jDwuPhbLXoJcazrsRmW3b9TPx3jTRPOO8kW', 'dokter', 'Dr. Christensen Rozy Klaping', 'Psikologi', 'Senin-Sabtu: 08:00-17:00', 1, '2025-11-25 02:27:52'),
(5, 'dr.haidar', '$2y$12$YoOwyyCcy/Z7Qa2hVHe1eeYMsTx8WIOsC5xVI9.jOBK8AO57CeV4e', 'dokter', 'Dr. Haidar Rasyid Zamzami', 'Dokter Hewan', 'Senin-Jumad: 08:00-15:00', 1, '2025-12-02 02:31:10');

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` int NOT NULL,
  `id_sesi` int NOT NULL,
  `pengirim` enum('pasien','dokter') NOT NULL,
  `pesan` text NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id`, `id_sesi`, `pengirim`, `pesan`, `dibuat_pada`) VALUES
(1, 1, 'pasien', 'Halo dok saya dika pratama saya ingin melakukan terapi psikologi', '2025-11-25 02:29:48'),
(2, 3, 'pasien', 'halo dok', '2025-12-01 03:05:38'),
(3, 4, 'pasien', 'halo dok', '2025-12-02 02:28:46'),
(4, 4, 'pasien', 'woii', '2025-12-02 02:29:42'),
(5, 4, 'pasien', 'et ya', '2025-12-02 02:31:15'),
(6, 4, 'pasien', 'dokter aneh', '2025-12-02 02:31:21'),
(7, 4, 'pasien', 'woi', '2025-12-02 02:31:36'),
(8, 4, 'pasien', 'gua kebelet ee gimana ini', '2025-12-02 02:31:43');

-- --------------------------------------------------------

--
-- Table structure for table `sesi_chat`
--

CREATE TABLE `sesi_chat` (
  `id` int NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `usia_pasien` int NOT NULL,
  `keluhan` text NOT NULL,
  `id_dokter` int DEFAULT NULL,
  `status` enum('aktif','selesai') DEFAULT 'aktif',
  `catatan_dokter` text,
  `rating` int DEFAULT NULL,
  `komentar_rating` text,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `sesi_chat`
--

INSERT INTO `sesi_chat` (`id`, `nama_pasien`, `usia_pasien`, `keluhan`, `id_dokter`, `status`, `catatan_dokter`, `rating`, `komentar_rating`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'Dika Pratama', 24, 'Kesehatan mental saya kurang baik', NULL, 'selesai', NULL, 5, '', '2025-11-25 02:29:18', '2025-11-25 02:30:02'),
(2, 'luthfi', 23, 'pilek batuk', NULL, 'aktif', NULL, NULL, NULL, '2025-12-01 03:05:19', '2025-12-01 03:05:19'),
(3, 'luthfi', 23, 'pilek batuk', NULL, 'aktif', NULL, NULL, NULL, '2025-12-01 03:05:21', '2025-12-01 03:05:21'),
(4, 'Fariz', 50, 'Kebelet ee', NULL, 'aktif', NULL, NULL, NULL, '2025-12-02 02:28:04', '2025-12-02 02:28:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda_dokter`
--
ALTER TABLE `agenda_dokter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dokter_tanggal` (`id_dokter`,`tanggal`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pengguna` (`nama_pengguna`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sesi` (`id_sesi`);

--
-- Indexes for table `sesi_chat`
--
ALTER TABLE `sesi_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda_dokter`
--
ALTER TABLE `agenda_dokter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sesi_chat`
--
ALTER TABLE `sesi_chat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda_dokter`
--
ALTER TABLE `agenda_dokter`
  ADD CONSTRAINT `agenda_dokter_ibfk_1` FOREIGN KEY (`id_dokter`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`id_sesi`) REFERENCES `sesi_chat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sesi_chat`
--
ALTER TABLE `sesi_chat`
  ADD CONSTRAINT `sesi_chat_ibfk_1` FOREIGN KEY (`id_dokter`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
