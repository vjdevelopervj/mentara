<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_admin();

$id_sesi = isset($_GET['id_sesi']) ? (int)$_GET['id_sesi'] : 0;
if ($id_sesi <= 0) {
    header('Location: consultations.php');
    exit;
}

// Detail sesi
$stmt = $pdo->prepare("
    SELECT sc.*, p.nama AS nama_dokter
    FROM sesi_chat sc
    LEFT JOIN pengguna p ON sc.id_dokter = p.id
    WHERE sc.id = ?
");
$stmt->execute([$id_sesi]);
$sesi = $stmt->fetch();

// Pesan untuk sesi ini
$daftar_pesan = [];
if ($sesi) {
    $stmt = $pdo->prepare("SELECT * FROM pesan WHERE id_sesi = ? ORDER BY dibuat_pada ASC, id ASC");
    $stmt->execute([$id_sesi]);
    $daftar_pesan = $stmt->fetchAll();
}

$status = $sesi['status'] ?? null;
$badge_class = 'completed';
$status_icon = 'fa-check-circle';
if ($status === 'aktif') {
    $badge_class = 'active';
    $status_icon = 'fa-clock';
} elseif ($status === 'dibatalkan') {
    $badge_class = 'cancelled';
    $status_icon = 'fa-times-circle';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Konsultasi - Admin Mentara</title>
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
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-comments"></i> Detail Konsultasi</h1>
                    <p>Lihat detail pesan untuk sesi konsultasi</p>
                </div>
                <div>
                    <a class="btn btn-secondary" href="consultations.php">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>

            <div class="content-area">
                <?php if (!$sesi): ?>
                    <div class="content-section">
                        <div class="section-body">
                            <div class="empty-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <h4>Sesi tidak ditemukan</h4>
                                <p>Data sesi konsultasi tidak tersedia.</p>
                                <a class="btn btn-secondary" href="consultations.php">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali ke Konsultasi
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="content-section">
                        <div class="section-header">
                            <h3><i class="fas fa-info-circle"></i> Informasi Sesi</h3>
                        </div>
                        <div class="section-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-user"></i> Pasien</div>
                                    <div class="info-value"><?php echo htmlspecialchars($sesi['nama_pasien']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-birthday-cake"></i> Usia</div>
                                    <div class="info-value"><?php echo (int)$sesi['usia_pasien']; ?> tahun</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-user-md"></i> Dokter</div>
                                    <div class="info-value"><?php echo htmlspecialchars($sesi['nama_dokter'] ?? 'Belum ditugaskan'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-calendar"></i> Dibuat</div>
                                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($sesi['dibuat_pada'])); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-signal"></i> Status</div>
                                    <div class="info-value">
                                        <span class="status-badge <?php echo $badge_class; ?>">
                                            <i class="fas <?php echo $status_icon; ?>"></i>
                                            <?php echo ucfirst($sesi['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-comment"></i> Pesan</div>
                                    <div class="info-value">
                                        <span class="message-count">
                                            <i class="fas fa-comment"></i>
                                            <?php echo count($daftar_pesan); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="complaint-summary">
                                <div class="summary-header">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h4>Keluhan</h4>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($sesi['keluhan'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="content-section">
                        <div class="section-header">
                            <h3><i class="fas fa-comments"></i> Percakapan</h3>
                        </div>
                        <div class="section-body">
                            <div class="chat-thread">
                                <div class="messages-container" id="messages">
                                    <?php if (count($daftar_pesan) === 0): ?>
                                        <div class="empty-chat">
                                            <i class="fas fa-comments"></i>
                                            <h3>Belum ada pesan</h3>
                                            <p>Pesan untuk sesi ini belum tersedia.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($daftar_pesan as $pesan): ?>
                                            <?php
                                            $pengirim = $pesan['pengirim'];
                                            $sender_label = $pengirim === 'dokter'
                                                ? ($sesi['nama_dokter'] ?? 'Dokter')
                                                : $sesi['nama_pasien'];
                                            $sender_icon = $pengirim === 'dokter' ? 'fa-user-md' : 'fa-user';
                                            ?>
                                            <div class="chat-message <?php echo htmlspecialchars($pengirim); ?>">
                                                <div class="message-avatar">
                                                    <i class="fas <?php echo $sender_icon; ?>"></i>
                                                </div>
                                                <div class="message-content">
                                                    <div class="message-header">
                                                        <span class="sender-name"><?php echo htmlspecialchars($sender_label); ?></span>
                                                        <span class="message-time"><?php echo date('d/m/Y H:i', strtotime($pesan['dibuat_pada'])); ?></span>
                                                    </div>
                                                    <div class="message-text"><?php echo nl2br(htmlspecialchars($pesan['pesan'])); ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto scroll ke pesan terbaru
        const messagesEl = document.getElementById('messages');
        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    </script>
</body>
</html>
