<?php
// viewPenjualan.php - Lihat detail penjualan
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: detailPenjualan.php');
    exit;
}

$id = $_GET['id'];

// Query untuk mendapatkan data penjualan dan pengguna
$query = "SELECT p.*, u.nama as nama_pengguna
          FROM penjualan p 
          LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
          WHERE p.id_penjualan = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.close();</script>";
    exit;
}

// Query untuk mendapatkan detail produk yang terjual
$detail_query = "SELECT dp.*, pr.nama_produk, pr.kategori, pr.satuan, pr.jumlah_stok
                 FROM detail_penjualan dp 
                 LEFT JOIN produk pr ON dp.kode_produk = pr.kode_produk 
                 WHERE dp.id_penjualan = ?
                 ORDER BY pr.nama_produk";
$detail_stmt = mysqli_prepare($connect, $detail_query);
mysqli_stmt_bind_param($detail_stmt, "s", $id);
mysqli_stmt_execute($detail_stmt);
$detail_result = mysqli_stmt_get_result($detail_stmt);

$total_items = 0;
$total_quantity = 0;
$detail_products = [];
while ($detail = mysqli_fetch_assoc($detail_result)) {
    $detail_products[] = $detail;
    $total_items++;
    $total_quantity += $detail['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Penjualan #<?= $data['id_penjualan'] ?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="viewPenjualanStyle.css">
</head>
<body>
<div class="page-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="card-title">Penjualan #<?= $data['id_penjualan'] ?></h1>
                <p class="card-subtitle mb-0">
                    <i class="fas fa-calendar-alt me-2"></i> 
                    <?= date('d F Y H:i', strtotime($data['tanggal_penjualan'])) ?>
                </p>
            </div>
            <span class="payment-method">
                <i class="fas fa-<?= $data['metode_pembayaran'] === 'tunai' ? 'money-bill-wave' : 'qrcode' ?> me-1"></i>
                <?= ucfirst($data['metode_pembayaran']) ?>
            </span>
        </div>
        
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-info-circle"></i>Informasi Transaksi
                        </h2>
                        <table class="info-table">
                            <tr>
                                <td>ID Penjualan</td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?= $data['id_penjualan'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Tanggal Penjualan</td>
                                <td><?= date('d F Y H:i:s', strtotime($data['tanggal_penjualan'])) ?></td>
                            </tr>
                            <tr>
                                <td>Total Harga</td>
                                <td class="fw-bold text-success">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Metode Pembayaran</td>
                                <td>
                                    <span class="payment-method">
                                        <i class="fas fa-<?= $data['metode_pembayaran'] === 'tunai' ? 'money-bill-wave' : 'qrcode' ?> me-1"></i>
                                        <?= ucfirst($data['metode_pembayaran']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Kasir</td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-user me-1"></i>
                                        <?= $data['nama_pengguna'] ?? 'Tidak diketahui' ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Item</td>
                                <td>
                                    <span class="badge badge-warning">
                                        <?= $total_items ?> jenis produk
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Kuantitas</td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= number_format($total_quantity) ?> item
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-chart-pie"></i>Ringkasan Keuangan
                        </h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="summary-card">
                                    <div class="text-center">
                                        <div class="summary-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <h4 class="summary-title">Total Penjualan</h4>
                                        <p class="summary-value text-success">
                                            Rp <?= number_format($data['total_harga'], 0, ',', '.') ?>
                                        </p>
                                        <small class="text-muted">
                                            <?= $total_items ?> produk, <?= number_format($total_quantity) ?> item
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <div class="text-center">
                                        <div class="summary-icon">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <h5 class="summary-title">Rata-rata Harga</h5>
                                        <p class="summary-value text-primary">
                                            Rp <?= number_format($data['total_harga'] / $total_quantity, 0, ',', '.') ?>
                                        </p>
                                        <small class="text-muted">per item</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <div class="text-center">
                                        <div class="summary-icon">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                        <h5 class="summary-title">Jenis Produk</h5>
                                        <p class="summary-value text-warning">
                                            <?= $total_items ?>
                                        </p>
                                        <small class="text-muted">kategori</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="divider">
            
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-shopping-cart"></i>Detail Produk Terjual
                </h2>
                
                <?php if (count($detail_products) > 0): ?>
                <div class="table-responsive">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail_products as $detail): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary">
                                        <?= htmlspecialchars($detail['kode_produk']) ?>
                                    </span>
                                </td>
                                <td class="fw-medium"><?= htmlspecialchars($detail['nama_produk']) ?></td>
                                <td>
                                    <span class="category-badge category-<?= $detail['kategori'] ?>">
                                        <?= ucfirst($detail['kategori']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($detail['satuan']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info">
                                        <?= number_format($detail['jumlah']) ?>
                                    </span>
                                </td>
                                <td class="text-end">Rp <?= number_format($detail['harga'], 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-success">
                                    Rp <?= number_format($detail['harga'] * $detail['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: var(--primary-light);">
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-center"><?= number_format($total_quantity) ?></th>
                                <th></th>
                                <th class="text-end">
                                    Rp <?= number_format($data['total_harga'], 0, ',', '.') ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada detail produk yang ditemukan.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-footer no-print d-flex justify-content-end gap-3 p-4">
            <button onclick="window.print()" class="btn-action btn-primary">
                <i class="fas fa-print me-2"></i> Cetak
            </button>
            <button onclick="window.close()" class="btn-action btn-outline-secondary">
                <i class="fas fa-times me-2"></i> Tutup
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>