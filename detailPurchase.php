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
    <title>Detail Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .detail-card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .status-badge { font-size: 1.1em; padding: 8px 15px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card detail-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Purchase Order #<?= $data['id_pemesanan'] ?></h4>
                        <span class="badge status-badge bg-<?= $data['stat'] === 'Tersimpan' ? 'success' : 'warning' ?>">
                            <?= $data['stat'] ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informasi Produk</h5>
                            <table class="table table-borderless">
                                <tr><td><strong>Kode Produk:</strong></td><td><?= $data['kode_produk'] ?></td></tr>
                                <tr><td><strong>Nama Produk:</strong></td><td><?= $data['nama_produk'] ?></td></tr>
                                <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-info"><?= $data['kategori'] ?></span></td></tr>
                                <tr><td><strong>Satuan:</strong></td><td><?= $data['satuan'] ?></td></tr>
                                <tr><td><strong>Harga Satuan:</strong></td><td>Rp <?= number_format($data['harga'], 0, ',', '.') ?></td></tr>
                                <tr><td><strong>Stok Saat Ini:</strong></td><td><?= number_format($data['jumlah_stok']) ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Informasi Pemesanan</h5>
                            <table class="table table-borderless">
                                <tr><td><strong>Tanggal Pesan:</strong></td><td><?= date('d/m/Y', strtotime($data['tanggal_pesan'])) ?></td></tr>
                                <tr><td><strong>Tanggal Datang:</strong></td><td><?= date('d/m/Y', strtotime($data['tanggal_datang'])) ?></td></tr>
                                <tr><td><strong>Vendor:</strong></td><td><?= $data['vendor'] ?></td></tr>
                                <tr><td><strong>Jumlah Pesan:</strong></td><td><?= number_format($data['jumlah_pesan']) ?></td></tr>
                                <tr><td><strong>Jumlah Diterima:</strong></td><td><?= number_format($data['jumlah_diterima']) ?></td></tr>
                                <tr><td><strong>Dibuat oleh:</strong></td><td><?= $data['nama'] ?></td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Ringkasan Finansial</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6>Total Nilai Pesanan</h6>
                                            <h4 class="text-primary">Rp <?= number_format($data['harga'] * $data['jumlah_pesan'], 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6>Total Nilai Diterima</h6>
                                            <h4 class="text-success">Rp <?= number_format($data['harga'] * $data['jumlah_diterima'], 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6>Selisih</h6>
                                            <h4 class="<?= ($data['jumlah_pesan'] - $data['jumlah_diterima']) > 0 ? 'text-warning' : 'text-success' ?>">
                                                <?= ($data['jumlah_pesan'] - $data['jumlah_diterima']) ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer no-print">
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="window.close()" class="btn btn-primary">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>