<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_admin();

// Dapatkan rating
$stmt = $pdo->query("SELECT sc.rating, sc.komentar_rating, sc.dibuat_pada, sc.nama_pasien, p.nama as nama_dokter FROM sesi_chat sc JOIN pengguna p ON sc.id_dokter = p.id WHERE sc.rating IS NOT NULL ORDER BY sc.dibuat_pada DESC");
$daftar_rating = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating dan Penilaian - Admin Mentara</title>
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
                <li><a href="consultations.php"><i class="fas fa-list"></i> Konsultasi</a></li>
                <li><a href="ratings.php" class="active"><i class="fas fa-star"></i> Rating</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Rating dan Penilaian</h1>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_rating as $rating): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($rating['dibuat_pada'])); ?></td>
                        <td><?php echo htmlspecialchars($rating['nama_pasien']); ?></td>
                        <td><?php echo htmlspecialchars($rating['nama_dokter']); ?></td>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="color: <?php echo $i <= $rating['rating'] ? '#ffc107' : '#ddd'; ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?php echo htmlspecialchars($rating['komentar_rating']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
