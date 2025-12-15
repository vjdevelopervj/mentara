<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = $_GET['id_sesi'] ?? 0;

// Dapatkan detail sesi
$stmt = $pdo->prepare("SELECT * FROM sesi_chat WHERE id = ? AND id_dokter = ?");
$stmt->execute([$id_sesi, $id_pengguna]);
$sesi = $stmt->fetch();

if (!$sesi) {
    header('Location: dashboard.php');
    exit;
}

// Dapatkan catatan yang ada
$stmt = $pdo->prepare("SELECT * FROM catatan_sesi WHERE id_sesi = ?");
$stmt->execute([$id_sesi]);
$catatan = $stmt->fetch();

// Tangani penyimpanan catatan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isi_catatan = bersihkan($_POST['catatan']);
    if ($catatan) {
        $stmt = $pdo->prepare("UPDATE catatan_sesi SET catatan = ?, diperbarui_pada = NOW() WHERE id_sesi = ?");
        $stmt->execute([$isi_catatan, $id_sesi]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO catatan_sesi (id_sesi, id_dokter, catatan) VALUES (?, ?, ?)");
        $stmt->execute([$id_sesi, $id_pengguna, $isi_catatan]);
    }
    $berhasil = "Catatan berhasil disimpan.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Sesi - Dokter Mentara</title>
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
                <li><a href="notes.php" class="active"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Catatan Sesi Konsultasi</h1>
            <p><strong>Pasien:</strong> <?php echo htmlspecialchars($sesi['nama_pasien']); ?> (<?php echo $sesi['usia_pasien']; ?> tahun)</p>
            <p><strong>Keluhan:</strong> <?php echo htmlspecialchars($sesi['keluhan']); ?></p>

            <?php if (isset($berhasil)): ?>
                <p style="color: green;"><?php echo $berhasil; ?></p>
            <?php endif; ?>

            <form action="notes.php?id_sesi=<?php echo $id_sesi; ?>" method="post">
                <div class="form-group">
                    <label for="catatan">Catatan Perkembangan Pasien:</label>
                    <textarea id="catatan" name="catatan" rows="15" placeholder="Tuliskan catatan perkembangan pasien, diagnosis, rencana tindakan, dll."><?php echo htmlspecialchars($catatan['catatan'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn">Simpan Catatan</button>
            </form>

            <div style="margin-top: 2rem;">
                <a href="chat.php?id_sesi=<?php echo $id_sesi; ?>" class="btn btn-primary">Kembali ke Chat</a>
                <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
