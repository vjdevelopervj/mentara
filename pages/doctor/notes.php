<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = isset($_GET['id_sesi']) ? (int) $_GET['id_sesi'] : 0;
$sesi = null;
$catatan = '';
$daftar_sesi = [];
$error = '';

// Tangani penyimpanan catatan
if ($id_sesi > 0) {
    $stmt = $pdo->prepare("SELECT * FROM sesi_chat WHERE id = ? AND id_dokter = ?");
    $stmt->execute([$id_sesi, $id_pengguna]);
    $sesi = $stmt->fetch();

    if (!$sesi) {
        $error = "Sesi tidak ditemukan atau bukan milik Anda.";
        $id_sesi = 0;
    } else {
        $catatan = $sesi['catatan_dokter'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isi_catatan = bersihkan($_POST['catatan'] ?? '');
            $stmt = $pdo->prepare("UPDATE sesi_chat SET catatan_dokter = ?, diperbarui_pada = NOW() WHERE id = ? AND id_dokter = ?");
            $stmt->execute([$isi_catatan, $id_sesi, $id_pengguna]);
            $catatan = $isi_catatan;
            $berhasil = "Catatan berhasil disimpan.";
        }
    }
}

if ($id_sesi === 0) {
    $stmt = $pdo->prepare("
        SELECT id, nama_pasien, usia_pasien, keluhan, status, diperbarui_pada
        FROM sesi_chat
        WHERE id_dokter = ?
        ORDER BY diperbarui_pada DESC
    ");
    $stmt->execute([$id_pengguna]);
    $daftar_sesi = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Sesi - Dokter Mentara</title>
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
                <li><a href="notes.php" class="active"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Catatan Sesi Konsultasi</h1>
            </div>

            <div class="content-area">
                <?php if ($id_sesi > 0 && $sesi): ?>
                    <div class="content-section">
                        <div class="section-header">
                            <h3><i class="fas fa-sticky-note"></i> Catatan Sesi</h3>
                        </div>
                        <div class="section-body">
                            <div class="session-card">
                                <div class="session-header">
                                    <div class="patient-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="patient-info">
                                        <h4><?php echo htmlspecialchars($sesi['nama_pasien']); ?></h4>
                                        <div class="patient-meta">
                                            <span><i class="fas fa-birthday-cake"></i> <?php echo $sesi['usia_pasien']; ?> tahun</span>
                                            <span class="status-badge <?php echo $sesi['status'] === 'aktif' ? 'active' : 'completed'; ?>">
                                                <?php echo ucfirst($sesi['status']); ?>
                                            </span>
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
                            </div>

                            <?php if (isset($berhasil)): ?>
                                <div class="alert alert-success"><?php echo $berhasil; ?></div>
                            <?php endif; ?>

                            <form action="notes.php?id_sesi=<?php echo $id_sesi; ?>" method="post" class="notes-form">
                                <div class="form-group">
                                    <label for="catatan">Catatan Perkembangan Pasien:</label>
                                    <textarea id="catatan" name="catatan" rows="15" placeholder="Tuliskan catatan perkembangan pasien, diagnosis, rencana tindakan, dll."><?php echo htmlspecialchars($catatan ?? ''); ?></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                                    <a href="chat.php?id_sesi=<?php echo $id_sesi; ?>" class="btn btn-secondary">Kembali ke Chat</a>
                                    <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="content-section">
                        <div class="section-header">
                            <h3><i class="fas fa-list"></i> Pilih Sesi Konsultasi</h3>
                        </div>
                        <div class="section-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php else: ?>
                                <div class="alert alert-warning">Pilih sesi untuk menulis catatan.</div>
                            <?php endif; ?>

                            <?php if (count($daftar_sesi) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Pasien</th>
                                            <th>Keluhan</th>
                                            <th>Status</th>
                                            <th>Terakhir Update</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daftar_sesi as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="table-patient">
                                                        <div class="patient-avatar small">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($item['nama_pasien']); ?></strong>
                                                            <small><?php echo $item['usia_pasien']; ?> tahun</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="complaint-preview">
                                                        <?php echo potongTeks($item['keluhan'], 60); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $item['status'] === 'aktif' ? 'active' : 'completed'; ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo waktu_relatif($item['diperbarui_pada']); ?></td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="notes.php?id_sesi=<?php echo $item['id']; ?>" class="btn-icon" title="Buka Catatan">
                                                            <i class="fas fa-sticky-note"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-comment-slash"></i>
                                    <h4>Belum ada sesi konsultasi</h4>
                                    <p>Mulai dari daftar chat untuk membuat sesi baru.</p>
                                </div>
                            <?php endif; ?>

                            <div class="form-actions">
                                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../../assets/js/doctor_sidebar.js"></script>
</body>
</html>
