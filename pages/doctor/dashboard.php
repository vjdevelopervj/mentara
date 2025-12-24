<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];

// Hitung statistik
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sesi_chat WHERE id_dokter = ?");
$stmt->execute([$id_pengguna]);
$total_sesi = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sesi_chat WHERE id_dokter = ? AND status = 'aktif'");
$stmt->execute([$id_pengguna]);
$sesi_aktif = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sesi_chat WHERE id_dokter = ? AND status = 'selesai' AND DATE(dibuat_pada) = CURDATE()");
$stmt->execute([$id_pengguna]);
$selesai_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT id) as total FROM sesi_chat WHERE id_dokter = ?");
$stmt->execute([$id_pengguna]);
$total_pasien = $stmt->fetch()['total'];

// Dapatkan sesi chat aktif yang ditugaskan ke dokter ini
$stmt = $pdo->prepare("
    SELECT sc.*, COUNT(p.id) as jumlah_pesan 
    FROM sesi_chat sc 
    LEFT JOIN pesan p ON sc.id = p.id_sesi 
    WHERE sc.id_dokter = ? AND sc.status = 'aktif' 
    GROUP BY sc.id 
    ORDER BY sc.diperbarui_pada DESC
");
$stmt->execute([$id_pengguna]);
$sesi_aktif_list = $stmt->fetchAll();

// Dapatkan sesi terbaru
$stmt = $pdo->prepare("
    SELECT sc.*, COUNT(p.id) as jumlah_pesan 
    FROM sesi_chat sc 
    LEFT JOIN pesan p ON sc.id = p.id_sesi 
    WHERE sc.id_dokter = ? AND sc.status = 'selesai' 
    GROUP BY sc.id 
    ORDER BY sc.diperbarui_pada DESC 
    LIMIT 5
");
$stmt->execute([$id_pengguna]);
$sesi_terbaru = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Mentara</title>
    <link rel="stylesheet" href="../../assets/css/doctor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="doctor-container">
        <!-- Sidebar (Menu lama) -->
        <div class="sidebar">
            <h2>Panel Dokter</h2>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="notes.php"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="dashboard-header">
                <h1>Dashboard Dokter</h1>
                <div class="header-info">
                    <div class="welcome-message">
                        <p>Selamat datang kembali, <strong><?php echo $_SESSION['nama_pengguna']; ?></strong>!</p>
                        <small>Hari ini: <?php echo date('d F Y'); ?></small>
                    </div>
                    <div class="header-actions">
                        <button class="btn-notification">
                            <i class="fas fa-bell"></i>
                            <?php if ($sesi_aktif > 0): ?>
                                <span class="badge"><?php echo $sesi_aktif; ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_sesi; ?></h3>
                        <p>Total Sesi</p>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>12% dari bulan lalu</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $sesi_aktif; ?></h3>
                        <p>Sesi Aktif</p>
                    </div>
                    <?php if ($sesi_aktif > 0): ?>
                        <div class="stat-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Perlu tindakan</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $selesai_hari_ini; ?></h3>
                        <p>Selesai Hari Ini</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_pasien; ?></h3>
                        <p>Total Pasien</p>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>5 pasien baru</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="content-area">
                <!-- Active Sessions -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-comment-medical"></i> Sesi Chat Aktif</h3>
                        <a href="chat.php" class="btn-view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="sessions-list">
                        <?php if (count($sesi_aktif_list) > 0): ?>
                            <?php foreach ($sesi_aktif_list as $sesi): ?>
                                <div class="session-card">
                                    <div class="session-header">
                                        <div class="patient-avatar">
                                            <i class="fas fa-user-injured"></i>
                                        </div>
                                        <div class="patient-info">
                                            <h4><?php echo htmlspecialchars($sesi['nama_pasien']); ?></h4>
                                            <div class="patient-meta">
                                                <span><i class="fas fa-birthday-cake"></i> <?php echo $sesi['usia_pasien']; ?> tahun</span>
                                                <span><i class="fas fa-comment"></i> <?php echo $sesi['jumlah_pesan']; ?> pesan</span>
                                                <span class="status-badge active">Aktif</span>
                                            </div>
                                        </div>
                                        <div class="session-time">
                                            <i class="far fa-clock"></i>
                                            <?php echo waktu_relatif($sesi['diperbarui_pada']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="session-body">
                                        <div class="complaint">
                                            <strong>Keluhan:</strong>
                                            <p><?php echo htmlspecialchars($sesi['keluhan']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="session-actions">
                                        <a href="chat_session.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-comment-medical"></i> Lanjutkan Chat
                                        </a>
                                        <a href="notes.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn btn-secondary">
                                            <i class="fas fa-file-medical"></i> Buat Catatan
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-comment-slash"></i>
                                <h4>Tidak ada sesi chat aktif</h4>
                                <p>Tidak ada pasien yang sedang menunggu chat saat ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Sessions -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-history"></i> Sesi Terbaru</h3>
                        <a href="history.php" class="btn-view-all">Lihat History <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="recent-sessions">
                        <?php if (count($sesi_terbaru) > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Pasien</th>
                                        <th>Keluhan</th>
                                        <th>Jumlah Pesan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sesi_terbaru as $sesi): ?>
                                        <tr>
                                            <td>
                                                <div class="table-patient">
                                                    <div class="patient-avatar small">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($sesi['nama_pasien']); ?></strong>
                                                        <small><?php echo $sesi['usia_pasien']; ?> tahun</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="complaint-preview">
                                                    <?php echo potongTeks($sesi['keluhan'], 60); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="message-count">
                                                    <i class="fas fa-comment"></i>
                                                    <?php echo $sesi['jumlah_pesan']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d M Y', strtotime($sesi['diperbarui_pada'])); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge completed">Selesai</span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="notes.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn-icon" title="Lihat Catatan">
                                                        <i class="fas fa-file-medical"></i>
                                                    </a>
                                                    <a href="chat_session.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn-icon" title="Lihat Chat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <h4>Belum ada sesi konseling</h4>
                                <p>Belum ada sesi konseling yang selesai.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-bolt"></i> Akses Cepat</h3>
                    </div>
                    
                    <div class="quick-actions-grid">
                        <a href="chat.php" class="quick-action">
                            <div class="quick-action-icon">
                                <i class="fas fa-comment-medical"></i>
                            </div>
                            <span>Mulai Chat Baru</span>
                        </a>
                        <a href="notes.php" class="quick-action">
                            <div class="quick-action-icon">
                                <i class="fas fa-file-medical-alt"></i>
                            </div>
                            <span>Catatan Sesi</span>
                        </a>
                        <a href="patients.php" class="quick-action">
                            <div class="quick-action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span>Daftar Pasien</span>
                        </a>
                        <a href="profile.php" class="quick-action">
                            <div class="quick-action-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <span>Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Notification bell
        document.querySelector('.btn-notification').addEventListener('click', function() {
            alert('Fitur notifikasi akan datang!');
        });

        // Hover effects for cards
        document.querySelectorAll('.stat-card, .session-card, .quick-action').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '';
            });
        });

        // Auto refresh page every 60 seconds untuk update status
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
    <script src="../../assets/js/doctor_sidebar.js"></script>
</body>
</html>
