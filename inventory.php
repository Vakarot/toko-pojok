<?php
/*session_start();

// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}*/
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="inventoryStyle.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory.php" class="active"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i> Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i> Notifikasi</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h1>Inventory</h1>
                    <p>Items detail informations</p>
                </div>
                <div class="search-bar">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search">
                    </div>
                    <div class="user-info dropdown">
                        <img src="https://storage.googleapis.com/a1aa/image/zo6PP4KS4jRPqjjFfiTVERFXjtMKO3D52JBI0TsGCII.jpg" alt="User Avatar">
                        <div>
                            <p>Luthfan Kafi</p>
                            <p>Owner</p>
                        </div>
                        <div class="dropdown-menu">
                            <a href="#">Profil</a>
                            <a href="#">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            <a class="btn btn-success" href="inventoryInput.php" role="button">Tambah Produk</a>

            <?php
                include 'koneksi.php';

                function formatRupiah($angka) {
                    return "Rp " . number_format($angka, 2, ',', '.');
                }

                $query = "SELECT kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok FROM produk";
                $result = mysqli_query($connect, $query);
                ?>

                <div class="table-responsive mt-4">
                    <table class="table table-striped table-bordered">
                        <thead class="table">
                            <tr>
                                <th>Kode Produk</th>
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
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_produk']) ?></td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= htmlspecialchars($row['satuan']) ?></td>
                                <td><?= htmlspecialchars($row['kadaluwarsa']) ?></td>
                                <td><?= formatRupiah($row['harga']) ?></td>
                                <td><?= number_format($row['jumlah_stok']) ?></td>
                                <td>
                                    <a href="#">Edit</a>
                                    <a href="form_edit.php?id=<?php echo $data['id'];?>">Hapus</a>
                                </td>
                                
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>