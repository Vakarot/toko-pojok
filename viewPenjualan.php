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
    <title>Detail Penjualan #<?= $data['id_penjualan'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
            color: white;
            padding: 2rem;
            border: none;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
        }

        .badge-success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
        }

        .badge-info {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        .section-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .section-title i {
            color: #667eea;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            vertical-align: top;
        }

        .info-table td:first-child {
            font-weight: 500;
            color: #555;
            width: 35%;
        }

        .info-table td:last-child {
            color: #333;
            font-weight: 400;
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .summary-title {
            font-size: 1rem;
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .divider {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent);
            margin: 2rem 0;
        }

        .btn-action {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-print {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .btn-print:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
        }

        .btn-close {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }

        .btn-close:hover {
            background: linear-gradient(135deg, #d32f2f, #b71c1c);
        }

        .product-table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .product-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 500;
            padding: 1rem;
            border: none;
        }

        .product-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            vertical-align: middle;
        }

        .product-table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge-category {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .category-pestisida { background: #e3f2fd; color: #1976d2; }
        .category-pupuk { background: #f3e5f5; color: #7b1fa2; }
        .category-perlengkapan { background: #e8f5e8; color: #388e3c; }
        .category-bibit { background: #fff3e0; color: #f57c00; }
        .category-benih { background: #fce4ec; color: #c2185b; }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .method-tunai {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .method-qris {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        @media print {
            body {
                background: white !important;
                font-size: 12px;
            }
            .no-print {
                display: none !important;
            }
            .glass-card {
                background: white !important;
                backdrop-filter: none !important;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .card-header {
                background: #f8f9fa !important;
                color: #333 !important;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .card-header {
                padding: 1.5rem;
            }
            .card-title {
                font-size: 1.5rem;
            }
            .info-table td:first-child {
                width: 40%;
            }
        }
    </style>
</head>
<body>
<div class="detail-container">
    <div class="glass-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title">Penjualan #<?= $data['id_penjualan'] ?></h2>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem; opacity: 0.9;">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <?= date('d F Y H:i', strtotime($data['tanggal_penjualan'])) ?>
                </p>
            </div>
            <span class="payment-method method-<?= $data['metode_pembayaran'] ?>">
                <i class="fas fa-<?= $data['metode_pembayaran'] === 'tunai' ? 'money-bill' : 'qrcode' ?> me-1"></i>
                <?= ucfirst($data['metode_pembayaran']) ?>
            </span>
        </div>
        
        <div class="card-body p-3 p-lg-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-3">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>Informasi Transaksi
                        </h3>
                        <table class="info-table">
                            <tr>
                                <td>ID Penjualan</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">
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
                                    <span class="payment-method method-<?= $data['metode_pembayaran'] ?>">
                                        <i class="fas fa-<?= $data['metode_pembayaran'] === 'tunai' ? 'money-bill' : 'qrcode' ?> me-1"></i>
                                        <?= ucfirst($data['metode_pembayaran']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Kasir</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="fas fa-user me-1"></i>
                                        <?= $data['nama_pengguna'] ?? 'Tidak diketahui' ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Item</td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning">
                                        <?= $total_items ?> jenis produk
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Kuantitas</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <?= number_format($total_quantity) ?> item
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="p-3">
                        <h3 class="section-title">
                            <i class="fas fa-chart-pie me-2"></i>Ringkasan Keuangan
                        </h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="summary-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-receipt text-success" style="font-size: 2.5rem;"></i>
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
                            <div class="col-6">
                                <div class="summary-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-calculator text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <h5 class="summary-title">Rata-rata Harga</h5>
                                        <p class="summary-value text-primary" style="font-size: 1.2rem;">
                                            Rp <?= number_format($data['total_harga'] / $total_quantity, 0, ',', '.') ?>
                                        </p>
                                        <small class="text-muted">per item</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-boxes text-warning" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <h5 class="summary-title">Jenis Produk</h5>
                                        <p class="summary-value text-warning" style="font-size: 1.2rem;">
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
            
            <div class="row">
                <div class="col-12">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-cart me-2"></i>Detail Produk Terjual
                    </h3>
                    
                    <?php if (count($detail_products) > 0): ?>
                    <div class="product-table">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Kode Produk</th>
                                    <th style="width: 25%;">Nama Produk</th>
                                    <th style="width: 15%;">Kategori</th>
                                    <th style="width: 10%;">Satuan</th>
                                    <th style="width: 10%;">Jumlah</th>
                                    <th style="width: 12%;">Harga Satuan</th>
                                    <th style="width: 13%;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail_products as $detail): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <?= htmlspecialchars($detail['kode_produk']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-medium"><?= htmlspecialchars($detail['nama_produk']) ?></td>
                                    <td>
                                        <span class="badge-category category-<?= $detail['kategori'] ?>">
                                            <?= ucfirst($detail['kategori']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($detail['satuan']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info">
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
                                <tr style="background: rgba(102, 126, 234, 0.1);">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-center"><?= number_format($total_quantity) ?></th>
                                    <th></th>
                                    <th class="text-end text-success">
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