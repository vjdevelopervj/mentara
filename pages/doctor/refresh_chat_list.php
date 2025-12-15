<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$id_pengguna = $_SESSION['id_pengguna'] ?? null;
if (!$id_pengguna) exit;

$status = $_GET['status'] ?? 'semua';
$search = $_GET['search'] ?? '';

$sql = "
    SELECT sc.*, COUNT(p.id) as jumlah_pesan 
    FROM sesi_chat sc 
    LEFT JOIN pesan p ON sc.id = p.id_sesi 
    WHERE sc.id_dokter = ?
";

$params = [$id_pengguna];

if ($status != 'semua') {
    $sql .= " AND sc.status = ?";
    $params[] = $status;
}

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
                    <?php echo potongTeks($sesi['keluhan'], 100); ?>
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