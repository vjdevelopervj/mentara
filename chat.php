<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['id_sesi_chat'])) {
    header('Location: patient_form.php');
    exit;
}

$id_sesi = $_SESSION['id_sesi_chat'];
$nama_pasien = $_SESSION['nama_pasien'];

// (Messages are sent via AJAX to send_message.php)

// Dapatkan pesan
$stmt = $pdo->prepare("SELECT * FROM pesan WHERE id_sesi = ? ORDER BY dibuat_pada ASC");
$stmt->execute([$id_sesi]);
$pesan_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Konsultasi - Mentara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="navbarchat.css">
    <link rel="stylesheet" href="assets/css/chat_session.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">
                <img class="logo-white" src="assets/images/mentara-logo.png" alt="logo">
                <img class="logo-colored" src="assets/images/mentara-logo-colored.png" alt="logo-colored">
                Mentara
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="#counselors">Konselor</a></li>
                <li><a href="#chat">Chat</a></li>
                <li><a href="#articles">Artikel</a></li>
                <li><a href="#help">Bantuan</a></li>
                <li><a href="login.php" class="btn-login">Login Dokter/Admin</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container patient-chat">
            <div class="chat-container">
                <div class="chat-messages-wrapper">
                    <div class="messages-header">
                        <h3><i class="fas fa-comments"></i> Percakapan</h3>
                        <div class="messages-info">
                            <span class="unread-count"><?php echo count($pesan_list); ?> pesan</span>
                        </div>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php foreach ($pesan_list as $pesan): ?>
                            <div class="message <?php echo $pesan['pengirim']; ?>" data-id="<?php echo $pesan['id']; ?>" data-time="<?php echo $pesan['dibuat_pada']; ?>">
                                <div class="message-avatar">
                                    <?php if ($pesan['pengirim'] == 'dokter'): ?>
                                        <div class="avatar-doctor"><i class="fas fa-user-md"></i></div>
                                    <?php elseif ($pesan['pengirim'] == 'sistem'): ?>
                                        <div class="avatar-system"><i class="fas fa-robot"></i></div>
                                    <?php else: ?>
                                        <div class="avatar-patient"><i class="fas fa-user"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="message-content-wrapper">
                                    <div class="message-header">
                                        <div class="sender-info">
                                            <strong class="sender-name"><?php echo htmlspecialchars($pesan['nama_pengirim'] ?? ucfirst($pesan['pengirim'])); ?></strong>
                                            <span class="sender-role <?php echo $pesan['pengirim']; ?>"><?php echo $pesan['pengirim'] == 'dokter' ? 'Dokter' : ($pesan['pengirim'] == 'sistem' ? 'Sistem' : 'Pasien'); ?></span>
                                        </div>
                                        <div class="message-meta">
                                            <span class="message-time"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($pesan['dibuat_pada'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="message-body"><?php echo nl2br(htmlspecialchars($pesan['pesan'])); ?></div>
                                    <div class="message-actions">
                                        <button class="btn-message-action" onclick="copyMessage('<?php echo addslashes($pesan['pesan']); ?>')" title="Salin"><i class="far fa-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="typing-indicator" id="typingIndicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span>Dokter sedang mengetik...</span>
                    </div>
                </div>

                <div class="chat-input-area">
                    <form class="chat-form" id="chatForm">
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
                    </form>
                </div>
            </div>

            <div class="chat-footer-actions">
                <a href="rating.php" class="btn">Selesai Konsultasi & Beri Rating</a>
            </div>
        </div>
    </main>

    <script src="assets/js/chat_session.js"></script>
    <script>
        window.SESSION_ID = <?php echo json_encode($id_sesi); ?>;
        window.GET_MESSAGES_URL = 'get_messages.php';
        window.LONGPOLL_ONLY_FROM = 'dokter';
        window.TYPING_URL = 'typing.php';
        window.LOCAL_ROLE = 'pasien';
        window.REMOTE_ROLE = 'dokter';
    </script>
</body>
</html>
