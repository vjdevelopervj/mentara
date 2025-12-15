<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];

// Filter status
$status = $_GET['status'] ?? 'semua';
$search = $_GET['search'] ?? '';

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

// Hitung statistik per status
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as jumlah 
    FROM sesi_chat 
    WHERE id_dokter = ? 
    GROUP BY status
");
$stmt->execute([$id_pengguna]);
$statistik_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);


?>

<?php
include '../../includes/db.php';
include '../../includes/functions.php';

alihkan_jika_belum_login();
alihkan_jika_bukan_dokter();

$id_pengguna = $_SESSION['id_pengguna'];

// DEBUG: Log user ID
error_log("Doctor ID: " . $id_pengguna);

// Filter status
$status = $_GET['status'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query untuk semua sesi chat
$sql = "
    SELECT sc.*, 
           COUNT(p.id) as jumlah_pesan,
           MAX(p.dibuat_pada) as pesan_terakhir
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

$sql .= " GROUP BY sc.id ORDER BY COALESCE(pesan_terakhir, sc.diperbarui_pada) DESC";

error_log("SQL Query: " . $sql);
error_log("Params: " . print_r($params, true));

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$semua_sesi = $stmt->fetchAll();

// DEBUG: Log jumlah sesi ditemukan
error_log("Total sessions found: " . count($semua_sesi));

// Hitung statistik per status
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as jumlah 
    FROM sesi_chat 
    WHERE id_dokter = ? 
    GROUP BY status
");
$stmt->execute([$id_pengguna]);
$statistik_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Chat - Dokter Mentara</title>
    <link rel="stylesheet" href="../../assets/css/doctor.css">
    <link rel="stylesheet" href="../../assets/css/chat_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="doctor-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Panel Dokter</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="chat.php" class="active"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="notes.php"><i class="fas fa-sticky-note"></i> Catatan Sesi</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content chat-list-page">
            <!-- Header -->
            <div class="chat-header">
                <div class="header-left">
                    <h1><i class="fas fa-comments"></i> Daftar Chat</h1>
                    <p>Kelola semua sesi konseling dengan pasien</p>
                </div>
                <div class="header-right">
                    <a href="chat_session.php?new=true" class="btn-new-chat">
                        <i class="fas fa-plus-circle"></i>
                        <span>Chat Baru</span>
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <form method="GET" action="chat.php">
                        <input type="text" name="search" placeholder="Cari pasien atau keluhan..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="status" value="<?php echo $status; ?>">
                        <button type="submit" class="btn-search">Cari</button>
                    </form>
                </div>

                <div class="filter-tabs">
                    <a href="?status=semua" class="filter-tab <?php echo $status == 'semua' ? 'active' : ''; ?>">
                        <span>Semua</span>
                        <span class="tab-count"><?php echo array_sum($statistik_status); ?></span>
                    </a>
                    <a href="?status=aktif" class="filter-tab <?php echo $status == 'aktif' ? 'active' : ''; ?>">
                        <span>Aktif</span>
                        <span class="tab-count"><?php echo $statistik_status['aktif'] ?? 0; ?></span>
                    </a>
                    <a href="?status=selesai" class="filter-tab <?php echo $status == 'selesai' ? 'active' : ''; ?>">
                        <span>Selesai</span>
                        <span class="tab-count"><?php echo $statistik_status['selesai'] ?? 0; ?></span>
                    </a>
                    <a href="?status=dibatalkan" class="filter-tab <?php echo $status == 'dibatalkan' ? 'active' : ''; ?>">
                        <span>Dibatalkan</span>
                        <span class="tab-count"><?php echo $statistik_status['dibatalkan'] ?? 0; ?></span>
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card-mini">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo array_sum($statistik_status); ?></h3>
                        <p>Total Sesi</p>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statistik_status['aktif'] ?? 0; ?></h3>
                        <p>Sesi Aktif</p>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statistik_status['selesai'] ?? 0; ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>
                <div class="stat-card-mini">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statistik_status['dibatalkan'] ?? 0; ?></h3>
                        <p>Dibatalkan</p>
                    </div>
                </div>
            </div>

            <!-- Chat List -->
            <div class="chat-list-container">
                <div class="chat-list-header">
                    <h3>Daftar Konseling</h3>
                    <div class="sort-options">
                        <span>Urutkan:</span>
                        <select id="sortSelect">
                            <option value="terbaru">Terbaru</option>
                            <option value="terlama">Terlama</option>
                            <option value="nama">Nama Pasien</option>
                        </select>
                    </div>
                </div>

                <div class="chat-list" id="chatList">
                    <?php if (count($semua_sesi) > 0): ?>
                        <?php foreach ($semua_sesi as $sesi): ?>
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
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-comment-slash"></i>
                            </div>
                            <h3>Tidak ada sesi chat</h3>
                            <p><?php echo $status != 'semua' ? "Tidak ada sesi dengan status '$status'" : "Belum ada sesi chat yang dibuat"; ?></p>
                            <a href="chat_session.php?new=true" class="btn-new-session">
                                <i class="fas fa-plus"></i>
                                Buat Sesi Baru
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-bar">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
                <div class="export-actions">
                    <button class="btn-export" onclick="exportChats('pdf')">
                        <i class="fas fa-file-pdf"></i>
                        Export PDF
                    </button>
                    <button class="btn-export" onclick="exportChats('csv')">
                        <i class="fas fa-file-csv"></i>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Konfirmasi Hapus</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus sesi chat ini?</p>
                <p><small>Catatan: Hanya sesi yang selesai atau dibatalkan yang dapat dihapus.</small></p>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel">Batal</button>
                <button class="btn-confirm">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // Sort functionality
        document.getElementById('sortSelect')?.addEventListener('change', function() {
            const sortBy = this.value;
            const chatList = document.getElementById('chatList');
            const chatItems = Array.from(chatList.querySelectorAll('.chat-item'));

            chatItems.sort((a, b) => {
                if (sortBy === 'terbaru') {
                    return b.dataset.time - a.dataset.time;
                } else if (sortBy === 'terlama') {
                    return a.dataset.time - b.dataset.time;
                } else if (sortBy === 'nama') {
                    return a.dataset.name.localeCompare(b.dataset.name);
                }
                return 0;
            });

            chatList.innerHTML = '';
            chatItems.forEach(item => chatList.appendChild(item));
        });

        // Dropdown menu
        function attachDropdownListeners() {
            document.querySelectorAll('.btn-more').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    const isVisible = menu.style.display === 'block';

                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(m => {
                        m.style.display = 'none';
                    });

                    // Toggle current dropdown
                    menu.style.display = isVisible ? 'none' : 'block';
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            });
        }

        // Delete confirmation
        let chatToDelete = null;

        function attachDeleteListeners() {
            document.querySelectorAll('.delete-chat').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    chatToDelete = this.dataset.id;
                    document.getElementById('deleteModal').style.display = 'flex';
                });
            });
        }

        // Modal close buttons
        document.querySelectorAll('.modal-close, .btn-cancel').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deleteModal').style.display = 'none';
                chatToDelete = null;
            });
        });

        // Confirm delete
        document.querySelector('.btn-confirm')?.addEventListener('click', function() {
            if (chatToDelete) {
                fetch(`delete_chat.php?id=${chatToDelete}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus chat');
                    });
            }
            document.getElementById('deleteModal').style.display = 'none';
        });

        // Export functionality
        function exportChats(format) {
            const status = '<?php echo $status; ?>';
            const search = '<?php echo urlencode($search); ?>';

            window.open(`export_chats.php?format=${format}&status=${status}&search=${search}`, '_blank');
        }

        // Highlight active chat items
        function attachChatItemListeners() {
            document.querySelectorAll('.chat-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (!e.target.closest('.chat-actions')) {
                        const chatId = this.dataset.id;
                        window.location.href = `chat_session.php?id_sesi=${chatId}`;
                    }
                });

                // Add hover effect
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });

                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
            });
        }

        // Auto refresh chat list
        let autoRefresh = true;
        let refreshInterval;

        function refreshChatList() {
            if (autoRefresh && !document.querySelector('.modal[style*="display: flex"]')) {
                const currentUrl = new URL(window.location.href);
                const params = new URLSearchParams(currentUrl.search);

                // Menambahkan timestamp untuk menghindari cache
                params.append('_t', Date.now());

                fetch(`refresh_chat_list.php?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (html.trim()) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            const newChatList = tempDiv.querySelector('.chat-list');
                            if (newChatList) {
                                document.querySelector('.chat-list').innerHTML = newChatList.innerHTML;

                                // Re-attach all event listeners
                                attachDropdownListeners();
                                attachDeleteListeners();
                                attachChatItemListeners();

                                // Highlight new messages
                                highlightNewMessages();

                                // Update last refresh time
                                updateLastRefresh();
                            } else {
                                // Jika tidak ada .chat-list, ganti seluruh konten
                                document.querySelector('.chat-list').innerHTML = html;

                                // Re-attach all event listeners
                                attachDropdownListeners();
                                attachDeleteListeners();
                                attachChatItemListeners();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Refresh error:', error);
                        // Jika error, reload halaman setelah 60 detik
                        setTimeout(() => {
                            if (autoRefresh) location.reload();
                        }, 60000);
                    });
            }
        }

        function highlightNewMessages() {
            const lastUpdate = localStorage.getItem('lastChatUpdate') || Date.now();
            const now = Date.now();

            // Check for new messages in each chat item
            document.querySelectorAll('.chat-item').forEach(item => {
                const itemTime = parseInt(item.dataset.time) * 1000;
                if (itemTime > lastUpdate) {
                    // Add highlight animation
                    item.classList.add('new-message');

                    // Remove highlight after 5 seconds
                    setTimeout(() => {
                        item.classList.remove('new-message');
                    }, 5000);
                }
            });

            localStorage.setItem('lastChatUpdate', now);
        }

        function updateLastRefresh() {
            localStorage.setItem('lastRefresh', Date.now());
        }

        // Initialize all event listeners
        function initializeListeners() {
            attachDropdownListeners();
            attachDeleteListeners();
            attachChatItemListeners();

            // Check for new messages on load
            highlightNewMessages();
        }

        // Start auto-refresh
        function startAutoRefresh(interval = 5000) {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(refreshChatList, interval);
        }

        // Pause auto-refresh ketika user sedang berinteraksi dengan modal
        function setupModalObserver() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'style') {
                            autoRefresh = modal.style.display === 'none';
                            if (autoRefresh) {
                                startAutoRefresh(5000);
                            } else {
                                if (refreshInterval) clearInterval(refreshInterval);
                            }
                        }
                    });
                });

                observer.observe(modal, {
                    attributes: true
                });
            }
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeListeners();
            setupModalObserver();
            startAutoRefresh(5000); // Refresh setiap 5 detik

            // Also refresh immediately on load
            setTimeout(refreshChatList, 1000);
        });

        // Add CSS for new message animation
        const style = document.createElement('style');
        style.textContent = `
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(67, 97, 238, 0); }
        100% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0); }
    }
    
    .chat-item.new-message {
        animation: pulse 2s infinite;
        border-left: 4px solid #4361ee;
        background-color: rgba(67, 97, 238, 0.05);
    }
    
    /* Smooth transition for chat list updates */
    .chat-list {
        transition: opacity 0.3s ease;
    }
    
    .chat-list.updating {
        opacity: 0.7;
    }
`;
        document.head.appendChild(style);

        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (refreshInterval) clearInterval(refreshInterval);
                autoRefresh = false;
            } else {
                autoRefresh = true;
                startAutoRefresh(2000); // Refresh lebih cepat saat kembali
                refreshChatList(); // Refresh segera
            }
        });

        // Refresh when coming back from chat session
        if (performance.navigation.type === 2) {
            // Page was accessed via back/forward button
            setTimeout(refreshChatList, 500);
        }
    </script>
</body>

</html>