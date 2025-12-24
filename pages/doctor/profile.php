<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];

// Dapatkan data pengguna
$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
$stmt->execute([$id_pengguna]);
$pengguna = $stmt->fetch();

// Tangani pembaruan profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = bersihkan($_POST['nama']);
    $spesialisasi = bersihkan($_POST['spesialisasi']);
    $jadwal = bersihkan($_POST['jadwal']);

    $stmt = $pdo->prepare("UPDATE pengguna SET nama = ?, spesialisasi = ?, jadwal = ? WHERE id = ?");
    $stmt->execute([$nama, $spesialisasi, $jadwal, $id_pengguna]);

    $berhasil = "Profil berhasil diperbarui.";
    // Refresh data pengguna
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmt->execute([$id_pengguna]);
    $pengguna = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dokter - Mentara</title>
    <?php $doctor_css_version = filemtime(__DIR__ . '/../../assets/css/doctor.css'); ?>
    <link rel="stylesheet" href="../../assets/css/doctor.css?v=<?php echo $doctor_css_version; ?>">
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
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Profile Dokter</h1>
            </div>

            <div class="content-area">
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-user-cog"></i> Informasi Profil</h3>
                    </div>
                    <div class="section-body">
                        <?php if (isset($berhasil)): ?>
                            <div class="alert alert-success"><?php echo $berhasil; ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="post" class="form-card profile-form">
                            <div class="form-group full">
                                <label for="nama_pengguna">Nama Pengguna:</label>
                                <input type="text" id="nama_pengguna" value="<?php echo htmlspecialchars($pengguna['nama_pengguna']); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label for="nama">Nama Lengkap:</label>
                                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($pengguna['nama']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="spesialisasi">Spesialisasi:</label>
                                <input type="text" id="spesialisasi" name="spesialisasi" value="<?php echo htmlspecialchars($pengguna['spesialisasi']); ?>" required>
                            </div>

                            <div class="form-group full">
                                <label for="jadwal">Jadwal Konsultasi:</label>
                                <textarea id="jadwal" name="jadwal" rows="4" required><?php echo htmlspecialchars($pengguna['jadwal']); ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Perbarui Profile</button>
                                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/doctor_sidebar.js"></script>
</body>
</html>
