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
    <?php $style_version = filemtime(__DIR__ . '/assets/css/style.css'); ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $style_version; ?>">
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

    <main class="rating-page">
        <div class="container">
            <?php if (isset($success)): ?>
                <div class="rating-card rating-success">
                    <div class="rating-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2><?php echo $success; ?></h2>
                    <p>Terima kasih sudah membantu kami meningkatkan layanan.</p>
                    <a href="index.php" class="btn">Kembali ke Beranda</a>
                </div>
            <?php else: ?>
                <div class="rating-card">
                    <div class="rating-header">
                        <div class="rating-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h1 class="rating-title">Beri Rating Dokter</h1>
                            <p class="rating-subtitle">Bagaimana pengalaman konsultasi Anda?</p>
                        </div>
                    </div>

                    <form action="rating.php" method="post" class="rating-form">
                        <div class="form-group">
                            <label>Rating (1-5 bintang):</label>
                            <div class="rating-stars">
                                <input type="radio" id="rating-5" name="rating" value="5" required>
                                <label for="rating-5" title="5 - Sangat Puas"><i class="fas fa-star"></i></label>

                                <input type="radio" id="rating-4" name="rating" value="4">
                                <label for="rating-4" title="4 - Puas"><i class="fas fa-star"></i></label>

                                <input type="radio" id="rating-3" name="rating" value="3">
                                <label for="rating-3" title="3 - Cukup"><i class="fas fa-star"></i></label>

                                <input type="radio" id="rating-2" name="rating" value="2">
                                <label for="rating-2" title="2 - Kurang Puas"><i class="fas fa-star"></i></label>

                                <input type="radio" id="rating-1" name="rating" value="1">
                                <label for="rating-1" title="1 - Tidak Puas"><i class="fas fa-star"></i></label>
                            </div>
                            <small class="rating-hint">Pilih 1 sampai 5 bintang.</small>
                        </div>

                        <div class="form-group">
                            <label for="comment">Komentar (opsional):</label>
                            <textarea id="comment" name="comment" rows="4" placeholder="Berikan komentar tentang pelayanan dokter..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-rating">Kirim Rating</button>
                    </form>
                </div>
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
