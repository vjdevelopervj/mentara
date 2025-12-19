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
        <div class="container">
            <div class="chat-container">
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

                <form class="chat-input" id="patientChatForm">
                    <input type="text" name="message" id="patientMessageInput" placeholder="Ketik pesan Anda..." required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="rating.php" class="btn">Selesai Konsultasi & Beri Rating</a>
            </div>
        </div>
    </main>

    <script>

        // Auto scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

        function createMessageElement(message) {
            const wrapper = document.createElement('div');
            wrapper.className = 'message ' + message.pengirim;
            wrapper.setAttribute('data-id', message.id || '');
            wrapper.setAttribute('data-time', message.dibuat_pada || '');

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            const avatarInner = document.createElement('div');
            if (message.pengirim === 'dokter') {
                avatarInner.className = 'avatar-doctor';
                avatarInner.innerHTML = '<i class="fas fa-user-md"></i>';
            } else if (message.pengirim === 'sistem') {
                avatarInner.className = 'avatar-system';
                avatarInner.innerHTML = '<i class="fas fa-robot"></i>';
            } else {
                avatarInner.className = 'avatar-patient';
                avatarInner.innerHTML = '<i class="fas fa-user"></i>';
            }
            avatar.appendChild(avatarInner);

            const contentWrap = document.createElement('div');
            contentWrap.className = 'message-content-wrapper';

            const header = document.createElement('div');
            header.className = 'message-header';
            header.innerHTML = '<div class="sender-info"><strong class="sender-name">' + (message.nama_pengirim || (message.pengirim.charAt(0).toUpperCase() + message.pengirim.slice(1))) + '</strong> <span class="sender-role ' + message.pengirim + '">' + (message.pengirim === 'dokter' ? 'Dokter' : (message.pengirim === 'sistem' ? 'Sistem' : 'Pasien')) + '</span></div>';

            const meta = document.createElement('div');
            meta.className = 'message-meta';
            meta.innerHTML = '<span class="message-time"><i class="far fa-clock"></i> ' + (message.dibuat_pada ? new Date(message.dibuat_pada).toLocaleTimeString() : '') + '</span>';

            header.appendChild(meta);

            const body = document.createElement('div');
            body.className = 'message-body';
            body.innerHTML = (message.pesan || '').replace(/\n/g, '<br>');

            const actions = document.createElement('div');
            actions.className = 'message-actions';
            const copyBtn = document.createElement('button');
            copyBtn.className = 'btn-message-action';
            copyBtn.title = 'Salin';
            copyBtn.innerHTML = '<i class="far fa-copy"></i>';
            copyBtn.addEventListener('click', function(){ copyMessage(message.pesan || ''); });
            actions.appendChild(copyBtn);

            contentWrap.appendChild(header);
            contentWrap.appendChild(body);
            contentWrap.appendChild(actions);

            wrapper.appendChild(avatar);
            wrapper.appendChild(contentWrap);
            return wrapper;
        }

        // Long-polling: get new messages without page refresh
        let lastMessageId = 0;
        // Initialize lastMessageId from existing DOM messages
        document.querySelectorAll('#chatMessages .message[data-id]').forEach(m => {
            const id = parseInt(m.getAttribute('data-id')) || 0;
            if (id > lastMessageId) lastMessageId = id;
        });

        function startLongPoll() {
            const urlBase = 'get_messages_longpoll.php?id_sesi=<?php echo $id_sesi; ?>';
            const url = urlBase + '&since_id=' + lastMessageId + '&only_from=dokter';
            fetch(url, { cache: 'no-store' })
                .then(r => r.json())
                .then(data => {
                    if (data && data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            chatMessages.appendChild(createMessageElement(message));
                            if (message.id && message.id > lastMessageId) lastMessageId = message.id;
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                    // reconnect immediately
                    setTimeout(startLongPoll, 50);
                })
                .catch(err => {
                    console.error('Long-poll error', err);
                    setTimeout(startLongPoll, 2000);
                });
        }

        // Start long-poll after page load
        setTimeout(startLongPoll, 200);

        // AJAX send for patient
        document.getElementById('patientChatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('patientMessageInput');
            const text = input.value.trim();
            if (!text) return;

            fetch('send_message.php', {
                method: 'POST',
                body: new URLSearchParams({ message: text })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    input.value = '';
                    if (res.messages && Array.isArray(res.messages)) {
                        chatMessages.innerHTML = '';
                        res.messages.forEach(message => chatMessages.appendChild(createMessageElement(message)));
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    } else {
                        // fallback refresh
                        fetch('get_messages.php?id_sesi=<?php echo $id_sesi; ?>&only_from=dokter')
                            .then(response => response.json())
                            .then(data => {
                                chatMessages.innerHTML = '';
                                data.messages.forEach(message => chatMessages.appendChild(createMessageElement(message)));
                                chatMessages.scrollTop = chatMessages.scrollHeight;
                            });
                    }
                } else {
                    console.error(res.error || 'Failed to send');
                }
            })
            .catch(err => console.error('Send error', err));
        });

        // No realtime socket configured; using polling only
    </script>
</body>
</html>
