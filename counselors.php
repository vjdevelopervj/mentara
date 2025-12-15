<?php
include 'includes/db.php';
include 'includes/functions.php';

// Get active doctors
$stmt = $pdo->query("SELECT * FROM pengguna WHERE peran = 'dokter' AND aktif = 1 ORDER BY nama");
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta nama="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Konselor - Mentara</title>
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
            <h1>Daftar Konselor</h1>
            <p>Konsultasikan kesehatan mental Anda dengan dokter ahli yang tersedia.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <?php foreach ($doctors as $doctor): ?>
                    <div style="background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <h3><?php echo htmlspecialchars($doctor['nama']); ?></h3>
                        <p><strong>Spesialisasi:</strong> <?php echo htmlspecialchars($doctor['spesialisasi']); ?></p>
                        <p><strong>Jadwal:</strong> <?php echo htmlspecialchars($doctor['jadwal']); ?></p>
                        <a href="patient_form.php" class="btn">Mulai Konsultasi</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Mentara. Semua hak dilindungi.</p>
        </div>
    </footer>
</body>
</html>
