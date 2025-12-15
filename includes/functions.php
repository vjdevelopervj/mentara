<?php
// Cek apakah fungsi sudah dideklarasikan
if (!function_exists('bersihkan')) {
    function bersihkan($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

if (!function_exists('sudah_login')) {
    function sudah_login() {
        return isset($_SESSION['id_pengguna']);
    }
}

if (!function_exists('dapatkan_peran_pengguna')) {
    function dapatkan_peran_pengguna() {
        return $_SESSION['peran'] ?? null;
    }
}

if (!function_exists('alihkan_jika_belum_login')) {
    function alihkan_jika_belum_login() {
        if (!sudah_login()) {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('alihkan_jika_bukan_admin')) {
    function alihkan_jika_bukan_admin() {
        if (dapatkan_peran_pengguna() !== 'admin') {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('alihkan_jika_bukan_dokter')) {
    function alihkan_jika_bukan_dokter() {
        if (dapatkan_peran_pengguna() !== 'dokter') {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('waktu_relatif')) {
    function waktu_relatif($waktu) {
        date_default_timezone_set('Asia/Jakarta');
        
        $sekarang = new DateTime();
        $waktu_tertentu = new DateTime($waktu);
        $selisih = $sekarang->diff($waktu_tertentu);
        
        if ($selisih->y > 0) {
            return $selisih->y . ' tahun yang lalu';
        } elseif ($selisih->m > 0) {
            return $selisih->m . ' bulan yang lalu';
        } elseif ($selisih->d > 0) {
            return $selisih->d . ' hari yang lalu';
        } elseif ($selisih->h > 0) {
            return $selisih->h . ' jam yang lalu';
        } elseif ($selisih->i > 0) {
            return $selisih->i . ' menit yang lalu';
        } else {
            return 'Baru saja';
        }
    }
}

if (!function_exists('potongTeks')) {
    function potongTeks($teks, $panjang) {
        if (strlen($teks) <= $panjang) {
            return $teks;
        }
        return substr($teks, 0, $panjang) . '...';
    }
}
?>