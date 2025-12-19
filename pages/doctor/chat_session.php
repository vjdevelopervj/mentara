<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = $_GET['id_sesi'] ?? null;

// Debug: Log session ID
error_log("Session ID: $id_sesi, Doctor ID: $id_pengguna");

// Jika membuat sesi baru
if (isset($_GET['new']) && $_GET['new'] == 'true') {
    // Buat sesi baru
    $stmt = $pdo->prepare("
        INSERT INTO sesi_chat (id_dokter, nama_pasien, status, dibuat_pada, diperbarui_pada) 
        VALUES (?, 'Pasien Baru', 'aktif', NOW(), NOW())
    ");
    $stmt->execute([$id_pengguna]);
    $id_sesi = $pdo->lastInsertId();
    
    header("Location: chat_session.php?id_sesi=$id_sesi");
    exit;
}

// Pastikan sesi ada dan milik dokter ini
$stmt = $pdo->prepare("
    SELECT sc.*, d.nama as nama_dokter
    FROM sesi_chat sc
    LEFT JOIN pengguna d ON sc.id_dokter = d.id
    WHERE sc.id = ? AND sc.id_dokter = ?
");
$stmt->execute([$id_sesi, $id_pengguna]);
$sesi = $stmt->fetch();

if (!$sesi) {
    error_log("Session not found or not owned by doctor. Session ID: $id_sesi, Doctor ID: $id_pengguna");
    header('Location: chat.php');
    exit;
}

// Tangani update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $status = bersihkan($_POST['status']);
    $stmt = $pdo->prepare("UPDATE sesi_chat SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id_sesi]);
    
    if ($status == 'selesai') {
        $stmt = $pdo->prepare("
            INSERT INTO pesan (id_sesi, pengirim, id, pesan) 
            VALUES (?, 'sistem', ?, 'Sesi konseling telah selesai. Terima kasih.')
        ");
        $stmt->execute([$id_sesi, $id_pengguna]);
    }
    
    header("Location: chat_session.php?id_sesi=$id_sesi");
    exit;
}

// Ambil pesan dan nama pengirim (dokter diambil dari sesi_chat.id_dokter)
$stmt = $pdo->prepare("    SELECT p.*, 
           CASE 
               WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter') 
               WHEN p.pengirim = 'sistem' THEN 'Sistem'
               ELSE 'Pasien' 
           END as nama_pengirim
    FROM pesan p
    LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id
    LEFT JOIN pengguna d ON sc.id_dokter = d.id
    WHERE p.id_sesi = ? 
    ORDER BY p.dibuat_pada ASC
");
$stmt->execute([$id_sesi]);
$messages = $stmt->fetchAll();

// Debug: Log jumlah pesan
error_log("Total messages: " . count($messages));

// Hitung statistik
$total_pesan = count($messages);
$pesan_dokter = count(array_filter($messages, fn($m) => $m['pengirim'] === 'dokter'));
$pesan_pasien = count(array_filter($messages, fn($m) => $m['pengirim'] === 'pasien'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Session - Dokter Mentara</title>
    <link rel="stylesheet" href="../../assets/css/doctor.css">
    <link rel="stylesheet" href="../../assets/css/chat_session.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="doctor-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Panel Dokter</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="chat.php" class="active"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="notes.php"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Session Header -->
            <div class="session-header">
                <div class="header-left">
                    <a href="chat.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <div class="patient-header">
                        <div class="patient-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="patient-details">
                            <h2><?php echo htmlspecialchars($sesi['nama_pasien']); ?></h2>
                            <div class="patient-meta">
                                <div class="status-badge <?php echo $sesi['status']; ?>">
                                    <?php echo ucfirst($sesi['status']); ?>
                                </div>
                                <?php if ($sesi['usia_pasien']): ?>
                                <span class="patient-age">
                                    <i class="fas fa-birthday-cake"></i>
                                    <?php echo $sesi['usia_pasien']; ?> tahun
                                </span>
                                <?php endif; ?>
                                <span class="session-duration">
                                    <i class="far fa-clock"></i>
                                    <?php echo waktu_relatif($sesi['dibuat_pada']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="session-stats">
                        <div class="stat-item">
                            <i class="fas fa-comment-dots"></i>
                            <span><?php echo $total_pesan; ?> pesan</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-user-md"></i>
                            <span><?php echo $pesan_dokter; ?> dokter</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo $pesan_pasien; ?> pasien</span>
                        </div>
                    </div>
                    
                    <div class="session-controls">
                        <form method="POST" class="status-form">
                            <select name="status" id="statusSelect" class="status-select">
                                <option value="aktif" <?php echo $sesi['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="selesai" <?php echo $sesi['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="dibatalkan" <?php echo $sesi['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                            <button type="submit" class="btn-status-update">
                                <i class="fas fa-sync-alt"></i>
                                Update
                            </button>
                        </form>
                        
                        <div class="action-buttons">
                            <button class="btn-action" onclick="printSession()" title="Cetak">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn-action" onclick="exportSession()" title="Export">
                                <i class="fas fa-download"></i>
                            </button>
                            <a href="notes.php?id_sesi=<?php echo $id_sesi; ?>" class="btn-action" title="Catatan">
                                <i class="fas fa-file-medical"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="chat-container">
                <!-- Messages Area -->
                <div class="chat-messages-wrapper">
                    <div class="messages-header">
                        <h3><i class="fas fa-comments"></i> Percakapan</h3>
                        <div class="messages-info">
                            <span class="unread-count" id="unreadCount"><?php echo $total_pesan; ?> pesan</span>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="empty-conversation">
                                <div class="empty-icon">
                                    <i class="fas fa-comment-slash"></i>
                                </div>
                                <h4>Belum ada percakapan</h4>
                                <p>Mulai percakapan dengan mengirim pesan pertama</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo $message['pengirim']; ?>" 
                                     data-id="<?php echo $message['id']; ?>"
                                     data-time="<?php echo $message['dibuat_pada']; ?>">
                                    <div class="message-avatar">
                                        <?php if ($message['pengirim'] == 'dokter'): ?>
                                            <div class="avatar-doctor">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        <?php elseif ($message['pengirim'] == 'sistem'): ?>
                                            <div class="avatar-system">
                                                <i class="fas fa-robot"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-patient">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="message-content-wrapper">
                                        <div class="message-header">
                                            <div class="sender-info">
                                                <strong class="sender-name"><?php echo htmlspecialchars($message['nama_pengirim']); ?></strong>
                                                <span class="sender-role <?php echo $message['pengirim']; ?>">
                                                    <?php echo $message['pengirim'] == 'dokter' ? 'Dokter' : ($message['pengirim'] == 'sistem' ? 'Sistem' : 'Pasien'); ?>
                                                </span>
                                            </div>
                                            <div class="message-meta">
                                                <span class="message-time">
                                                    <i class="far fa-clock"></i>
                                                    <?php echo date('H:i', strtotime($message['dibuat_pada'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="message-body">
                                            <?php echo nl2br(htmlspecialchars($message['pesan'])); ?>
                                        </div>
                                        <div class="message-actions">
                                            <button class="btn-message-action" onclick="copyMessage('<?php echo addslashes($message['pesan']); ?>')" title="Salin">
                                                <i class="far fa-copy"></i>
                                            </button>
                                            <button class="btn-message-action" onclick="replyToMessage(<?php echo $message['id']; ?>)" title="Balas">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="typing-indicator" id="typingIndicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span>Pasien sedang mengetik...</span>
                    </div>
                </div>

                <!-- Input Area -->
                <?php if ($sesi['status'] == 'aktif'): ?>
                <div class="chat-input-area">
                    <form id="chatForm" class="chat-form">
                        <input type="hidden" name="id_sesi" value="<?php echo $id_sesi; ?>">
                        
                        <div class="input-wrapper">
                            <div class="message-input-group">
                                <textarea 
                                    name="message" 
                                    id="messageInput" 
                                    placeholder="Ketik pesan Anda di sini..." 
                                    rows="1"
                                    autocomplete="off"
                                    oninput="autoResize(this)"></textarea>
                                <div class="input-actions">
                                    <button type="submit" class="btn-send">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Kirim</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="quick-actions">
                            <div class="quick-actions-header">
                                <i class="fas fa-bolt"></i>
                                <span>Pesan Cepat</span>
                            </div>
                            <div class="quick-buttons">
                                <button type="button" class="btn-quick" data-text="Bagaimana perasaan Anda hari ini?">
                                    <i class="fas fa-smile"></i> Perasaan
                                </button>
                                <button type="button" class="btn-quick" data-text="Bisakah Anda menjelaskan lebih detail?">
                                    <i class="fas fa-question-circle"></i> Detail
                                </button>
                                <button type="button" class="btn-quick" data-text="Apakah ada yang mengganggu tidur Anda?">
                                    <i class="fas fa-bed"></i> Tidur
                                </button>
                                <button type="button" class="btn-quick" data-text="Bagaimana nafsu makan Anda?">
                                    <i class="fas fa-utensils"></i> Makan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="chat-disabled">
                    <div class="disabled-content">
                        <i class="fas fa-comment-slash"></i>
                        <h3>Chat tidak aktif</h3>
                        <p>Sesi sudah <?php echo $sesi['status']; ?>. Tidak dapat mengirim pesan baru.</p>
                        <div class="disabled-actions">
                            <a href="chat.php" class="btn-back-to-list">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Daftar Chat
                            </a>
                            <a href="notes.php?id_sesi=<?php echo $id_sesi; ?>" class="btn-notes">
                                <i class="fas fa-file-medical"></i>
                                Lihat Catatan
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="notificationToast">
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Pesan berhasil dikirim</span>
        </div>
    </div>

    <script src="../../assets/js/chat_session.js"></script>
    <script>
        // expose session id to the external JS
        window.SESSION_ID = <?php echo json_encode($id_sesi); ?>;
        window.GET_MESSAGES_URL = '../../get_messages.php';
        // For doctor view we want to fetch messages coming from pasien
        window.ONLY_FROM = 'pasien';
    </script>
    <script>
        // No realtime socket configured; using polling only
    </script>
</body>
</html>