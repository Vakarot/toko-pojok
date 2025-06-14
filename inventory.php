<?php
include 'koneksi.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

// Handle delete request
if (isset($_GET['delete'])) {
    $kode_produk = $_GET['delete'];
    $delete_query = "DELETE FROM produk WHERE kode_produk = ?";
    $delete_stmt = mysqli_prepare($connect, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "s", $kode_produk);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $success_message = "Produk berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus produk!";
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok FROM produk";

if (!empty($search)) {
    $query .= " WHERE nama_produk LIKE ? OR kode_produk LIKE ? OR kategori LIKE ?";
    $stmt = mysqli_prepare($connect, $query);
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $searchParam, $searchParam, $searchParam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($connect, $query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a href="inventory.php" class="active"><i class="fas fa-boxes"></i>Inventory</a></li>
                <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header-top">
            <div>
                <h1 class="header-title">Inventory</h1>
                <div class="header-subtitle">Items Detail Information</div>
            </div>
            <div class="search-profile">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk..." aria-label="Search products" class="form-control me-2" />
                    <!-- <button type="submit" class="btn btn-outline-success me-2">
                        <i class="fas fa-search"></i>
                    </button> -->
                    <!-- <?php if (!empty($search)): ?>
                        <a href="inventory.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?> -->
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <?php if (!empty($search)): ?>
                    <small class="text-muted">Hasil pencarian untuk: "<?= htmlspecialchars($search) ?>"</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Kadaluwarsa</th>
                        <th>Harga</th>
                        <th>Jumlah Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($data['kode_produk']); ?></td>
                            <td><?= ucfirst(htmlspecialchars($data['kategori'])); ?></span></td>
                            <td><?= htmlspecialchars($data['nama_produk']); ?></td>
                            <td><?= htmlspecialchars($data['satuan']); ?></td>
                            <td><?= htmlspecialchars($data['kadaluwarsa']); ?></td>
                            <td><?= formatRupiah($data['harga']); ?></td>
                            <td class="<?= ($data['jumlah_stok'] <= 5) ? 'stok-rendah' : ''; ?>">
                                <?= htmlspecialchars($data['jumlah_stok']); ?>
                                <?php if ($data['jumlah_stok'] <= 5): ?>
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok Rendah"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="inventoryEdit.php?kode=<?= urlencode($data['kode_produk']); ?>" class="btn btn-outline-success btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete('<?= htmlspecialchars($data['kode_produk']); ?>', '<?= htmlspecialchars($data['nama_produk']); ?>')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    <?= !empty($search) ? 'Tidak ada produk yang sesuai dengan pencarian.' : 'Belum ada produk yang ditambahkan.' ?>
                                </p>
                                <?php if (empty($search)): ?>
                                    <a href="inventoryEdit.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Tambah Produk Pertama
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete(kodeProduk, namaProduk) {
    Swal.fire({
        title: 'Hapus Produk?',
        text: `Apakah Anda yakin ingin menghapus produk "${namaProduk}"? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `inventory.php?delete=${encodeURIComponent(kodeProduk)}`;
        }
    });
}

// Show success message if redirected after successful operation
<?php if (isset($_GET['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Operasi berhasil dilakukan.',
    confirmButtonColor: '#4CAF50'
});
<?php endif; ?>
</script>
</body>
</html>