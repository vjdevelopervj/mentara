<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];
$status = $_GET['status'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Handle AJAX send message from doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['id_sesi'])) {
    $id_sesi = (int)$_POST['id_sesi'];
    $pesan = bersihkan($_POST['message']);

    // Ensure this doctor owns the session
    $stmtChk = $pdo->prepare("SELECT id_dokter FROM sesi_chat WHERE id = ?");
    $stmtChk->execute([$id_sesi]);
    $row = $stmtChk->fetch();

    if ($row && $row['id_dokter'] == $id_pengguna) {
        $stmt = $pdo->prepare("INSERT INTO pesan (id_sesi, pengirim, pesan) VALUES (?, 'dokter', ?)");
        $stmt->execute([$id_sesi, $pesan]);
        $message_id = (int) $pdo->lastInsertId();

        // Update sesi_chat diperbarui_pada
        $stmtUp = $pdo->prepare("UPDATE sesi_chat SET diperbarui_pada = NOW() WHERE id = ?");
        $stmtUp->execute([$id_sesi]);

        // Kembalikan pesan terbaru untuk sesi ini
        $stmt2 = $pdo->prepare("SELECT p.id, p.id_sesi, p.pengirim, p.pesan, p.dibuat_pada,
            CASE WHEN p.pengirim = 'dokter' THEN COALESCE(d.nama, 'Dokter')
                 WHEN p.pengirim = 'sistem' THEN 'Sistem'
                 ELSE 'Pasien' END as nama_pengirim
            FROM pesan p
            LEFT JOIN sesi_chat sc ON p.id_sesi = sc.id
            LEFT JOIN pengguna d ON sc.id_dokter = d.id
            WHERE p.id = ?");
        $stmt2->execute([$message_id]);
        $message_row = $stmt2->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message_row]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized or session not found']);
    exit;
}

// Query untuk semua sesi chat
$sql = "
    SELECT sc.*, COUNT(p.id) as jumlah_pesan 
    FROM sesi_chat sc 
    LEFT JOIN pesan p ON sc.id = p.id_sesi 
    WHERE sc.id_dokter = ?
";

$params = [$id_pengguna];

// Filter berdasarkan status
if ($status != 'semua') {
    $sql .= " AND sc.status = ?";
    $params[] = $status;
}

// Filter berdasarkan pencarian
if (!empty($search)) {
    $sql .= " AND (sc.nama_pasien LIKE ? OR sc.keluhan LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " GROUP BY sc.id ORDER BY sc.diperbarui_pada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$semua_sesi = $stmt->fetchAll();

// Kembalikan HTML chat list
foreach ($semua_sesi as $sesi):
?>
<div class="chat-item" data-id="<?php echo $sesi['id']; ?>"
    data-time="<?php echo strtotime($sesi['diperbarui_pada']); ?>"
    data-name="<?php echo strtolower($sesi['nama_pasien']); ?>">
    <div class="chat-item-left">
        <div class="patient-avatar <?php echo $sesi['status']; ?>">
            <?php if ($sesi['status'] == 'aktif'): ?>
                <i class="fas fa-user-clock"></i>
            <?php elseif ($sesi['status'] == 'selesai'): ?>
                <i class="fas fa-user-check"></i>
            <?php else: ?>
                <i class="fas fa-user-times"></i>
            <?php endif; ?>
        </div>
        <div class="chat-info">
            <div class="chat-patient">
                <h4><?php echo htmlspecialchars($sesi['nama_pasien']); ?></h4>
                <span class="patient-age"><?php echo $sesi['usia_pasien']; ?> tahun</span>
                <span class="chat-status <?php echo $sesi['status']; ?>">
                    <?php echo ucfirst($sesi['status']); ?>
                </span>
            </div>
            <div class="chat-preview">
                <p class="complaint-preview">
                    <?php echo potongTeks($sesi['keluhan'] ?? 'Tidak ada keluhan', 100); ?>
                </p>
                <div class="chat-meta">
                    <span class="message-count">
                        <i class="fas fa-comment"></i>
                        <?php echo $sesi['jumlah_pesan']; ?> pesan
                    </span>
                    <span class="last-activity">
                        <i class="far fa-clock"></i>
                        <?php echo waktu_relatif($sesi['diperbarui_pada']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-actions">
        <?php if ($sesi['status'] == 'aktif'): ?>
            <a href="chat_session.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn-chat-action primary">
                <i class="fas fa-comment-medical"></i>
                Lanjutkan
            </a>
        <?php else: ?>
            <a href="chat_session.php?id_sesi=<?php echo $sesi['id']; ?>" class="btn-chat-action secondary">
                <i class="fas fa-eye"></i>
                Lihat
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn-more">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu">
                <a href="notes.php?id_sesi=<?php echo $sesi['id']; ?>">
                    <i class="fas fa-file-medical"></i> Catatan
                </a>
                <a href="chat_session.php?id_sesi=<?php echo $sesi['id']; ?>">
                    <i class="fas fa-comments"></i> Detail Chat
                </a>
                <a href="#" class="delete-chat" data-id="<?php echo $sesi['id']; ?>">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
