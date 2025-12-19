<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');
// Allow script to run a little longer while waiting for new messages
set_time_limit(30);
// Disable output buffering
while (ob_get_level()) ob_end_clean();

$id_sesi = isset($_GET['id_sesi']) ? (int)$_GET['id_sesi'] : 0;
$since_id = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
$only_from = isset($_GET['only_from']) ? $_GET['only_from'] : null;

 $start = time();
 $timeout = 25; // seconds

try {
    do {
        $sql = "SELECT p.*, 
           CASE 
               WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter') 
               WHEN p.pengirim = 'sistem' THEN 'Sistem'
               ELSE 'Pasien' 
           END as nama_pengirim
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

        $sql .= " ORDER BY p.dibuat_pada ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($messages)) {
            echo json_encode(['messages' => $messages]);
            // flush output immediately
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            exit;
        }

        // No new messages yet — wait a bit and try again
        usleep(500000); // 0.5s
    } while ((time() - $start) < $timeout);

    // Timeout, return empty
    echo json_encode(['messages' => []]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
