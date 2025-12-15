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

// Tangani pengiriman pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $pesan = bersihkan($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO pesan (id_sesi, pengirim, pesan) VALUES (?, 'pasien', ?)");
    $stmt->execute([$id_sesi, $pesan]);
}

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
                <div class="chat-messages" id="chat-messages">
                    <?php foreach ($pesan_list as $pesan): ?>
                        <div class="message <?php echo $pesan['pengirim']; ?>">
                            <strong><?php echo ucfirst($pesan['pengirim']); ?>:</strong>
                            <?php echo htmlspecialchars($pesan['pesan']); ?>
                            <small><?php echo date('H:i', strtotime($pesan['dibuat_pada'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="chat-input" action="chat.php" method="post">
                    <input type="text" name="message" placeholder="Ketik pesan Anda..." required>
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
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Refresh messages every 5 seconds
        setInterval(function() {
            fetch('get_messages.php?id_sesi=<?php echo $id_sesi; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.messages.length > 0) {
                        chatMessages.innerHTML = '';
                        data.messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'message ' + message.pengirim;
                            messageDiv.innerHTML = '<strong>' + message.pengirim.charAt(0).toUpperCase() + message.pengirim.slice(1) + ':</strong> ' + message.pesan + '<small>' + new Date(message.dibuat_pada).toLocaleTimeString() + '</small>';
                            chatMessages.appendChild(messageDiv);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                });
        }, 5000);
    </script>
</body>
</html>
