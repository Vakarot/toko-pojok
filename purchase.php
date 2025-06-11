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
    <title>Purchase</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="purchaseStyle.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="purchase.php" class="active"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
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
                    <h1>Purchase</h1>
                    <p>List of Products to Buy</p>
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
            <div>
                <button class="btn btn-primary" onclick="window.location.href='addProduct.php'">Tambah Pemesanan</button>
            </div>

            <?php

            include 'koneksi.php';
            // Pastikan koneksi berhasil
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
            LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
            ORDER BY p.stat DESC";

            $result = mysqli_query($connect, $query);
            ?>

            <!-- Updated table structure -->
            <div class="table-responsive mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Daftar Purchase Order</h4>
                    <a href="addProduct.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah PO
                    </a>
                </div>
                
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
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
                            <th>Operasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                $totalNilai = $row['harga'] * $row['jumlah_diterima'];
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
                                            class="btn btn-sm btn-<?= $row['stat'] === 'Tersimpan' ? 'success' : 'outline-primary' ?> btn-simpan" 
                                            onclick="simpanRow('<?= htmlspecialchars($row['id_pemesanan']) ?>')"
                                            <?= $row['stat'] === 'Tersimpan' ? 'disabled' : '' ?>
                                            title="<?= $row['stat'] === 'Tersimpan' ? 'Sudah disimpan ke inventory' : 'Simpan ke inventory' ?>"
                                        >
                                            <?= $row['stat'] === 'Tersimpan' ? '<i class="fas fa-check"></i> Disimpan' : '<i class="fas fa-save"></i> Simpan' ?>
                                        </button>
                                    </td>
                                    <td><?= htmlspecialchars($row['id_pemesanan']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $statusClass ?> status-cell">
                                            <?= htmlspecialchars($row['stat']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($row['kode_produk']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['nama_produk']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($row['kategori']) ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pesan'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_datang'])) ?></td>
                                    <td><?= htmlspecialchars($row['vendor']) ?></td>
                                    <td><?= number_format($row['jumlah_pesan']) ?></td>
                                    <td><?= number_format($row['jumlah_diterima']) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <strong>Rp <?= number_format($totalNilai, 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['jumlah_stok'] > 0 ? 'success' : 'warning' ?>">
                                            <?= number_format($row['jumlah_stok']) ?>
                                        </span>
                                    </td>
                                    <td class="aksi-cell">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info btn-lihat" 
                                                    onclick="lihatDetail('<?= $row['id_pemesanan'] ?>')"
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning btn-edit" 
                                                    onclick="editPemesanan('<?= $row['id_pemesanan'] ?>')"
                                                    <?= $row['stat'] === 'Tersimpan' ? 'style="display:none"' : '' ?>
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-hapus" 
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
                                <td colspan="14" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada data purchase order</p>
                                        <a href="input_purchase.php" class="btn btn-primary">Tambah PO Pertama</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                        simpanBtn.innerHTML = '<i class="fas fa-check"></i> Disimpan';
                        simpanBtn.className = 'btn btn-sm btn-success btn-simpan';
                        statusCell.innerHTML = 'Tersimpan';
                        statusCell.className = 'badge bg-success status-cell';
                        editBtn.style.display = 'none';
                        
                        // Refresh halaman untuk update stok
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        // Rollback jika gagal
                        simpanBtn.innerHTML = originalText;
                        simpanBtn.disabled = false;
                        alert('Gagal menyimpan: ' + data.message);
                    }
                })
                .catch(error => {
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
                            alert('Gagal menghapus: ' + data.message);
                        }
                    });
                }
            }

            // Filter dan search functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Add search functionality
                const searchInput = document.querySelector('.search-input input');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const rows = document.querySelectorAll('tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                }
            });
            </script>
        </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
