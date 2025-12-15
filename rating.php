<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['id_sesi_chat'])) {
    header('Location: index.php');
    exit;
}

$id_sesi = $_SESSION['id_sesi_chat'];

// Dapatkan detail sesi
$stmt = $pdo->prepare("SELECT * FROM sesi_chat WHERE id = ?");
$stmt->execute([$id_sesi]);
$sesi = $stmt->fetch();

if (!$sesi) {
    header('Location: index.php');
    exit;
}

// Tangani pengiriman rating
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $komentar = bersihkan($_POST['comment']);

    $stmt = $pdo->prepare("UPDATE sesi_chat SET rating = ?, komentar_rating = ?, status = 'selesai' WHERE id = ?");
    $stmt->execute([$rating, $komentar, $id_sesi]);

    // Hapus sesi
    unset($_SESSION['id_sesi_chat']);
    unset($_SESSION['nama_pasien']);

    $success = "Terima kasih atas rating Anda! Sesi konsultasi telah selesai.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Rating - Mentara</title>
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
            <?php if (isset($success)): ?>
                <div style="text-align: center; background-color: #d4edda; color: #155724; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h2><?php echo $success; ?></h2>
                    <a href="index.php" class="btn">Kembali ke Beranda</a>
                </div>
            <?php else: ?>
                <h1>Beri Rating Dokter</h1>
                <p>Bagaimana pengalaman konsultasi Anda dengan dokter?</p>

                <form action="rating.php" method="post" style="max-width: 500px; margin: 0 auto;">
                    <div class="form-group">
                        <label for="rating">Rating (1-5 bintang):</label>
                        <select id="rating" name="rating" required>
                            <option value="">Pilih rating</option>
                            <option value="5">5 - Sangat Puas</option>
                            <option value="4">4 - Puas</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang Puas</option>
                            <option value="1">1 - Tidak Puas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="comment">Komentar (opsional):</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Berikan komentar tentang pelayanan dokter..."></textarea>
                    </div>

                    <button type="submit" class="btn">Kirim Rating</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Mentara. Semua hak dilindungi.</p>
        </div>
    </footer>
</body>
</html>
