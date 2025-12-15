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
            <h1>Dashboard Admin</h1>

            <div class="dashboard-cards">
                <div class="card">
                    <h3>Dokter Aktif</h3>
                    <p><?php echo $total_doctors; ?></p>
                </div>
                <div class="card">
                    <h3>Sesi Berjalan</h3>
                    <p><?php echo $active_sessions; ?></p>
                </div>
                <div class="card">
                    <h3>Rating Rata-rata</h3>
                    <p><?php echo $avg_rating; ?>/5</p>
                </div>
            </div>

            <h2>Aktivitas Terbaru</h2>
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
                        <td>Sesi Konsultasi</td>
                        <td><?php echo htmlspecialchars($aktivitas['nama_pasien']); ?> - <?php echo $aktivitas['jumlah_pesan']; ?> pesan</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
