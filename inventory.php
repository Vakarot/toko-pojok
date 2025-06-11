<?php
include 'koneksi.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

$query = "SELECT kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok FROM produk";
$result = mysqli_query($connect, $query);
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
                <div class="header-subtitle">Informasi produk dalam stok</div>
            </div>
            <div class="search-profile">
                <input type="search" placeholder="Cari produk..." aria-label="Search products" />
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
                    <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($data['kode_produk']); ?></td>
                        <td><?= htmlspecialchars($data['kategori']); ?></td>
                        <td><?= htmlspecialchars($data['nama_produk']); ?></td>
                        <td><?= htmlspecialchars($data['satuan']); ?></td>
                        <td><?= htmlspecialchars($data['kadaluwarsa']); ?></td>
                        <td><?= formatRupiah($data['harga']); ?></td>
                        <td class="<?= ($data['jumlah_stok'] <= 5) ? 'stok-rendah' : ''; ?>">
                            <?= htmlspecialchars($data['jumlah_stok']); ?>
                        </td>
                        <td>
                            <a href="inventoryEdit.php" class="btn btn-outline-success btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-outline-danger btn-sm" title="Delete" onclick="return confirm('Yakin hapus produk ini?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>