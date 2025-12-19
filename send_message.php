<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

if (!isset($_SESSION['id_sesi_chat'])) {
    echo json_encode(['success' => false, 'error' => 'No active session']);
    exit;
}

$id_sesi = $_SESSION['id_sesi_chat'];
$message = isset($_POST['message']) ? bersihkan($_POST['message']) : '';

if (trim($message) === '') {
    echo json_encode(['success' => false, 'error' => 'Empty message']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO pesan (id_sesi, pengirim, pesan) VALUES (?, 'pasien', ?)");
    $stmt->execute([$id_sesi, $message]);

    // Update sesi_chat diperbarui_pada
    $stmtUp = $pdo->prepare("UPDATE sesi_chat SET diperbarui_pada = NOW() WHERE id = ?");
    $stmtUp->execute([$id_sesi]);
    // Ambil pesan terbaru untuk dikembalikan
    $stmt2 = $pdo->prepare("SELECT p.*, CASE WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter') ELSE 'Pasien' END as nama_pengirim FROM pesan p LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id LEFT JOIN pengguna d ON sc.id_dokter = d.id WHERE p.id_sesi = ? ORDER BY p.dibuat_pada ASC");
    $stmt2->execute([$id_sesi]);
    $messages = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
