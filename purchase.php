<?php
session_start();
include 'koneksi.php';
// Jika pengguna belum login, arahkan ke halaman login
if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini.'); window.location.href='index.php';</script>";
    exit();
}

if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Inisialisasi variabel search
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="purchaseStyle.css">
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
                    <li><a href="purchase.php" class="active"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php" class="active"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">         
            <div class="header-top">
                <div>
                    <h1 class="header-title">Purchase</h1>
                    <div class="header-subtitle">List of Products to Buy</div>
                </div>
                <div class="search-profile">
                    <form method="GET" class="d-flex">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." aria-label="Search products" class="form-control me-2" />
                        <?php if (!empty($search)): ?>
                            <a href="purchase.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
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
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php
                
                // Pastikan koneksi berhasil
                if (!$connect) {
                    die("Koneksi database gagal: " . mysqli_connect_error());
                }

                // Query dengan filter search jika ada
                $query = "SELECT 
                    p.id_pemesanan,
                    p.stat,
                    p.kode_produk,
                    p.tanggal_pesan,
                    p.tanggal_datang,
                    p.vendor,
                    p.jumlah_pesan,
                    p.jumlah_diterima,
                    pr.nama_produk,
                    pr.kategori,
                    pr.satuan,
                    pr.harga,
                    pr.jumlah_stok,
                    u.nama
                FROM pemesanan p
                LEFT JOIN produk pr ON p.kode_produk = pr.kode_produk
                LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna";

                // Tambahkan kondisi search jika ada
                if (!empty($search)) {
                    $search_escaped = mysqli_real_escape_string($connect, $search);
                    $query .= " WHERE (pr.nama_produk LIKE '%$search_escaped%' 
                                OR p.kode_produk LIKE '%$search_escaped%' 
                                OR p.vendor LIKE '%$search_escaped%')";
                }

                $query .= " ORDER BY p.stat DESC, p.tanggal_pesan DESC";

                $result = mysqli_query($connect, $query);
                
                if (!$result) {
                    die("Query error: " . mysqli_error($connect));
                }
            ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-success" onclick="window.location.href='addProduct.php'">
                    <i class="fas fa-plus me-2"></i>Tambah Pemesanan
                </button>
            </div>

            <div class="table-container">
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Aksi</th>
                                <th>ID Order</th>
                                <th>Status</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Tanggal Pesan</th>
                                <th>Tanggal Datang</th>
                                <th>Vendor</th>
                                <th>Qty Pesan</th>
                                <th>Qty Diterima</th>
                                <th>Harga Satuan</th>
                                <th>Total Nilai</th>
                                <th>Stok Saat Ini</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php 
                                    $totalNilai = ($row['harga'] ?? 0) * ($row['jumlah_diterima'] ?? 0);
                                    $statusClass = '';
                                    switch($row['stat']) {
                                        case 'Tersimpan': $statusClass = 'success'; break;
                                        case 'Pending': $statusClass = 'warning'; break;
                                        case 'Dibatalkan': $statusClass = 'danger'; break;
                                        default: $statusClass = 'secondary';
                                    }
                                    ?>
                                    <tr id="row-<?= htmlspecialchars($row['id_pemesanan']) ?>">
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-<?= $row['stat'] === 'Tersimpan' ? 'success' : 'primary' ?> btn-simpan" 
                                                onclick="simpanRow('<?= htmlspecialchars($row['id_pemesanan']) ?>')"
                                                <?= $row['stat'] === 'Tersimpan' ? 'disabled' : '' ?>
                                                title="<?= $row['stat'] === 'Tersimpan' ? 'Sudah disimpan ke inventory' : 'Simpan ke inventory' ?>"
                                            >
                                                <?= $row['stat'] === 'Tersimpan' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-save"></i>' ?>
                                            </button>
                                        </td>
                                        <td>
                                            <span class="text-primary fw-bold"><?= htmlspecialchars($row['id_pemesanan'] ?? '') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?> status-cell">
                                                <?= htmlspecialchars($row['stat'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <span class="product-code"><?= htmlspecialchars($row['kode_produk'] ?? '') ?></span>
                                                <span class="product-name"><?= htmlspecialchars($row['nama_produk'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($row['kategori'] ?? '') ?></span>
                                        </td>
                                        <td><?= $row['tanggal_pesan'] ? date('d/m/Y', strtotime($row['tanggal_pesan'])) : '-' ?></td>
                                        <td><?= $row['tanggal_datang'] ? date('d/m/Y', strtotime($row['tanggal_datang'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($row['vendor'] ?? '') ?></td>
                                        <td><?= number_format($row['jumlah_pesan'] ?? 0) ?></td>
                                        <td><?= number_format($row['jumlah_diterima'] ?? 0) ?></td>
                                        <td>Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.') ?></td>
                                        <td>
                                            <strong>Rp <?= number_format($totalNilai, 0, ',', '.') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= ($row['jumlah_stok'] ?? 0) > 0 ? 'success' : 'warning' ?>">
                                                <?= number_format($row['jumlah_stok'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-outline-info btn-action" 
                                                        onclick="lihatDetail('<?= $row['id_pemesanan'] ?>')"
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-action btn-edit" 
                                                        onclick="editPemesanan('<?= $row['id_pemesanan'] ?>')"
                                                        <?= $row['stat'] === 'Tersimpan' ? 'style="display:none"' : '' ?>
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-action" 
                                                        onclick="hapusPemesanan('<?= $row['id_pemesanan'] ?>')"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="14">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h5 class="mb-2">
                                                <?= !empty($search) ? 'Tidak ada hasil pencarian' : 'Belum ada data purchase order' ?>
                                            </h5>
                                            <p class="text-muted mb-3">
                                                <?= !empty($search) ? 'Coba kata kunci lain' : 'Mulai dengan menambahkan purchase order pertama Anda' ?>
                                            </p>
                                            <?php if (empty($search)): ?>
                                                <a href="addPurchase.php" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Tambah PO
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                function simpanRow(rowId) {
                    if (!confirm('Apakah Anda yakin ingin menyimpan data ini ke inventory? Setelah disimpan, data tidak dapat diubah.')) {
                        return;
                    }
                    
                    const row = document.getElementById(`row-${rowId}`);
                    const simpanBtn = row.querySelector('.btn-simpan');
                    const statusCell = row.querySelector('.status-cell');
                    const editBtn = row.querySelector('.btn-edit');

                    // Tampilkan loading
                    const originalText = simpanBtn.innerHTML;
                    simpanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                    simpanBtn.disabled = true;

                    // Kirim permintaan ke server
                    fetch('updateStatus.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${rowId}&stat=Tersimpan`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update tampilan jika berhasil
                            simpanBtn.innerHTML = '<i class="fas fa-check"></i>';
                            simpanBtn.className = 'btn btn-sm btn-success btn-simpan';
                            simpanBtn.disabled = true;
                            statusCell.innerHTML = 'Tersimpan';
                            statusCell.className = 'badge bg-success status-cell';
                            if (editBtn) editBtn.style.display = 'none';
                            
                            // Refresh halaman untuk update stok
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            // Rollback jika gagal
                            simpanBtn.innerHTML = originalText;
                            simpanBtn.disabled = false;
                            alert('Gagal menyimpan: ' + (data.message || 'Error tidak diketahui'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        simpanBtn.innerHTML = originalText;
                        simpanBtn.disabled = false;
                        alert('Terjadi kesalahan sistem');
                    });
                }

                function lihatDetail(id) {
                    // Implementasi modal atau redirect ke halaman detail
                    window.open(`detailPurchase.php?id=${id}`, '_blank');
                }

                function editPemesanan(id) {
                    window.location.href = `editPurchase.php?id=${id}`;
                }

                function hapusPemesanan(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        fetch('deletePurchase.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${id}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Data berhasil dihapus');
                                location.reload();
                            } else {
                                alert('Gagal menghapus: ' + (data.message || 'Error tidak diketahui'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan sistem');
                        });
                    }
                }

                // Filter dan search functionality
                document.addEventListener('DOMContentLoaded', function() {
                    // Auto submit search form on input
                    const searchInput = document.querySelector('input[name="search"]');
                    if (searchInput) {
                        let timeout;
                        searchInput.addEventListener('input', function() {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => {
                                this.form.submit();
                            }, 500); // Delay 500ms untuk menghindari terlalu banyak request
                        });
                    }
                });

                // Add this to your existing script section
                document.addEventListener('DOMContentLoaded', function() {
                    // Adjust table container width on resize
                    function adjustTableWidth() {
                        const sidebarWidth = document.querySelector('.sidebar').offsetWidth;
                        const windowWidth = window.innerWidth;
                        const tableContainer = document.querySelector('.table-container');
                        
                        if (tableContainer) {
                            tableContainer.style.maxWidth = (windowWidth - sidebarWidth - 100) + 'px';
                        }
                    }
                    
                    // Initial adjustment
                    adjustTableWidth();
                    
                    // Adjust on window resize
                    window.addEventListener('resize', adjustTableWidth);
                });
            </script>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>