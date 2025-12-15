<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = $_GET['id_sesi'] ?? null;

// Jika membuat sesi baru
if (isset($_GET['new']) && $_GET['new'] == 'true') {
    // Buat sesi baru
    $stmt = $pdo->prepare("
        INSERT INTO sesi_chat (id_dokter, nama_pasien, status, dibuat_pada, diperbarui_pada) 
        VALUES (?, 'Pasien Baru', 'aktif', NOW(), NOW())
    ");
    $stmt->execute([$id_pengguna]);
    $id_sesi = $pdo->lastInsertId();
    
    // Redirect ke chat session dengan ID baru
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
    header('Location: chat.php');
    exit;
}

// Tangani update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $status = bersihkan($_POST['status']);
    $stmt = $pdo->prepare("UPDATE sesi_chat SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id_sesi]);
    
    // Jika selesai, tambahkan pesan otomatis
    if ($status == 'selesai') {
        $stmt = $pdo->prepare("
            INSERT INTO pesan (id_sesi, pengirim, id_pengirim, pesan) 
            VALUES (?, 'sistem', ?, 'Sesi konseling telah selesai. Terima kasih.')
        ");
        $stmt->execute([$id_sesi, $id_pengguna]);
    }
    
    header("Location: chat_session.php?id_sesi=$id_sesi");
    exit;
}

// Dapatkan semua pesan
$stmt = $pdo->prepare("
    SELECT p.*, 
           CASE 
               WHEN p.pengirim = 'dokter' THEN d.nama 
               WHEN p.pengirim = 'sistem' THEN 'Sistem'
               ELSE 'Pasien' 
           END as nama_pengirim
    FROM pesan p
    LEFT JOIN pengguna d ON (p.pengirim = 'dokter' AND d.id = p.id_pengirim)
    WHERE p.id_sesi = ? 
    ORDER BY p.dibuat_pada ASC
");
$stmt->execute([$id_sesi]);
$messages = $stmt->fetchAll();

// Dapatkan info pasien (jika ada)
$stmt = $pdo->prepare("SELECT nama, email, telepon FROM pasien WHERE id_sesi = ?");
$stmt->execute([$id_sesi]);
$pasien = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Session - Dokter Mentara</title>
    <link rel="stylesheet" href="../assets/css/doctor.css">
    <link rel="stylesheet" href="../assets/css/chat_session.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content chat-session-page">
            <!-- Header -->
            <div class="session-header">
                <div class="header-left">
                    <a href="chat.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="patient-info">
                        <h2><?php echo htmlspecialchars($sesi['nama_pasien']); ?></h2>
                        <div class="patient-meta">
                            <span class="status-badge <?php echo $sesi['status']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($sesi['status']); ?>
                            </span>
                            <?php if ($pasien): ?>
                                <span class="patient-contact">
                                    <i class="fas fa-phone"></i> <?php echo $pasien['telepon']; ?>
                                </span>
                                <span class="patient-contact">
                                    <i class="fas fa-envelope"></i> <?php echo $pasien['email']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="header-right">
                    <form method="POST" class="status-form">
                        <select name="status" id="statusSelect" class="status-select">
                            <option value="aktif" <?php echo $sesi['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?php echo $sesi['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="dibatalkan" <?php echo $sesi['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                        <button type="submit" class="btn-status-update">Update</button>
                    </form>
                    
                    <div class="session-actions">
                        <button class="btn-action" onclick="printSession()">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="btn-action" onclick="exportSession()">
                            <i class="fas fa-download"></i>
                        </button>
                        <a href="notes.php?id_sesi=<?php echo $id_sesi; ?>" class="btn-action">
                            <i class="fas fa-file-medical"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="chat-container">
                <!-- Messages Area -->
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo $message['pengirim']; ?>">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($message['nama_pengirim']); ?></strong>
                                <span class="message-time">
                                    <?php echo date('H:i', strtotime($message['dibuat_pada'])); ?>
                                </span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['pesan'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Input Area -->
                <?php if ($sesi['status'] == 'aktif'): ?>
                <div class="chat-input-area">
                    <form id="chatForm" class="chat-form">
                        <input type="hidden" name="id_sesi" value="<?php echo $id_sesi; ?>">
                        <div class="input-group">
                            <input type="text" 
                                   name="message" 
                                   id="messageInput" 
                                   placeholder="Ketik pesan Anda di sini..." 
                                   autocomplete="off">
                            <button type="submit" class="btn-send">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="quick-actions">
                            <button type="button" class="btn-quick" data-text="Bagaimana perasaan Anda hari ini?">
                                <i class="fas fa-smile"></i> Perasaan
                            </button>
                            <button type="button" class="btn-quick" data-text="Bisakah Anda menjelaskan lebih detail?">
                                <i class="fas fa-question"></i> Detail
                            </button>
                            <button type="button" class="btn-quick" data-text="Apakah ada hal lain yang ingin dibicarakan?">
                                <i class="fas fa-plus"></i> Topik Lain
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="chat-disabled">
                    <p>Chat tidak aktif karena sesi sudah <?php echo $sesi['status']; ?>.</p>
                    <a href="chat.php" class="btn-back-to-list">Kembali ke Daftar Chat</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Patient Summary -->
            <div class="patient-summary">
                <h3><i class="fas fa-user-injured"></i> Ringkasan Pasien</h3>
                <div class="summary-content">
                    <div class="summary-item">
                        <label>Keluhan Utama:</label>
                        <p><?php echo htmlspecialchars($sesi['keluhan'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="summary-item">
                        <label>Durasi Sesi:</label>
                        <p><?php echo waktu_relatif($sesi['dibuat_pada']); ?></p>
                    </div>
                    <div class="summary-item">
                        <label>Jumlah Pesan:</label>
                        <p><?php echo count($messages); ?> pesan</p>
                    </div>
                    <div class="summary-item">
                        <label>Terakhir Aktif:</label>
                        <p><?php echo waktu_relatif($sesi['diperbarui_pada']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto scroll to bottom
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Load messages
        function loadMessages() {
            fetch(`get_messages.php?id_sesi=<?php echo $id_sesi; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        const chatMessages = document.getElementById('chatMessages');
                        const currentCount = chatMessages.querySelectorAll('.message').length;
                        
                        if (data.messages.length > currentCount) {
                            chatMessages.innerHTML = '';
                            data.messages.forEach(message => {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = 'message ' + message.pengirim;
                                messageDiv.innerHTML = `
                                    <div class="message-header">
                                        <strong>${message.nama_pengirim}</strong>
                                        <span class="message-time">
                                            ${new Date(message.dibuat_pada).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        </span>
                                    </div>
                                    <div class="message-content">
                                        ${message.pesan.replace(/\n/g, '<br>')}
                                    </div>
                                `;
                                chatMessages.appendChild(messageDiv);
                            });
                            scrollToBottom();
                        }
                    }
                })
                .catch(error => console.error('Error loading messages:', error));
        }

        // Send message
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageInput = document.getElementById('messageInput');
            
            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                }
            })
            .catch(error => console.error('Error sending message:', error));
        });

        // Quick actions
        document.querySelectorAll('.btn-quick').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('messageInput').value = this.dataset.text;
                document.getElementById('messageInput').focus();
            });
        });

        // Auto refresh every 3 seconds
        setInterval(loadMessages, 3000);

        // Initial load
        scrollToBottom();
        loadMessages();

        // Print function
        function printSession() {
            window.print();
        }

        // Export function
        function exportSession() {
            window.open(`export_session.php?id_sesi=<?php echo $id_sesi; ?>`, '_blank');
        }

        // Auto focus on input
        document.getElementById('messageInput')?.focus();

        // Enter to send (Ctrl+Enter for new line)
        document.getElementById('messageInput')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
                e.preventDefault();
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>