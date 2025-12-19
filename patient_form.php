<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = bersihkan($_POST['nama']);
    $usia = (int)$_POST['usia'];
    $keluhan = bersihkan($_POST['keluhan']);
    $dokter_id = (isset($_POST['dokter_id']) && $_POST['dokter_id'] !== '') ? (int)$_POST['dokter_id'] : null;
    // Jika session sudah punya id_sesi_chat, kembali ke chat tanpa membuat duplikat
    if (isset($_SESSION['id_sesi_chat']) && !empty($_SESSION['id_sesi_chat'])) {
        $existingId = (int)$_SESSION['id_sesi_chat'];
        $check = $pdo->prepare("SELECT id FROM sesi_chat WHERE id = ? LIMIT 1");
        $check->execute([$existingId]);
        if ($check->fetch()) {
            // respond for both fetch and normal form submit
            header('Location: chat.php');
            echo json_encode(['redirect' => 'chat.php']);
            exit;
        } else {
            // session id invalid, remove it and continue
            unset($_SESSION['id_sesi_chat']);
        }
    }

    // Cek apakah ada sesi serupa baru-baru ini (hindari double submit)
    $dupCheck = $pdo->prepare("SELECT id FROM sesi_chat WHERE nama_pasien = ? AND usia_pasien = ? AND keluhan = ? AND COALESCE(id_dokter, 0) <=> COALESCE(?,0) AND dibuat_pada >= (NOW() - INTERVAL 30 SECOND) LIMIT 1");
    $dupCheck->execute([$nama, $usia, $keluhan, $dokter_id]);
    $found = $dupCheck->fetch();
    if ($found) {
        $_SESSION['id_sesi_chat'] = $found['id'];
        $_SESSION['nama_pasien'] = $nama;
        header('Location: chat.php');
        echo json_encode(['redirect' => 'chat.php']);
        exit;
    }

    // Buat sesi chat (simpan id dokter jika ada)
    $stmt = $pdo->prepare("INSERT INTO sesi_chat (nama_pasien, usia_pasien, keluhan, id_dokter, status, dibuat_pada, diperbarui_pada) VALUES (?, ?, ?, ?, 'aktif', NOW(), NOW())");
    $stmt->execute([$nama, $usia, $keluhan, $dokter_id]);
    $id_sesi = $pdo->lastInsertId();

    $_SESSION['id_sesi_chat'] = $id_sesi;
    $_SESSION['nama_pasien'] = $nama;

    header('Location: chat.php');
    echo json_encode(['redirect' => 'chat.php']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mulai Konsultasi - Mentara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">Mentara</div>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="counselors.php">Konselor</a></li>
                <li><a href="patient_form.php">Chat</a></li>
                <li><a href="articles.php">Artikel</a></li>
                <li><a href="help.php">Bantuan</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>Mulai Konsultasi</h1>
            <p>Silakan isi formulir berikut untuk memulai sesi konsultasi dengan dokter.</p>

            <form action="patient_form.php" method="post" style="max-width: 500px; margin: 0 auto;">
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>

                <div class="form-group">
                    <label for="usia">Usia:</label>
                    <input type="number" id="usia" name="usia" min="1" max="120" required>
                </div>

                <div class="form-group">
                    <label for="keluhan">Keluhan:</label>
                    <textarea id="keluhan" name="keluhan" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn">Mulai Chat</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Mentara. Semua hak dilindungi.</p>
        </div>
    </footer>
</body>
</html>
