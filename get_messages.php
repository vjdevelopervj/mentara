<?php
session_start();
include 'includes/db.php';

if (isset($_GET['id_sesi'])) {
    $id_sesi = (int)$_GET['id_sesi'];
    $only_from = isset($_GET['only_from']) ? $_GET['only_from'] : null; // expected 'dokter' or 'pasien'

    // Baseline query: ambil pesan dan nama pengirim (dokter diambil dari sesi_chat.id_dokter)
    $sql = "SELECT p.*, CASE WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter') WHEN p.pengirim = 'sistem' THEN 'Sistem' ELSE 'Pasien' END as nama_pengirim FROM pesan p LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id LEFT JOIN pengguna d ON sc.id_dokter = d.id WHERE p.id_sesi = ?";

    $params = [$id_sesi];
    if ($only_from === 'dokter' || $only_from === 'pasien') {
        $sql .= " AND p.pengirim = ?";
        $params[] = $only_from;
    }

    $sql .= " ORDER BY p.dibuat_pada ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

echo json_encode(['success' => false, 'messages' => []]);
?>