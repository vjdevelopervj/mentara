<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_admin();

// Dapatkan konsultasi
$stmt = $pdo->query("SELECT sc.*, p.nama as nama_dokter, COUNT(ps.id) as jumlah_pesan FROM sesi_chat sc LEFT JOIN pengguna p ON sc.id_dokter = p.id LEFT JOIN pesan ps ON sc.id = ps.id_sesi GROUP BY sc.id ORDER BY sc.dibuat_pada DESC");
$daftar_konsultasi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Konsultasi - Admin Mentara</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="doctors.php"><i class="fas fa-user-md"></i> Kelola Dokter</a></li>
                <li><a href="consultations.php" class="active"><i class="fas fa-list"></i> Konsultasi</a></li>
                <li><a href="ratings.php"><i class="fas fa-star"></i> Rating</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Daftar Konsultasi</h1>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Usia</th>
                        <th>Keluhan</th>
                        <th>Dokter</th>
                        <th>Pesan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_konsultasi as $konsultasi): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($konsultasi['dibuat_pada'])); ?></td>
                        <td><?php echo htmlspecialchars($konsultasi['nama_pasien']); ?></td>
                        <td><?php echo $konsultasi['usia_pasien']; ?></td>
                        <td><?php echo htmlspecialchars($konsultasi['keluhan']); ?></td>
                        <td><?php echo htmlspecialchars($konsultasi['nama_dokter'] ?? 'Belum ditugaskan'); ?></td>
                        <td><?php echo $konsultasi['jumlah_pesan']; ?></td>
                        <td><?php echo ucfirst($konsultasi['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
