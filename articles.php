<?php
include 'includes/db.php';
include 'includes/functions.php';

// Get articles (simplified - in real app, this would be from database)
$articles = [
    [
        'title' => 'Pentingnya Kesehatan Mental',
        'content' => 'Kesehatan mental adalah aspek penting dalam kehidupan sehari-hari...',
        'date' => '2023-10-01'
    ],
    [
        'title' => 'Cara Mengatasi Stres',
        'content' => 'Stres adalah respons alami tubuh terhadap tekanan...',
        'date' => '2023-10-05'
    ],
    [
        'title' => 'Manfaat Konsultasi Online',
        'content' => 'Konsultasi kesehatan mental secara online memberikan kemudahan...',
        'date' => '2023-10-10'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Mentara</title>
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
            <h1>Artikel Kesehatan Mental</h1>
            <p>Temukan informasi berguna tentang kesehatan mental dan kesejahteraan psikologis.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <?php foreach ($articles as $article): ?>
                    <div style="background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($article['content'], 0, 100)) . '...'; ?></p>
                        <small><?php echo date('d M Y', strtotime($article['date'])); ?></small>
                        <br><br>
                        <a href="#" class="btn">Baca Selengkapnya</a>
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
