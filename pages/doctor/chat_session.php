<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = $_GET['id_sesi'] ?? 0;
$new_chat = isset($_GET['new']) && $_GET['new'] == 'true';

// Jika membuat chat baru
if ($new_chat) {
    // Form untuk membuat sesi baru
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_pasien = bersihkan($_POST['nama_pasien']);
        $usia_pasien = (int)$_POST['usia_pasien'];
        $keluhan = bersihkan($_POST['keluhan']);
        
        $stmt = $pdo->prepare("
            INSERT INTO sesi_chat (id_dokter, nama_pasien, usia_pasien, keluhan, status) 
            VALUES (?, ?, ?, ?, 'aktif')
        ");
        $stmt->execute([$id_pengguna, $nama_pasien, $usia_pasien, $keluhan]);
        $id_sesi = $pdo->lastInsertId();
        
        header("Location: chat_session.php?id_sesi=$id_sesi");
        exit;
    }
    
    // Tampilkan form pembuatan chat baru
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat Baru - Dokter Mentara</title>
        <link rel="stylesheet" href="../../assets/css/doctor.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .new-chat-container {
                max-width: 600px;
                margin: 2rem auto;
                background: white;
                border-radius: 10px;
                box-shadow: 0 2px 20px rgba(0,0,0,0.1);
                padding: 2rem;
            }
            
            .new-chat-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            
            .new-chat-header i {
                font-size: 3rem;
                color: #007bff;
                margin-bottom: 1rem;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                color: #333;
            }
            
            .form-group input,
            .form-group textarea {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-family: 'Poppins', sans-serif;
            }
            
            .form-group textarea {
                min-height: 120px;
                resize: vertical;
            }
            
            .form-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border-radius: 5px;
                border: none;
                cursor: pointer;
                font-weight: 500;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .btn-primary {
                background: #007bff;
                color: white;
            }
            
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="doctor-container">
            <div class="sidebar">
                <h2>Panel Dokter</h2>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="notes.php"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="main-content">
                <div class="new-chat-container">
                    <div class="new-chat-header">
                        <i class="fas fa-comment-medical"></i>
                        <h1>Mulai Chat Baru</h1>
                        <p>Isi informasi pasien untuk memulai sesi konseling</p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nama_pasien"><i class="fas fa-user"></i> Nama Pasien</label>
                            <input type="text" id="nama_pasien" name="nama_pasien" required 
                                   placeholder="Masukkan nama pasien">
                        </div>
                        
                        <div class="form-group">
                            <label for="usia_pasien"><i class="fas fa-birthday-cake"></i> Usia Pasien</label>
                            <input type="number" id="usia_pasien" name="usia_pasien" required 
                                   min="1" max="120" placeholder="Usia pasien">
                        </div>
                        
                        <div class="form-group">
                            <label for="keluhan"><i class="fas fa-exclamation-triangle"></i> Keluhan Utama</label>
                            <textarea id="keluhan" name="keluhan" required 
                                      placeholder="Jelaskan keluhan utama pasien..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="chat.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-comment-medical"></i> Mulai Chat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika mengakses chat yang sudah ada
$stmt = $pdo->prepare("SELECT * FROM sesi_chat WHERE id = ? AND id_dokter = ?");
$stmt->execute([$id_sesi, $id_pengguna]);
$sesi = $stmt->fetch();

if (!$sesi) {
    header('Location: chat.php');
    exit;
}

// Tangani pengiriman pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['pesan'])) {
        $pesan = bersihkan($_POST['pesan']);
        if (!empty(trim($pesan))) {
            $stmt = $pdo->prepare("INSERT INTO pesan (id_sesi, pengirim, pesan) VALUES (?, 'dokter', ?)");
            $stmt->execute([$id_sesi, $pesan]);

            // Perbarui timestamp sesi
            $stmt = $pdo->prepare("UPDATE sesi_chat SET diperbarui_pada = NOW() WHERE id = ?");
            $stmt->execute([$id_sesi]);
            
            header("Location: chat_session.php?id_sesi=$id_sesi");
            exit;
        }
    }
    
    // Tangani selesai sesi
    if (isset($_POST['selesai_sesi'])) {
        $stmt = $pdo->prepare("UPDATE sesi_chat SET status = 'selesai' WHERE id = ?");
        $stmt->execute([$id_sesi]);
        header("Location: chat.php");
        exit;
    }
    
    // Tangani batalkan sesi
    if (isset($_POST['batalkan_sesi'])) {
        $stmt = $pdo->prepare("UPDATE sesi_chat SET status = 'dibatalkan' WHERE id = ?");
        $stmt->execute([$id_sesi]);
        header("Location: chat.php");
        exit;
    }
}

// Dapatkan pesan
$stmt = $pdo->prepare("SELECT * FROM pesan WHERE id_sesi = ? ORDER BY dibuat_pada ASC");
$stmt->execute([$id_sesi]);
$daftar_pesan = $stmt->fetchAll();

// Gunakan file chat.php yang sudah ada untuk tampilan chat
include 'chat.php';
?>