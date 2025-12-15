<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];

// Dapatkan history konsultasi
$stmt = $pdo->prepare("SELECT sc.*, COUNT(p.id) as jumlah_pesan, sc.rating, sc.komentar_rating FROM sesi_chat sc LEFT JOIN pesan p ON sc.id = p.id_sesi WHERE sc.id_dokter = ? AND sc.status = 'selesai' GROUP BY sc.id ORDER BY sc.diperbarui_pada DESC");
$stmt->execute([$id_pengguna]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Konsultasi - Dokter Mentara</title>
    <link rel="stylesheet" href="../../assets/css/doctor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="doctor-container">
        <div class="sidebar">
            <h2>Panel Dokter</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="notes.php"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php" class="active"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>History Konsultasi</h1>

            <?php if (count($history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pasien</th>
                            <th>Usia</th>
                            <th>Keluhan</th>
                            <th>Pesan</th>
                            <th>Rating</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $sesi): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($sesi['diperbarui_pada'])); ?></td>
                            <td><?php echo htmlspecialchars($sesi['nama_pasien']); ?></td>
                            <td><?php echo $sesi['usia_pasien']; ?></td>
                            <td><?php echo htmlspecialchars($sesi['keluhan']); ?></td>
                            <td><?php echo $sesi['jumlah_pesan']; ?></td>
                            <td>
                                <?php if ($sesi['rating']): ?>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?php echo $i <= $sesi['rating'] ? '#ffc107' : '#ddd'; ?>"></i>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    Belum ada rating
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="notes.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn">Lihat Catatan</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada history konsultasi.</p>
            <?php endif; ?>

            <div style="margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
