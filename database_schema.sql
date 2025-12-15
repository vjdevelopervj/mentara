-- Skema database untuk sistem konsultasi kesehatan mental Mentara

CREATE DATABASE IF NOT EXISTS mentara_db;
USE mentara_db;

-- Tabel pengguna (untuk dokter dan admin)
CREATE TABLE pengguna (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_pengguna VARCHAR(50) UNIQUE NOT NULL,
    kata_sandi VARCHAR(255) NOT NULL,
    peran ENUM('admin', 'dokter') NOT NULL,
    nama VARCHAR(100) NOT NULL,
    spesialisasi VARCHAR(100),
    jadwal TEXT,
    aktif BOOLEAN DEFAULT TRUE,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel sesi chat
CREATE TABLE sesi_chat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_pasien VARCHAR(100) NOT NULL,
    usia_pasien INT NOT NULL,
    keluhan TEXT NOT NULL,
    id_dokter INT,
    status ENUM('aktif', 'selesai') DEFAULT 'aktif',
    rating INT CHECK (rating >= 1 AND rating <= 5),
    komentar_rating TEXT,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dokter) REFERENCES pengguna(id)
);

-- Tabel catatan sesi (untuk catatan pribadi dokter)
CREATE TABLE catatan_sesi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_sesi INT NOT NULL,
    id_dokter INT NOT NULL,
    catatan TEXT,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sesi) REFERENCES sesi_chat(id) ON DELETE CASCADE,
    FOREIGN KEY (id_dokter) REFERENCES pengguna(id)
);

-- Tabel pesan
CREATE TABLE pesan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_sesi INT NOT NULL,
    pengirim ENUM('pasien', 'dokter') NOT NULL,
    pesan TEXT NOT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sesi) REFERENCES sesi_chat(id) ON DELETE CASCADE
);

-- Masukkan data contoh
INSERT INTO pengguna (nama_pengguna, kata_sandi, peran, nama, spesialisasi, jadwal) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', NULL, NULL),
('dr.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dokter', 'Dr. John Smith', 'Psikologi Klinis', 'Senin-Jumat: 09:00-17:00'),
('dr.jane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dokter', 'Dr. Jane Doe', 'Psikiatri', 'Senin-Rabu: 08:00-16:00, Jumat: 10:00-14:00');

-- Catatan: Kata sandi untuk semua pengguna adalah 'password' (hashed)
