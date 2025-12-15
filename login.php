<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (sudah_login()) {
    if (dapatkan_peran_pengguna() == 'admin') {
        header('Location: pages/admin/dashboard.php');
    } else {
        header('Location: pages/doctor/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pengguna = bersihkan($_POST['username']);
    $kata_sandi = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE nama_pengguna = ?");
    $stmt->execute([$nama_pengguna]);
    $pengguna = $stmt->fetch();

    if ($pengguna && password_verify($kata_sandi, $pengguna['kata_sandi'])) {
        $_SESSION['id_pengguna'] = $pengguna['id'];
        $_SESSION['nama_pengguna'] = $pengguna['nama_pengguna'];
        $_SESSION['peran'] = $pengguna['peran'];

        if ($pengguna['peran'] == 'admin') {
            header('Location: pages/admin/dashboard.php');
        } else {
            header('Location: pages/doctor/dashboard.php');
        }
        exit;
    } else {
        $error = 'Nama pengguna atau kata sandi salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mentara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="login-page">
    <div class="login-wrapper">
        <!-- Left side - Brand/Info -->
        <div class="login-left">
            <div class="brand-container">
                <div class="logo-brand">
                    <!-- <i class="fas fa-heartbeat"></i> -->
                    <img src="assets/images/mentara-logo.png" alt="logo">
                    <span>MENTARA</span>
                </div>
                <h1>Selamat Datang Kembali</h1>
                <p>Masuk ke akun Anda untuk melanjutkan ke platform kesehatan mental terpercaya</p>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Keamanan data terjamin</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock"></i>
                        <span>Akses 24/7</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-user-md"></i>
                        <span>Tenaga profesional</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="login-right">
            <div class="login-container">
                <div class="login-header">
                    <h2>Masuk ke Akun</h2>
                    <p>Silakan masukkan kredensial Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" action="login.php" method="post">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            <span>Nama Pengguna</span>
                        </label>
                        <div class="input-with-icon">
                            <input type="text" id="username" name="username" required
                                placeholder="Masukkan nama pengguna">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            <span>Kata Sandi</span>
                        </label>
                        <div class="input-with-icon">
                            <input type="password" id="password" name="password" required
                                placeholder="Masukkan kata sandi">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="forgot-password">
                            <a href="forgot-password.php">Lupa kata sandi?</a>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="btn-login-submit">
                        <span>Masuk</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="divider">
                        <span>atau</span>
                    </div>

                    <div class="alternative-login">
                        <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                    </div>

                    <div class="back-home">
                        <a href="index.php" class="btn-back-home">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </form>

                <div class="login-footer">
                    <p>&copy; 2024 Mentara. Hak cipta dilindungi.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form validation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!username || !password) {
                e.preventDefault();
                alert('Harap isi semua field yang diperlukan');
            }
        });

        // Add focus effects
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>

</html>