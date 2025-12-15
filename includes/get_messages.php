<?php
// includes/get_messages.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'dokter') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$id_sesi = $_GET['id_sesi'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM pesan WHERE id_sesi = ? ORDER BY dibuat_pada ASC");
$stmt->execute([$id_sesi]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);
?>