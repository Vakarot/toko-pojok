<?php
session_start();
include 'koneksi.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

function formatTanggal($tanggal) {
    return date('d/m/Y', strtotime($tanggal));
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id_penjualan = $_GET['delete'];
    
    // Start transaction
    mysqli_begin_transaction($connect);
    
    try {
        // First, restore stock for each product in the sale
        $restore_query = "SELECT dp.kode_produk, dp.jumlah 
                         FROM detail_penjualan dp 
                         WHERE dp.id_penjualan = ?";
        $restore_stmt = mysqli_prepare($connect, $restore_query);
        mysqli_stmt_bind_param($restore_stmt, "s", $id_penjualan);
        mysqli_stmt_execute($restore_stmt);
        $restore_result = mysqli_stmt_get_result($restore_stmt);
        
        while ($item = mysqli_fetch_assoc($restore_result)) {
            $update_stock_query = "UPDATE produk SET jumlah_stok = jumlah_stok + ? WHERE kode_produk = ?";
            $update_stock_stmt = mysqli_prepare($connect, $update_stock_query);
            mysqli_stmt_bind_param($update_stock_stmt, "is", $item['jumlah'], $item['kode_produk']);
            mysqli_stmt_execute($update_stock_stmt);
        }
        
        // Delete detail_penjualan records
        $delete_detail_query = "DELETE FROM detail_penjualan WHERE id_penjualan = ?";
        $delete_detail_stmt = mysqli_prepare($connect, $delete_detail_query);
        mysqli_stmt_bind_param($delete_detail_stmt, "s", $id_penjualan);
        mysqli_stmt_execute($delete_detail_stmt);
        
        // Delete penjualan record
        $delete_penjualan_query = "DELETE FROM penjualan WHERE id_penjualan = ?";
        $delete_penjualan_stmt = mysqli_prepare($connect, $delete_penjualan_query);
        mysqli_stmt_bind_param($delete_penjualan_stmt, "s", $id_penjualan);
        mysqli_stmt_execute($delete_penjualan_stmt);
        
        // Commit transaction
        mysqli_commit($connect);
        $success_message = "Penjualan berhasil dihapus dan stok telah dikembalikan!";
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($connect);
        $error_message = "Gagal menghapus penjualan!";
    }
}

// Pagination settings
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get sales data with item count
$query = "SELECT 
            p.id_penjualan,
            p.tanggal_penjualan,
            p.total_harga,
            p.metode_pembayaran,
            COUNT(dp.kode_produk) as total_item,
            SUM(dp.jumlah) as total_quantity
          FROM penjualan p
          LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
          GROUP BY p.id_penjualan, p.tanggal_penjualan, p.total_harga, p.metode_pembayaran";

$count_query = "SELECT COUNT(DISTINCT p.id_penjualan) as total FROM penjualan p";

if (!empty($search)) {
    $search_condition = " HAVING p.id_penjualan LIKE ? OR p.metode_pembayaran LIKE ? OR DATE_FORMAT(p.tanggal_penjualan, '%d/%m/%Y') LIKE ?";
    $query .= $search_condition;
    
    // For count query, we need to use subquery
    $count_query = "SELECT COUNT(*) as total FROM (
                     SELECT p.id_penjualan 
                     FROM penjualan p 
                     WHERE p.id_penjualan LIKE ? OR p.metode_pembayaran LIKE ? OR DATE_FORMAT(p.tanggal_penjualan, '%d/%m/%Y') LIKE ?
                   ) as filtered";
    
    $searchParam = "%$search%";
    
    // Get total count
    $count_stmt = mysqli_prepare($connect, $count_query);
    mysqli_stmt_bind_param($count_stmt, "sss", $searchParam, $searchParam, $searchParam);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_row = mysqli_fetch_assoc($count_result);
    $total = $total_row['total'];
    
    // Get paginated data
    $query .= " ORDER BY p.tanggal_penjualan DESC LIMIT ?, ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "sssii", $searchParam, $searchParam, $searchParam, $offset, $per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // Get total count
    $count_result = mysqli_query($connect, $count_query);
    $total_row = mysqli_fetch_assoc($count_result);
    $total = $total_row['total'];
    
    // Get paginated data
    $query .= " ORDER BY p.tanggal_penjualan DESC LIMIT ?, ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Sales Detail</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="inventoryStyle.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
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
                    <li><a href="cashier.php" class="active"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php" class="active"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header-top">
            <div>
                <h1 class="header-title">Sales Detail</h1>
                <div class="header-subtitle">Sales Transaction Information</div>
            </div>
            <div class="search-profile">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." aria-label="Search sales" class="form-control me-2" />
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

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <?php if (!empty($search)): ?>
                    <small class="text-muted">Hasil pencarian untuk: "<?= htmlspecialchars($search) ?>"</small>
                <?php endif; ?>
            </div>

            <a href="cashier.php">Kembali</a>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Show:</span>
                <select class="form-select items-per-page-select" onchange="updatePerPage(this.value)">
                    <option value="10" <?= $per_page == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $per_page == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $per_page == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
        </div> -->

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-success" onclick="window.location.href='cashier.php'">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </button>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Show:</span>
                <select class="form-select items-per-page-select" onchange="updatePerPage(this.value)">
                    <option value="10" <?= $per_page == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $per_page == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $per_page == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Penjualan</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th>Total Harga</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary"><?= htmlspecialchars($data['id_penjualan']); ?></span>
                            </td>
                            <td><?= formatTanggal($data['tanggal_penjualan']); ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= $data['total_item']; ?> jenis
                                </span>
                                <small class="text-muted d-block">
                                    <?= $data['total_quantity']; ?> total qty
                                </small>
                            </td>
                            <td class="fw-bold text-success"><?= formatRupiah($data['total_harga']); ?></td>
                            <td>
                                <?php if ($data['metode_pembayaran'] == 'tunai'): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-money-bill me-1"></i>Tunai
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-qrcode me-1"></i>QRIS
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="viewPenjualan.php?id=<?= urlencode($data['id_penjualan']); ?>" class="btn btn-outline-info btn-sm" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'Owner' || $_SESSION['role'] == 'Cashier')): ?>
                                <button onclick="confirmDelete('<?= htmlspecialchars($data['id_penjualan']); ?>')" class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    <?= !empty($search) ? 'Tidak ada penjualan yang sesuai dengan pencarian.' : 'Belum ada penjualan yang tercatat.' ?>
                                </p>
                                <?php if (empty($search)): ?>
                                    <a href="cashier.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Buat Penjualan Pertama
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                <div class="pagination-info mb-2 mb-md-0">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total) ?> of <?= $total ?> entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPaginationLink(1, $per_page, $search) ?>" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPaginationLink($page - 1, $per_page, $search) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        // Show page numbers
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif;
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildPaginationLink($i, $per_page, $search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor;
                        
                        if ($end_page < $total_pages): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPaginationLink($page + 1, $per_page, $search) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPaginationLink($total_pages, $per_page, $search) ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </main>
</div>

<?php
function buildPaginationLink($page, $per_page, $search) {
    $params = ['page' => $page, 'per_page' => $per_page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    return 'riwayatPenjualan.php?' . http_build_query($params);
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
function updatePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // Reset to first page when changing items per page
    window.location.href = url.toString();
}

function confirmDelete(idPenjualan) {
    Swal.fire({
        title: 'Hapus Penjualan?',
        text: `Apakah Anda yakin ingin menghapus penjualan ${idPenjualan}? Stok produk akan dikembalikan dan tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `detailPenjualan.php?delete=${encodeURIComponent(idPenjualan)}`;
        }
    });
}
</script>

<?php if (isset($_GET['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Operasi berhasil dilakukan.',
    confirmButtonColor: '#4CAF50'
});
</script>
<?php endif; ?>

</body>
</html>