<?php
session_start();
include 'includes/db.php';

if (isset($_GET['id_sesi'])) {
    $id_sesi = (int) $_GET['id_sesi'];
    $only_from = isset($_GET['only_from']) ? $_GET['only_from'] : null; // expected 'dokter' or 'pasien'
    $since_id = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 200;

    if ($limit <= 0 || $limit > 200) {
        $limit = 200;
    }

    $allowed_pengirim = ['dokter', 'pasien', 'sistem'];
    if (!in_array($only_from, $allowed_pengirim, true)) {
        $only_from = null;
    }

    // Baseline query: ambil pesan dan nama pengirim (dokter diambil dari sesi_chat.id_dokter)
    $sql = "SELECT p.id, p.id_sesi, p.pengirim, p.pesan, p.dibuat_pada,
            CASE WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter')
                 WHEN p.pengirim = 'sistem' THEN 'Sistem'
                 ELSE 'Pasien' END as nama_pengirim
            FROM pesan p
            LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id
            LEFT JOIN pengguna d ON sc.id_dokter = d.id
            WHERE p.id_sesi = ?";

    $params = [$id_sesi];
    if ($since_id > 0) {
        $sql .= " AND p.id > ?";
        $params[] = $since_id;
    }
    if ($only_from) {
        $sql .= " AND p.pengirim = ?";
        $params[] = $only_from;
    }

    $sql .= " ORDER BY p.id ASC LIMIT " . $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

echo json_encode(['success' => false, 'messages' => []]);
?>
