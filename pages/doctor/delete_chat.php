<?php
// delete_chat.php
session_start();
include '../includes/db.php';
include '../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$id_sesi = $_GET['id'] ?? 0;

// Cek apakah sesi milik dokter ini
$stmt = $pdo->prepare("SELECT status FROM sesi_chat WHERE id = ? AND id_dokter = ?");
$stmt->execute([$id_sesi, $id_pengguna]);
$sesi = $stmt->fetch();

if (!$sesi) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak ditemukan']);
    exit;
}

// Hanya bisa menghapus sesi yang sudah selesai atau dibatalkan
if ($sesi['status'] === 'aktif') {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus sesi yang masih aktif']);
    exit;
}

try {
    // Hapus pesan terlebih dahulu
    $pdo->prepare("DELETE FROM pesan WHERE id_sesi = ?")->execute([$id_sesi]);
    
    // Hapus sesi
    $pdo->prepare("DELETE FROM sesi_chat WHERE id = ?")->execute([$id_sesi]);
    
    echo json_encode(['success' => true, 'message' => 'Sesi berhasil dihapus']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>