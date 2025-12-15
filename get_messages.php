<?php
session_start();
include 'includes/db.php';

if (isset($_GET['id_sesi'])) {
    $id_sesi = $_GET['id_sesi'];
    
    // Query untuk mendapatkan pesan terbaru
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CASE 
                   WHEN p.pengirim = 'dokter' THEN d.nama 
                   ELSE 'Pasien' 
               END as nama_pengirim
        FROM pesan p
        LEFT JOIN pengguna d ON (p.pengirim = 'dokter' AND d.id = p.id_pengirim)
        WHERE p.id_sesi = ? 
        ORDER BY p.dibuat_pada ASC
    ");
    $stmt->execute([$id_sesi]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

echo json_encode(['success' => false, 'messages' => []]);
?>