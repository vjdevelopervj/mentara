<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_admin();

// Tangani aksi dokter
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_dokter'])) {
        $nama_pengguna = bersihkan($_POST['nama_pengguna']);
        $kata_sandi = password_hash($_POST['kata_sandi'], PASSWORD_DEFAULT);
        $nama = bersihkan($_POST['nama']);
        $spesialisasi = bersihkan($_POST['spesialisasi']);
        $jadwal = bersihkan($_POST['jadwal']);

        $stmt = $pdo->prepare("INSERT INTO pengguna (nama_pengguna, kata_sandi, peran, nama, spesialisasi, jadwal) VALUES (?, ?, 'dokter', ?, ?, ?)");
        $stmt->execute([$nama_pengguna, $kata_sandi, $nama, $spesialisasi, $jadwal]);
    } elseif (isset($_POST['ubah_status'])) {
        $id_dokter = (int)$_POST['id_dokter'];
        $stmt = $pdo->prepare("UPDATE pengguna SET aktif = NOT aktif WHERE id = ? AND peran = 'dokter'");
        $stmt->execute([$id_dokter]);
    }
}

// Dapatkan dokter
$stmt = $pdo->query("SELECT * FROM pengguna WHERE peran = 'dokter' ORDER BY nama");
$dokter_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - Admin Mentara</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="doctors.php" class="active"><i class="fas fa-user-md"></i> Kelola Dokter</a></li>
                <li><a href="consultations.php"><i class="fas fa-list"></i> Konsultasi</a></li>
                <li><a href="ratings.php"><i class="fas fa-star"></i> Rating</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Kelola Dokter</h1>

            <button onclick="toggleForm()" class="btn">Tambah Dokter Baru</button>

            <div id="add-doctor-form" style="display: none; margin-top: 2rem; background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <h2>Tambah Dokter Baru</h2>
                <form action="doctors.php" method="post">
                    <div class="form-group">
                        <label for="nama_pengguna">Nama Pengguna:</label>
                        <input type="text" id="nama_pengguna" name="nama_pengguna" required>
                    </div>
                    <div class="form-group">
                        <label for="kata_sandi">Kata Sandi:</label>
                        <input type="password" id="kata_sandi" name="kata_sandi" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="spesialisasi">Spesialisasi:</label>
                        <input type="text" id="spesialisasi" name="spesialisasi" required>
                    </div>
                    <div class="form-group">
                        <label for="jadwal">Jadwal:</label>
                        <textarea id="jadwal" name="jadwal" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="tambah_dokter" class="btn">Tambah Dokter</button>
                </form>
            </div>

            <h2 style="margin-top: 2rem;">Daftar Dokter</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Spesialisasi</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dokter_list as $dokter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dokter['nama']); ?></td>
                        <td><?php echo htmlspecialchars($dokter['spesialisasi']); ?></td>
                        <td><?php echo htmlspecialchars($dokter['jadwal']); ?></td>
                        <td><?php echo $dokter['aktif'] ? 'Aktif' : 'Nonaktif'; ?></td>
                        <td>
                            <form action="doctors.php" method="post" style="display: inline;">
                                <input type="hidden" name="id_dokter" value="<?php echo $dokter['id']; ?>">
                                <button type="submit" name="ubah_status" class="btn btn-warning">
                                    <?php echo $dokter['aktif'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('add-doctor-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
