<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$id_sesi = isset($_POST['id_sesi']) ? (int) $_POST['id_sesi'] : 0;
$role = isset($_POST['role']) ? $_POST['role'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'typing';

if ($id_sesi <= 0 || !in_array($role, ['dokter', 'pasien'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

if ($role === 'pasien') {
    if (!isset($_SESSION['id_sesi_chat']) || (int) $_SESSION['id_sesi_chat'] !== $id_sesi) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

if ($role === 'dokter') {
    if (!isset($_SESSION['id_pengguna'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

$typing_dir = __DIR__ . '/storage/typing';
if (!is_dir($typing_dir)) {
    mkdir($typing_dir, 0755, true);
}

$typing_file = $typing_dir . '/typing_' . $id_sesi . '.json';
$data = [];
if (is_file($typing_file)) {
    $raw = file_get_contents($typing_file);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$now = time();
if ($status === 'stop') {
    $data[$role] = 0;
} else {
    $data[$role] = $now;
}

file_put_contents($typing_file, json_encode($data), LOCK_EX);
echo json_encode(['success' => true]);
