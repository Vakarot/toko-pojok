<?php
// history.php - Halaman untuk melihat riwayat aktivitas sistem

session_start();
include 'koneksi.php';

// Cek session
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Cek role - hanya Owner dan Inventory Control yang bisa lihat riwayat lengkap
$user_role = $_SESSION['role'] ?? '';
if (!in_array($user_role, ['Owner'])) {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini.'); window.location.href='index.php';</script>";
    exit();
}

// Handle export request
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $date_from = $_GET['date_from'] ?? null;
    $date_to = $_GET['date_to'] ?? null;
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="history_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Kode Riwayat', 'Nama Pengguna', 'Role', 'Action', 'Timestamp']);
    
    $export_query = "SELECT r.kode_riwayat, p.nama, p.role, r.action, r.timestamp 
                     FROM riwayat r 
                     JOIN pengguna p ON r.id_pengguna = p.id_pengguna";
    
    $conditions = [];
    if ($date_from) {
        $conditions[] = "DATE(r.timestamp) >= '" . mysqli_real_escape_string($connect, $date_from) . "'";
    }
    if ($date_to) {
        $conditions[] = "DATE(r.timestamp) <= '" . mysqli_real_escape_string($connect, $date_to) . "'";
    }
    
    if (!empty($conditions)) {
        $export_query .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $export_query .= " ORDER BY r.timestamp DESC";
    
    $export_result = mysqli_query($connect, $export_query);
    while ($row = mysqli_fetch_assoc($export_result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

// Get filter parameters
$filter_user = $_GET['filter_user'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query for history with filters
$history_query = "SELECT r.kode_riwayat, r.id_pengguna, p.nama, p.role, r.action, r.timestamp 
                  FROM riwayat r 
                  JOIN pengguna p ON r.id_pengguna = p.id_pengguna";

$conditions = [];
$params = [];

if ($filter_user) {
    $conditions[] = "r.id_pengguna = ?";
    $params[] = $filter_user;
}

if ($date_from) {
    $conditions[] = "DATE(r.timestamp) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $conditions[] = "DATE(r.timestamp) <= ?";
    $params[] = $date_to;
}

if ($search) {
    $conditions[] = "(p.nama LIKE ? OR r.action LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($conditions)) {
    $history_query .= " WHERE " . implode(' AND ', $conditions);
}

$history_query .= " ORDER BY r.timestamp DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Execute query
$stmt = mysqli_prepare($connect, $history_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii'; // last two are integers (limit, offset)
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM riwayat r JOIN pengguna p ON r.id_pengguna = p.id_pengguna";
if (!empty($conditions)) {
    $count_query .= " WHERE " . implode(' AND ', array_slice($conditions, 0, -2)); // Remove LIMIT params
}

$count_stmt = mysqli_prepare($connect, $count_query);
if (!empty($params) && count($params) > 2) {
    $count_types = str_repeat('s', count($params) - 2);
    mysqli_stmt_bind_param($count_stmt, $count_types, ...array_slice($params, 0, -2));
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get users for filter dropdown
$users_query = "SELECT id_pengguna, nama, role FROM pengguna ORDER BY nama";
$users_result = mysqli_query($connect, $users_query);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}

// Get activity statistics
$stats_query = "SELECT 
    COUNT(*) as total_activities,
    COUNT(DISTINCT r.id_pengguna) as active_users,
    COUNT(CASE WHEN DATE(r.timestamp) = CURDATE() THEN 1 END) as today_activities,
    COUNT(CASE WHEN DATE(r.timestamp) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_activities
    FROM riwayat r";

if (!empty($conditions)) {
    $stats_query .= " JOIN pengguna p ON r.id_pengguna = p.id_pengguna WHERE " . implode(' AND ', array_slice($conditions, 0, -2));
}

$stats_stmt = mysqli_prepare($connect, $stats_query);
if (!empty($params) && count($params) > 2) {
    $stats_types = str_repeat('s', count($params) - 2);
    mysqli_stmt_bind_param($stats_stmt, $stats_types, ...array_slice($params, 0, -2));
}
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent activities by type
$activity_types_query = "SELECT 
    CASE 
        WHEN action LIKE '%login%' THEN 'Login'
        WHEN action LIKE '%logout%' THEN 'Logout'
        WHEN action LIKE '%tambah%' OR action LIKE '%add%' THEN 'Tambah Data'
        WHEN action LIKE '%edit%' OR action LIKE '%update%' THEN 'Edit Data'
        WHEN action LIKE '%hapus%' OR action LIKE '%delete%' THEN 'Hapus Data'
        WHEN action LIKE '%penjualan%' OR action LIKE '%jual%' THEN 'Penjualan'
        WHEN action LIKE '%pemesanan%' OR action LIKE '%pesan%' THEN 'Pemesanan'
        ELSE 'Lainnya'
    END as activity_type,
    COUNT(*) as count
    FROM riwayat r
    WHERE DATE(r.timestamp) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY activity_type
    ORDER BY count DESC
    LIMIT 5";

$activity_types_result = mysqli_query($connect, $activity_types_query);
$activity_types = [];
while ($row = mysqli_fetch_assoc($activity_types_result)) {
    $activity_types[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="historyStyle.css">
    <title>History - TokoPojok</title>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Owner') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php" class="active"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Inventory Control') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
        </aside>

        <main class="main-content">
            <div class="header-top">
                <div>
                    <h1 class="header-title">History</h1>
                    <div class="header-subtitle">Riwayat Aktivitas Sistem</div>
                </div>
                <div class="search-profile">
                    <form method="GET" class="d-flex">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari aktivitas..." aria-label="Search activities" class="form-control me-2" />
                        <?php if (!empty($search)): ?>
                            <a href="history.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div class="profile-dropdown dropdown">
                        <div
                            class="profile-icon rounded-circle shadow-sm"
                            id="profileDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            role="button"
                            tabindex="0"
                            title="Profil"
                        >
                            <i class="fas fa-user"></i>
                        </div>
                        <ul
                            class="dropdown-menu dropdown-menu-end mt-2 rounded-3"
                            aria-labelledby="profileDropdown"
                        >
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                    <i class="fas fa-id-card text-success"></i> Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Export Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <?php if (!empty($search)): ?>
                        <small class="text-muted">Hasil pencarian untuk: "<?= htmlspecialchars($search) ?>"</small>
                    <?php endif; ?>
                    <small class="text-muted">Menampilkan <?= $total_records ?> aktivitas</small>
                </div>
                <div>
                    <a href="?export=csv<?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?>" 
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>

            <!-- History Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Riwayat</th>
                            <th>Nama Pengguna</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($history_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_riwayat']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($row['id_pengguna']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $row['role'] == 'Owner' ? 'danger' : 
                                            ($row['role'] == 'Inventory Control' ? 'warning' : 'info') 
                                        ?>">
                                            <?= htmlspecialchars($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-text">
                                            <?php
                                            $action = htmlspecialchars($row['action']);
                                            // Add icons based on action type
                                            if (strpos($action, 'login') !== false) {
                                                echo '<i class="fas fa-sign-in-alt text-success me-1"></i>';
                                            } elseif (strpos($action, 'logout') !== false) {
                                                echo '<i class="fas fa-sign-out-alt text-danger me-1"></i>';
                                            } elseif (strpos($action, 'tambah') !== false || strpos($action, 'add') !== false) {
                                                echo '<i class="fas fa-plus text-success me-1"></i>';
                                            } elseif (strpos($action, 'edit') !== false || strpos($action, 'update') !== false) {
                                                echo '<i class="fas fa-edit text-warning me-1"></i>';
                                            } elseif (strpos($action, 'hapus') !== false || strpos($action, 'delete') !== false) {
                                                echo '<i class="fas fa-trash text-danger me-1"></i>';
                                            } elseif (strpos($action, 'penjualan') !== false || strpos($action, 'jual') !== false) {
                                                echo '<i class="fas fa-cash-register text-info me-1"></i>';
                                            } elseif (strpos($action, 'pemesanan') !== false || strpos($action, 'pesan') !== false) {
                                                echo '<i class="fas fa-shopping-cart text-primary me-1"></i>';
                                            } else {
                                                echo '<i class="fas fa-info-circle text-secondary me-1"></i>';
                                            }
                                            echo $action;
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="timestamp">
                                            <strong><?= date('d/m/Y H:i:s', strtotime($row['timestamp'])) ?></strong><br>
                                            <small class="text-muted"><?= time_elapsed_string($row['timestamp']) ?></small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <h5>Tidak ada riwayat aktivitas</h5>
                                        <p class="text-muted">Belum ada aktivitas yang tercatat untuk filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="History pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $total_pages ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= $filter_user ? '&filter_user=' . urlencode($filter_user) : '' ?><?= $date_from ? '&date_from=' . urlencode($date_from) : '' ?><?= $date_to ? '&date_to=' . urlencode($date_to) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh untuk data real-time (optional)
        function refreshData() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }
        
        // Refresh setiap 5 menit (300000 ms) - uncomment jika diperlukan
        // setInterval(refreshData, 300000);
        
        // Filter form enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.querySelector('form');
            const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');
            
            // Auto-submit on filter change (optional)
            // filterInputs.forEach(input => {
            //     input.addEventListener('change', function() {
            //         filterForm.submit();
            //     });
            // });
        });
    </script>
</body>
</html>

<?php
// Helper function untuk menampilkan waktu yang sudah berlalu
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks from days
    $diff_w = floor($diff->d / 7);
    $diff_d = $diff->d - ($diff_w * 7);

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    foreach ($string as $k => &$v) {
        if ($k === 'w' && $diff_w) {
            $v = $diff_w . ' ' . $v . ($diff_w > 1 ? '' : '');
        } elseif ($k === 'd' && $diff_d) {
            $v = $diff_d . ' ' . $v . ($diff_d > 1 ? '' : '');
        } elseif ($k !== 'w' && $k !== 'd' && $diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>