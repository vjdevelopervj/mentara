<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_admin();

// Dapatkan statistik
$stmt = $pdo->query("SELECT COUNT(*) as total_doctors FROM pengguna WHERE peran = 'dokter' AND aktif = 1");
$total_doctors = $stmt->fetch()['total_doctors'];

$stmt = $pdo->query("SELECT COUNT(*) as active_sessions FROM sesi_chat WHERE status = 'aktif'");
$active_sessions = $stmt->fetch()['active_sessions'];

$stmt = $pdo->query("SELECT AVG(rating) as avg_rating FROM sesi_chat WHERE rating IS NOT NULL");
$avg_rating = $stmt->fetch()['avg_rating'];
$avg_rating = $avg_rating ? round($avg_rating, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Mentara</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="doctors.php"><i class="fas fa-user-md"></i> Kelola Dokter</a></li>
                <li><a href="consultations.php"><i class="fas fa-list"></i> Konsultasi</a></li>
                <li><a href="ratings.php"><i class="fas fa-star"></i> Rating</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
                <div class="header-info">
                    <div class="welcome-message">
                        <p>Selamat datang kembali, <strong><?php echo htmlspecialchars($_SESSION['nama_pengguna'] ?? 'Admin'); ?></strong>!</p>
                        <small>Hari ini: <?php echo date('d F Y'); ?></small>
                    </div>
                    <div class="header-actions">
                        <a href="consultations.php" class="btn-notification" title="Lihat konsultasi">
                            <i class="fas fa-bell"></i>
                            <?php if ($active_sessions > 0): ?>
                                <span class="badge"><?php echo $active_sessions; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_doctors; ?></h3>
                        <p>Dokter Aktif</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $active_sessions; ?></h3>
                        <p>Sesi Berjalan</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $avg_rating; ?>/5</h3>
                        <p>Rating Rata-rata</p>
                    </div>
                </div>
            </div>

            <div class="content-area">
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Aktivitas Terbaru</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Aktivitas</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Dapatkan aktivitas terbaru (disederhanakan)
                            $stmt = $pdo->query("SELECT sc.nama_pasien, sc.dibuat_pada, COUNT(p.id) as jumlah_pesan FROM sesi_chat sc LEFT JOIN pesan p ON sc.id = p.id_sesi GROUP BY sc.id ORDER BY sc.dibuat_pada DESC LIMIT 10");
                            while ($aktivitas = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($aktivitas['dibuat_pada'])); ?></td>
                                <td><i class="fas fa-comment-dots"></i> Sesi Konsultasi</td>
                                <td>
                                    <span class="activity-detail"><?php echo htmlspecialchars($aktivitas['nama_pasien']); ?></span>
                                    <span class="message-count">
                                        <i class="fas fa-comment"></i>
                                        <?php echo $aktivitas['jumlah_pesan']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
