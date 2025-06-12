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
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #218838;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #2f4f4f;
            --light-text: #6c757d;
            --success-gradient: linear-gradient(135deg, #28a745, #218838);
            --warning-gradient: linear-gradient(135deg, #ffc107, #fd7e14);
            --danger-gradient: linear-gradient(135deg, #dc3545, #c82333);
            --info-gradient: linear-gradient(135deg, #17a2b8, #138496);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: var(--dark-text);
            line-height: 1.6;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }

        .detail-container {
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
        }

        .card-header {
            background: var(--success-gradient);
            color: white;
            padding: 1.75rem 2rem;
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            transform: rotate(30deg);
        }

        .card-title {
            font-weight: 600;
            margin-bottom: 0;
            position: relative;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
            position: relative;
            z-index: 1;
        }

        .badge-success { background: var(--success-gradient); }
        .badge-warning { background: var(--warning-gradient); }
        .badge-danger { background: var(--danger-gradient); }
        .badge-secondary { background: var(--secondary-color); }
        .badge-info { background: var(--info-gradient); }

        .section-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
            border-radius: 3px;
        }

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .info-table tr td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            background-color: rgba(248, 249, 250, 0.5);
            border-radius: 8px;
            transition: var(--transition);
        }

        .info-table tr:hover td {
            background-color: rgba(233, 236, 239, 0.7);
            transform: translateX(5px);
        }

        .info-table tr td:first-child {
            width: 35%;
            font-weight: 500;
            color: var(--dark-text);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .info-table tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .summary-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            height: 100%;
            background: white;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-color);
        }

        .summary-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(40, 167, 69, 0.15);
        }

        .summary-card .card-body {
            padding: 1.75rem;
            position: relative;
            z-index: 1;
        }

        .summary-title {
            font-size: 1rem;
            font-weight: 500;
            color: var(--light-text);
            margin-bottom: 0.75rem;
        }

        .summary-value {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(40, 167, 69, 0.3), transparent);
            margin: 2.5rem 0;
            border: none;
        }

        .btn-action {
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-print {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-close {
            background: white;
            color: var(--dark-text);
            box-shadow: var(--shadow-sm);
            border: 1px solid #e9ecef;
        }

        .btn-close:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: var(--dark-text);
        }

        .badge-stock {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .detail-container { margin: 0; max-width: 100%; }
            .glass-card { box-shadow: none; border: none; }
        }

        @media (max-width: 992px) {
            .detail-container { padding: 0 1rem; }
            .card-header { padding: 1.5rem; }
            .card-title { font-size: 1.5rem; }
            .summary-value { font-size: 1.5rem; }
        }

        @media (max-width: 768px) {
            .card-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .section-title { font-size: 1.25rem; }
            .info-table tr td { padding: 0.75rem; }
            .btn-action { padding: 0.65rem 1.25rem; }
        }

        @media (max-width: 576px) {
            .detail-container { margin: 1.5rem auto; }
            .card-header { padding: 1.25rem; }
            .card-title { font-size: 1.35rem; }
            .section-title { font-size: 1.15rem; }
            .summary-card .card-body { padding: 1.25rem; }
        }
    </style>
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
                                <td><?= number_format($data['jumlah_pesan']) ?> <?= $data['satuan'] ?></td>
                            </tr>
                            <tr>
                                <td>Jumlah Diterima</td>
                                <td>
                                    <?= number_format($data['jumlah_diterima']) ?> <?= $data['satuan'] ?>
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
                                <i class="fas fa-balance-scale <?= ($data['jumlah_pesan'] - $data['jumlah_diterima']) != 0 ? 'text-warning' : 'text-secondary' ?>" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="summary-title">Selisih Kuantitas</h4>
                            <p class="summary-value <?= ($data['jumlah_pesan'] - $data['jumlah_diterima']) > 0 ? 'text-warning' : 'text-success' ?>">
                                <?= number_format(abs($data['jumlah_pesan'] - $data['jumlah_diterima'])) ?>
                            </p>
                            <small class="text-muted">
                                <?= ($data['jumlah_pesan'] - $data['jumlah_diterima']) > 0 ? 'Kurang diterima' : ($data['jumlah_pesan'] - $data['jumlah_diterima']) < 0 ? 'Lebih diterima' : 'Tepat sesuai' ?>
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