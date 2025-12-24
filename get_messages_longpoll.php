<?php
session_start();
session_write_close();
require_once 'includes/db.php';

header('Content-Type: application/json');
// Allow script to run a little longer while waiting for new messages
set_time_limit(30);
// Disable output buffering
while (ob_get_level()) ob_end_clean();

$id_sesi = isset($_GET['id_sesi']) ? (int) $_GET['id_sesi'] : 0;
$since_id = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;
$only_from = isset($_GET['only_from']) ? $_GET['only_from'] : null;
$typing_ts = isset($_GET['typing_ts']) ? (int) $_GET['typing_ts'] : 0;

if ($id_sesi <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid session']);
    exit;
}

$allowed_pengirim = ['dokter', 'pasien', 'sistem'];
if (!in_array($only_from, $allowed_pengirim, true)) {
    $only_from = null;
}

$typing_dir = __DIR__ . '/storage/typing';
$typing_file = $typing_dir . '/typing_' . $id_sesi . '.json';
$typing_ttl = 4;

function read_typing_state(string $file_path, int $ttl): array
{
    $data = [];
    if (is_file($file_path)) {
        $raw = file_get_contents($file_path);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    $now = time();
    $dokter_ts = isset($data['dokter']) ? (int) $data['dokter'] : 0;
    $pasien_ts = isset($data['pasien']) ? (int) $data['pasien'] : 0;

    return [
        'dokter' => $dokter_ts > 0 && ($now - $dokter_ts) <= $ttl,
        'pasien' => $pasien_ts > 0 && ($now - $pasien_ts) <= $ttl
    ];
}

$start = time();
$timeout = 25; // seconds

$sql = "SELECT p.id, p.id_sesi, p.pengirim, p.pesan, p.dibuat_pada,
           CASE
               WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter')
               WHEN p.pengirim = 'sistem' THEN 'Sistem'
               ELSE 'Pasien'
           END as nama_pengirim
        FROM pesan p
        LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id
        LEFT JOIN pengguna d ON sc.id_dokter = d.id
        WHERE p.id_sesi = ? AND p.id > ?";

$params = [$id_sesi, $since_id];
if ($only_from) {
    $sql .= " AND p.pengirim = ?";
    $params[] = $only_from;
}

$sql .= " ORDER BY p.id ASC LIMIT 100";
$stmt = $pdo->prepare($sql);

try {
    do {
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $current_typing_ts = is_file($typing_file) ? (int) filemtime($typing_file) : 0;
        $typing_changed = $current_typing_ts > $typing_ts;

        if (!empty($messages) || $typing_changed) {
            $typing_state = read_typing_state($typing_file, $typing_ttl);
            echo json_encode([
                'messages' => $messages,
                'typing' => $typing_state,
                'typing_ts' => $current_typing_ts
            ]);
            // flush output immediately
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            exit;
        }

        // No new messages yet - wait a bit and try again
        usleep(350000); // 0.35s
    } while ((time() - $start) < $timeout);

    $current_typing_ts = is_file($typing_file) ? (int) filemtime($typing_file) : 0;
    $typing_state = read_typing_state($typing_file, $typing_ttl);
    // Timeout, return empty
    echo json_encode([
        'messages' => [],
        'typing' => $typing_state,
        'typing_ts' => $current_typing_ts
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
