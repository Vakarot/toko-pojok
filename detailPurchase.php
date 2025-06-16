<?php
// detail_purchase.php - Lihat detail purchase order
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: purchase.php');
    exit;
}

$id = $_GET['id'];

$query = "SELECT p.*, pr.nama_produk, pr.kategori, pr.satuan, pr.harga, pr.jumlah_stok, u.nama
          FROM pemesanan p 
          LEFT JOIN produk pr ON p.kode_produk = pr.kode_produk 
          LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
          WHERE p.id_pemesanan = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.close();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Purchase Order #<?= $data['id_pemesanan'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="detailPurchaseStyle.css">
</head>
<body>
<div class="detail-container">
    <div class="glass-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title">Purchase Order #<?= $data['id_pemesanan'] ?></h2>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem; opacity: 0.9;">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <?= date('d F Y', strtotime($data['tanggal_pesan'])) ?>
                </p>
            </div>
            <span class="badge status-badge badge-<?= 
                $data['stat'] === 'Tersimpan' ? 'success' : 
                ($data['stat'] === 'Pending' ? 'warning' : 
                ($data['stat'] === 'Dibatalkan' ? 'danger' : 'secondary')) 
            ?>">
                <i class="fas fa-<?= 
                    $data['stat'] === 'Tersimpan' ? 'check-circle' : 
                    ($data['stat'] === 'Pending' ? 'clock' : 
                    ($data['stat'] === 'Dibatalkan' ? 'times-circle' : 'info-circle')) 
                ?> me-1"></i>
                <?= $data['stat'] ?>
            </span>
        </div>
        
        <div class="card-body p-3 p-lg-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-3">
                        <h3 class="section-title">
                            <i class="fas fa-box-open me-2"></i>Informasi Produk
                        </h3>
                        <table class="info-table">
                            <tr>
                                <td>Kode Produk</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">
                                        <?= $data['kode_produk'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Nama Produk</td>
                                <td><?= $data['nama_produk'] ?></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><span class="badge badge-info"><?= $data['kategori'] ?></span></td>
                            </tr>
                            <tr>
                                <td>Satuan</td>
                                <td><?= $data['satuan'] ?></td>
                            </tr>
                            <tr>
                                <td>Harga Satuan</td>
                                <td class="fw-bold">Rp <?= number_format($data['harga'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Stok Saat Ini</td>
                                <td>
                                    <span class="badge-stock bg-<?= $data['jumlah_stok'] > 0 ? 'success' : 'warning' ?>-subtle text-<?= $data['jumlah_stok'] > 0 ? 'success' : 'warning' ?>">
                                        <i class="fas fa-<?= $data['jumlah_stok'] > 0 ? 'check' : 'exclamation-triangle' ?> me-1"></i>
                                        <?= number_format($data['jumlah_stok']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="p-3">
                        <h3 class="section-title">
                            <i class="fas fa-truck me-2"></i>Informasi Pemesanan
                        </h3>
                        <table class="info-table">
                            <tr>
                                <td>Tanggal Pesan</td>
                                <td><?= date('d F Y H:i', strtotime($data['tanggal_pesan'])) ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal Datang</td>
                                <td>
                                    <?= date('d F Y', strtotime($data['tanggal_datang'])) ?>
                                    <?php if ($data['tanggal_datang'] > date('Y-m-d')): ?>
                                        <span class="badge bg-warning-subtle text-warning ms-2">Akan Datang</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Vendor</td>
                                <td><?= $data['vendor'] ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah Pesan</td>
                                <td><?= number_format($data['jumlah_pesan']) ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah Diterima</td>
                                <td>
                                    <?= number_format($data['jumlah_diterima']) ?>
                                    <?php if ($data['jumlah_diterima'] < $data['jumlah_pesan']): ?>
                                        <span class="badge bg-danger-subtle text-danger ms-2">Kurang <?= number_format($data['jumlah_pesan'] - $data['jumlah_diterima']) ?></span>
                                    <?php elseif ($data['jumlah_diterima'] > $data['jumlah_pesan']): ?>
                                        <span class="badge bg-success-subtle text-success ms-2">Lebih <?= number_format($data['jumlah_diterima'] - $data['jumlah_pesan']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Dibuat oleh</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="fas fa-user me-1"></i>
                                        <?= $data['nama'] ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <hr class="divider">
            
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>Ringkasan Finansial
                    </h3>
                </div>
                
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-file-invoice-dollar text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="summary-title">Total Nilai Pesanan</h4>
                            <p class="summary-value text-primary">
                                Rp <?= number_format($data['harga'] * $data['jumlah_pesan'], 0, ',', '.') ?>
                            </p>
                            <small class="text-muted d-block"><?= number_format($data['jumlah_pesan']) ?> x Rp <?= number_format($data['harga'], 0, ',', '.') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="summary-title">Total Nilai Diterima</h4>
                            <p class="summary-value text-success">
                                Rp <?= number_format($data['harga'] * $data['jumlah_diterima'], 0, ',', '.') ?>
                            </p>
                            <small class="text-muted d-block"><?= number_format($data['jumlah_diterima']) ?> x Rp <?= number_format($data['harga'], 0, ',', '.') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-balance-scale <?= (($data['jumlah_pesan'] - $data['jumlah_diterima']) != 0) ? 'text-warning' : 'text-secondary' ?>" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="summary-title">Selisih Kuantitas</h4>
                            <p class="summary-value <?= (($data['jumlah_pesan'] - $data['jumlah_diterima']) > 0) ? 'text-warning' : 'text-success' ?>">
                                <?= number_format(abs($data['jumlah_pesan'] - $data['jumlah_diterima'])) ?>
                            </p>
                            <small class="text-muted">
                                <?= (($data['jumlah_pesan'] - $data['jumlah_diterima']) > 0) ? 'Kurang diterima' : ((($data['jumlah_pesan'] - $data['jumlah_diterima']) < 0) ? 'Lebih diterima' : 'Tepat sesuai') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer no-print d-flex justify-content-end gap-3 p-4">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i> Cetak
            </button>
            <button onclick="window.close()" class="btn-action btn-close">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>