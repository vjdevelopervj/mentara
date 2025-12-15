<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

// Dapatkan daftar dokter untuk halaman konselor
try {
    $stmt = $pdo->query("SELECT * FROM pengguna WHERE peran = 'dokter' AND aktif = 1 ORDER BY nama");
    $dokter_list = $stmt->fetchAll();
} catch (Exception $e) {
    $dokter_list = [];
}

// Data artikel kesehatan mental
$artikel_list = [
    [
        'id' => 1,
        'judul' => 'Mengatasi Stres Harian',
        'kategori' => 'Tips Kesehatan',
        'deskripsi' => 'Stres adalah respons alami tubuh terhadap tantangan. Pelajari cara mengelolanya dengan teknik relaksasi dan olahraga.',
        'isi' => 'Stres harian dapat mempengaruhi kesehatan mental dan fisik Anda. Berikut beberapa cara mengatasi stres: 1. Olahraga teratur, 2. Meditasi dan mindfulness, 3. Berbicara dengan orang terpercaya, 4. Istirahat cukup, 5. Aktivitas hobi yang menyenangkan.'
    ],
    [
        'id' => 2,
        'judul' => 'Pentingnya Kesehatan Mental',
        'kategori' => 'Edukasi',
        'deskripsi' => 'Kesehatan mental sama pentingnya dengan kesehatan fisik. Jaga keseimbangan hidup Anda.',
        'isi' => 'Kesehatan mental yang baik memungkinkan kami untuk menyadari potensi penuh kami, mengatasi stres hidup, bekerja produktif, dan berkontribusi pada komunitas kami.'
    ],
    [
        'id' => 3,
        'judul' => 'Cara Berkomunikasi yang Sehat',
        'kategori' => 'Self Healing',
        'deskripsi' => 'Komunikasi yang baik dapat meningkatkan hubungan interpersonal dan mengurangi konflik.',
        'isi' => 'Komunikasi yang efektif adalah kunci dari hubungan yang sehat. Dengarkan dengan penuh perhatian, ekspresikan perasaan dengan jujur, dan hormati perspektif orang lain.'
    ],
    [
        'id' => 4,
        'judul' => 'Self Healing untuk Pemula',
        'kategori' => 'Self Healing',
        'deskripsi' => 'Panduan lengkap untuk memulai perjalanan self healing Anda sendiri.',
        'isi' => 'Self healing adalah proses penyembuhan diri dari dalam. Mulai dengan menerima diri sendiri, bermaaf pada diri sendiri, dan menjalin hubungan yang positif dengan orang-orang di sekitar Anda.'
    ],
    [
        'id' => 5,
        'judul' => 'Depresi: Mengenali Gejala dan Cara Mengatasi',
        'kategori' => 'Edukasi',
        'deskripsi' => 'Pelajari gejala-gejala depresi dan strategi mengatasinya dengan efektif.',
        'isi' => 'Depresi adalah kondisi medis yang serius. Gejala umum meliputi kesedihan yang berkepanjangan, kehilangan minat pada aktivitas, perubahan nafsu makan, dan gangguan tidur. Jangan ragu untuk mencari bantuan profesional.'
    ]
];

// Data FAQ
$faq_list = [
    [
        'pertanyaan' => 'Apakah konsultasi di Mentara gratis?',
        'jawaban' => 'Ya, sesi konsultasi pertama Anda gratis. Kami percaya bahwa setiap orang berhak mendapatkan akses ke layanan kesehatan mental berkualitas.'
    ],
    [
        'pertanyaan' => 'Apakah data saya aman?',
        'jawaban' => 'Keamanan data Anda adalah prioritas utama kami. Kami menggunakan enkripsi tingkat bank dan mematuhi standar privasi internasional.'
    ],
    [
        'pertanyaan' => 'Berapa lama waktu tunggu untuk chat dengan konselor?',
        'jawaban' => 'Biasanya konselor kami siap dalam waktu kurang dari 5 menit. Namun, waktu tunggu dapat bervariasi tergantung ketersediaan konselor.'
    ],
    [
        'pertanyaan' => 'Apakah saya bisa memilih konselor yang spesifik?',
        'jawaban' => 'Ya, Anda dapat melihat profil semua konselor kami dan memilih yang paling sesuai dengan kebutuhan Anda.'
    ],
    [
        'pertanyaan' => 'Apa yang harus saya lakukan jika mengalami krisis?',
        'jawaban' => 'Jika Anda mengalami krisis atau situasi darurat, silakan hubungi hotline kami atau layanan darurat setempat segera.'
    ]
];

// Data kontak darurat
$kontak_darurat = [
    ['nama' => 'Hotline Kesehatan Mental 24/7', 'nomor' => '1500-929', 'deskripsi' => 'Layanan darurat kesehatan mental'],
    ['nama' => 'Tim Mentara', 'nomor' => '+62-812-3456-7890', 'deskripsi' => 'Hubungi tim kami'],
    ['nama' => 'Email Support', 'email' => 'support@mentara.com', 'deskripsi' => 'Kirim email ke support kami'],
    ['nama' => 'WhatsApp', 'nomor' => '+62-812-3456-7890', 'deskripsi' => 'Chat via WhatsApp']
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentara - Konsultasi Kesehatan Mental</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<style>
    /* Chat Section Styles */
    #chat {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    /* Doctor Selection */
    .doctor-selection {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 3rem;
    }

    .selection-subtitle {
        color: #666;
        margin-bottom: 1.5rem;
        font-size: 1rem;
    }

    .doctor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .doctor-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .doctor-card:hover {
        border-color: #4361ee;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(67, 97, 238, 0.15);
    }

    .doctor-card.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(67, 97, 238, 0.02) 100%);
    }

    .doctor-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f1f1;
    }

    .doctor-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .doctor-basic-info h4 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.2rem;
    }

    .doctor-specialty {
        display: inline-block;
        background: rgba(67, 97, 238, 0.1);
        color: #4361ee;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .doctor-card-body {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .doctor-info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #666;
        font-size: 0.9rem;
    }

    .doctor-info-item i {
        color: #4361ee;
        width: 20px;
    }

    .doctor-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-select-dokter {
        background: #4361ee;
        color: white;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-select-dokter:hover {
        background: #3a56d4;
        transform: translateY(-2px);
    }

    .btn-select-dokter.selected {
        background: #28a745;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge.online {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .status-badge.offline {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .status-badge i {
        font-size: 0.6rem;
    }

    .no-doctors {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .no-doctors i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    /* Consultation Form */
    .consultation-form-wrapper {
        background: white;
        padding: 2.5rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .form-header {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f1f1f1;
    }

    .form-header h3 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .selected-doctor-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(67, 97, 238, 0.05);
        padding: 1rem 1.5rem;
        border-radius: 10px;
        border: 1px solid rgba(67, 97, 238, 0.1);
    }

    .selected-doctor-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #2c3e50;
    }

    .selected-doctor-info i {
        font-size: 1.5rem;
        color: #4361ee;
    }

    .btn-change-doctor {
        background: white;
        border: 1px solid #4361ee;
        color: #4361ee;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-change-doctor:hover {
        background: #4361ee;
        color: white;
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        color: #2c3e50;
        font-weight: 500;
    }

    .form-group label i {
        color: #4361ee;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    /* Form Actions */
    .form-actions {
        margin-top: 2rem;
    }

    .btn-start-chat {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .btn-start-chat:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-start-chat:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Alert */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }

    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .doctor-grid {
            grid-template-columns: 1fr;
        }

        .selected-doctor-display {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }
</style>

<body>
    <header>
        <nav class="container">
            <div class="logo">
                <img class="logo-white" src="assets/images/mentara-logo.png" alt="logo">
                <img class="logo-colored" src="assets/images/mentara-logo-colored.png" alt="logo-colored">
                Mentara
            </div>
            <ul class="nav-links">
                <li><a href="#home">Beranda</a></li>
                <li><a href="#counselors">Konselor</a></li>
                <li><a href="#chat">Chat</a></li>
                <li><a href="#articles">Artikel</a></li>
                <li><a href="#help">Bantuan</a></li>
                <li><a href="login.php" class="btn-login">Login Dokter/Admin</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Beranda -->
        <section id="home" class="landing-section">
            <div class="rectangle"></div>
            <!-- <img src="assets/images/doctor.png" alt="hero-doctor"> -->

            <div class="container">
                <h1>Selamat Datang di Mentara</h1>
                <p>Konsultasi kesehatan mental yang mudah dan anonim. Mulai percakapan dengan dokter ahli kapan saja.</p>
                <a href="#chat" class="btn scroll-link">Mulai Konsultasi</a>

                <h2>Kenapa Memilih Mentara?</h2>
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Anonim</h3>
                        <p>Konsultasi tanpa perlu membuat akun</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-clock"></i>
                        <h3>24/7</h3>
                        <p>Tersedia kapan saja</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-user-md"></i>
                        <h3>Dokter Ahli</h3>
                        <p>Dokter kesehatan mental berpengalaman</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Konselor -->
        <section id="counselors" class="landing-section">
            <div class="container">
                <h1>Daftar Konselor</h1>
                <p>Pilih dokter yang sesuai dengan kebutuhan Anda.</p>
                <div class="doctor-grid">
                    <?php foreach ($dokter_list as $dokter): ?>
                        <div class="doctor-card">
                            <!-- <div class="doctor-image">
                                <img src="assets/images/doctor.png" alt="Doctor">
                            </div> -->
                            <div class="doctor-info">
                                <h3><?php echo htmlspecialchars($dokter['nama']); ?></h3>
                                <p><i class="fas fa-stethoscope"></i> <strong>Spesialisasi:</strong> <?php echo htmlspecialchars($dokter['spesialisasi']); ?></p>
                                <p><i class="fas fa-calendar-alt"></i> <strong>Jadwal:</strong> <?php echo htmlspecialchars($dokter['jadwal']); ?></p>
                            </div>
                            <a href="#chat" class="btn chat-now">Chat Sekarang</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Chat -->
        <section id="chat" class="landing-section">
            <div class="container">
                <h1>Mulai Konsultasi</h1>
                <p>Pilih dokter dan isi data diri untuk memulai sesi konsultasi.</p>

                <!-- Pilihan Dokter -->
                <div class="doctor-selection">
                    <h3><i class="fas fa-user-md"></i> Pilih Dokter Anda</h3>
                    <p class="selection-subtitle">Pilih dokter yang paling sesuai dengan kebutuhan Anda</p>

                    <div class="doctor-grid">
                        <?php if (!empty($dokter_list)): ?>
                            <?php foreach ($dokter_list as $dokter): ?>
                                <div class="doctor-card" data-dokter-id="<?php echo $dokter['id']; ?>"
                                    data-dokter-nama="<?php echo htmlspecialchars($dokter['nama']); ?>">
                                    <div class="doctor-card-header">
                                        <div class="doctor-avatar">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="doctor-basic-info">
                                            <h4>Dr. <?php echo htmlspecialchars($dokter['nama']); ?></h4>
                                            <span class="doctor-specialty"><?php echo htmlspecialchars($dokter['spesialisasi'] ?? 'Psikolog'); ?></span>
                                        </div>
                                    </div>

                                    <div class="doctor-card-body">
                                        <div class="doctor-info-item">
                                            <i class="fas fa-graduation-cap"></i>
                                            <span><?php echo htmlspecialchars($dokter['pendidikan'] ?? 'S2 Psikologi'); ?></span>
                                        </div>
                                        <div class="doctor-info-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span><?php echo htmlspecialchars($dokter['pengalaman'] ?? '5+ tahun'); ?></span>
                                        </div>
                                    </div>

                                    <div class="doctor-card-footer">
                                        <button type="button" class="btn-select-dokter">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Pilih Dokter Ini</span>
                                        </button>
                                        <div class="doctor-status">
                                            <span class="status-badge <?php echo ($dokter['status_online'] ?? 0) ? 'online' : 'offline'; ?>">
                                                <i class="fas fa-circle"></i>
                                                <?php echo ($dokter['status_online'] ?? 0) ? 'Online' : 'Offline'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-doctors">
                                <i class="fas fa-user-md-slash"></i>
                                <h4>Belum ada dokter yang tersedia</h4>
                                <p>Silakan coba lagi nanti atau hubungi admin untuk informasi lebih lanjut.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Konsultasi Sederhana -->
                <div class="consultation-form-wrapper" id="consultationForm">
                    <div class="form-header">
                        <h3><i class="fas fa-file-medical"></i> Formulir Konsultasi</h3>
                        <div class="selected-doctor-display" id="selectedDoctorDisplay">
                            <div class="selected-doctor-info">
                                <i class="fas fa-user-md"></i>
                                <div>
                                    <strong>Dokter yang Dipilih:</strong>
                                    <span id="selectedDoctorName">Belum ada dokter dipilih</span>
                                </div>
                            </div>
                            <button type="button" class="btn-change-doctor" id="btnChangeDoctor">
                                <i class="fas fa-exchange-alt"></i>
                                Ganti Dokter
                            </button>
                        </div>
                    </div>

                    <form id="consultation-form" style="max-width: 500px; margin: 0 auto;">
                        <input type="hidden" name="dokter_id" id="dokterId" value="">

                        <div class="form-group">
                            <label for="nama">
                                <i class="fas fa-user"></i>
                                Nama Lengkap
                            </label>
                            <input type="text" id="nama" name="nama" required
                                placeholder="Masukkan nama lengkap Anda">
                        </div>

                        <div class="form-group">
                            <label for="usia">
                                <i class="fas fa-birthday-cake"></i>
                                Usia
                            </label>
                            <input type="number" id="usia" name="usia" min="1" max="120" required
                                placeholder="Masukkan usia">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-start-chat" id="btnStartChat" disabled>
                                <i class="fas fa-comment-medical"></i>
                                Mulai Konsultasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Artikel -->
        <section id="articles" class="landing-section">
            <div class="container">
                <h1>Artikel Kesehatan Mental</h1>
                <p>Baca artikel informatif tentang kesehatan mental.</p>
                <div class="article-list">
                    <?php foreach ($artikel_list as $artikel): ?>
                        <div class="article-card">
                            <h3><?php echo htmlspecialchars($artikel['judul']); ?></h3>
                            <p><?php echo htmlspecialchars($artikel['isi']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Bantuan -->
        <section id="help" class="landing-section">
            <div class="container">
                <h1>Bantuan</h1>
                <p>Jika Anda membutuhkan bantuan, hubungi kami melalui:</p>
                <ul>
                    <li>Email: support@mentara.com</li>
                    <li>Telepon: 0800-123-4567</li>
                    <li>Chat: Gunakan fitur chat di website ini</li>
                </ul>
                <h3>FAQ</h3>
                <div class="faq">
                    <h4>Apakah konsultasi ini gratis?</h4>
                    <p>Ya, konsultasi awal gratis untuk semua pengguna.</p>
                    <h4>Apakah data saya aman?</h4>
                    <p>Kami menjaga kerahasiaan data Anda dengan standar keamanan tinggi.</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Mentara. Semua hak dilindungi.</p>
        </div>
    </footer>

    <style>
        .landing-section {
            min-height: 100vh;
            padding: 4rem 0;
            display: flex;
            align-items: center;
        }

        #home {
            text-align: center;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            background: url("assets/images/herobg.jpeg");
            background-size: cover;
            background-position: center;
            color: white;
        }

        #home h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: white;
        }

        #home p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature {
            text-align: center;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature i {
            font-size: 3rem;
            color: white;
            margin-bottom: 1rem;
        }

        .feature h3 {
            margin-bottom: 0.5rem;
            color: white;
        }

        .feature p {
            color: rgba(255, 255, 255, 0.9);
        }

        #counselors {
            /* background-color: #f8f9fa; */
            background-color: #F3F8FDFF;
        }

        #chat {
            background-color: #fff;
        }

        #articles {
            background-color: #f8f9fa;
        }

        #help {
            background-color: #fff;
        }

        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .doctor-card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
        }

        .article-list {
            margin-top: 3rem;
        }

        .article-card {
            background-color: #fff;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-3px);
        }

        .faq h4 {
            margin-top: 1.5rem;
            color: #2c3e50;
        }

        .scroll-link {
            text-decoration: none;
            color: inherit;
        }
    </style>

    <script>
        // Tambahkan ke bagian script di index.php
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('header');
            const sections = document.querySelectorAll('.landing-section');

            // Fungsi untuk menentukan apakah section memiliki background gelap
            function isDarkSection(section) {
                // Daftar section yang memiliki background gelap
                const darkSections = ['home'];
                return darkSections.includes(section.id);
            }

            // Fungsi untuk mengupdate warna navbar berdasarkan section yang aktif
            function updateNavbarStyle() {
                const scrollPosition = window.scrollY;

                // Tambah class scrolled saat di-scroll
                if (scrollPosition > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                // Cari section yang sedang aktif
                let activeSection = null;
                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 100;
                    const sectionBottom = sectionTop + section.offsetHeight;

                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        activeSection = section;
                    }
                });

                // Update warna teks navbar berdasarkan section aktif
                if (activeSection && isDarkSection(activeSection)) {
                    header.classList.add('light-text');
                } else {
                    header.classList.remove('light-text');
                }
            }

            // Panggil fungsi saat scroll
            window.addEventListener('scroll', updateNavbarStyle);

            // Panggil sekali saat halaman dimuat
            updateNavbarStyle();

            // Fungsi untuk scroll ke section (yang sudah ada)
            function showSection(sectionId) {
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Update URL hash
                    window.location.hash = sectionId;
                }
            }

            // Handle form submission for consultation
            document.getElementById('consultation-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('patient_form.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Handle initial load and hash changes
            window.addEventListener('load', function() {
                const hash = window.location.hash.substring(1);
                if (hash) {
                    setTimeout(() => {
                        showSection(hash);
                    }, 100);
                }
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    showSection(targetId);
                });
            });
        });

        // const header = document.querySelector('header');
        // const sections = document.querySelectorAll('.landing-section');

        // // Fungsi untuk mengupdate style navbar berdasarkan section yang aktif
        // function updateNavbarStyle() {
        //     const scrollPosition = window.scrollY;

        //     // Hapus semua class section sebelumnya
        //     header.classList.remove('in-home', 'in-counselors', 'in-chat', 'in-articles', 'in-help');

        //     // Tambah class scrolled saat di-scroll
        //     if (scrollPosition > 50) {
        //         header.classList.add('scrolled');
        //     } else {
        //         header.classList.remove('scrolled');
        //     }

        //     // Cari section yang sedang aktif
        //     let activeSection = null;
        //     sections.forEach(section => {
        //         const sectionTop = section.offsetTop - 100;
        //         const sectionBottom = sectionTop + section.offsetHeight;

        //         if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
        //             activeSection = section;
        //         }
        //     });

        //     // Tambahkan class spesifik berdasarkan section aktif
        //     if (activeSection) {
        //         header.classList.add(`in-${activeSection.id}`);

        //         // Update warna teks berdasarkan section
        //         if (activeSection.id === 'home') {
        //             header.classList.add('light-text');
        //         } else {
        //             header.classList.remove('light-text');
        //         }
        //     }
        // }

        // // Panggil fungsi saat scroll
        // window.addEventListener('scroll', updateNavbarStyle);

        // // Panggil sekali saat halaman dimuat
        // updateNavbarStyle();
    </script>
    <script>
// JavaScript untuk pilihan dokter sederhana
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const doctorCards = document.querySelectorAll('.doctor-card');
    const selectedDoctorName = document.getElementById('selectedDoctorName');
    const selectedDoctorDisplay = document.getElementById('selectedDoctorDisplay');
    const dokterIdInput = document.getElementById('dokterId');
    const btnChangeDoctor = document.getElementById('btnChangeDoctor');
    const btnStartChat = document.getElementById('btnStartChat');
    const consultationForm = document.getElementById('consultation-form');
    
    // State
    let selectedDoctor = null;
    
    // Doctor Selection Logic
    doctorCards.forEach(card => {
        card.addEventListener('click', function() {
            // Hapus selected class dari semua cards
            doctorCards.forEach(c => c.classList.remove('selected'));
            
            // Tambah selected class ke card yang diklik
            this.classList.add('selected');
            
            // Update tombol pilih dokter
            const button = this.querySelector('.btn-select-dokter');
            button.innerHTML = '<i class="fas fa-check-circle"></i><span>Terpilih</span>';
            button.classList.add('selected');
            
            // Update selected doctor
            selectedDoctor = {
                id: this.dataset.dokterId,
                name: this.dataset.dokterNama
            };
            
            // Update form display
            updateFormDisplay();
            
            // Scroll ke form
            document.getElementById('consultationForm').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Update form display
    function updateFormDisplay() {
        if (selectedDoctor) {
            selectedDoctorName.textContent = selectedDoctor.name;
            dokterIdInput.value = selectedDoctor.id;
            selectedDoctorDisplay.style.display = 'flex';
            btnStartChat.disabled = false;
        } else {
            selectedDoctorName.textContent = 'Belum ada dokter dipilih';
            dokterIdInput.value = '';
            selectedDoctorDisplay.style.display = 'none';
            btnStartChat.disabled = true;
        }
    }
    
    // Change doctor button
    btnChangeDoctor.addEventListener('click', function() {
        selectedDoctor = null;
        updateFormDisplay();
        
        // Reset doctor cards
        doctorCards.forEach(card => {
            card.classList.remove('selected');
            const button = card.querySelector('.btn-select-dokter');
            button.innerHTML = '<i class="fas fa-check-circle"></i><span>Pilih Dokter Ini</span>';
            button.classList.remove('selected');
        });
        
        // Scroll back to doctor selection
        document.querySelector('.doctor-selection').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });
    
    // Form submission
    consultationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if doctor is selected
        if (!selectedDoctor) {
            showAlert('Silakan pilih dokter terlebih dahulu', 'warning');
            return;
        }
        
        // Validate form
        const nama = document.getElementById('nama').value.trim();
        const usia = document.getElementById('usia').value;
        
        if (!nama || !usia) {
            showAlert('Harap lengkapi nama dan usia', 'warning');
            return;
        }
        
        // Show loading state
        const originalText = btnStartChat.innerHTML;
        btnStartChat.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        btnStartChat.disabled = true;
        
        // Submit form
        const formData = new FormData(this);
        
        fetch('patient_form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Handle response
            if (data.includes('chat.php') || data.includes('Location:')) {
                // Redirect to chat page
                window.location.href = 'chat.php';
            } else {
                // Try to parse as JSON
                try {
                    const result = JSON.parse(data);
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        window.location.href = 'chat.php';
                    }
                } catch (e) {
                    // Default redirect
                    window.location.href = 'chat.php';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan. Silakan coba lagi.', 'warning');
            btnStartChat.innerHTML = originalText;
            btnStartChat.disabled = false;
        });
    });
    
    // Alert function
    function showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : 'check-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Insert before form
        consultationForm.parentNode.insertBefore(alertDiv, consultationForm);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Initialize
    updateFormDisplay();
});
</script>
</body>

</html>