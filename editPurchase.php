<?php
// edit_purchase.php - Edit purchase order (hanya jika status masih Pending)
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: purchase.php');
    exit;
}

$id = $_GET['id'];

// Get purchase data
$query = "SELECT p.*, pr.nama_produk, pr.kategori, pr.satuan, pr.harga, pr.jumlah_stok 
          FROM pemesanan p 
          LEFT JOIN produk pr ON p.kode_produk = pr.kode_produk 
          WHERE p.id_pemesanan = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$purchase = mysqli_fetch_assoc($result);

if (!$purchase) {
    echo "<script>alert('Data tidak ditemukan'); window.location.href='purchase.php';</script>";
    exit;
}

if ($purchase['stat'] === 'Tersimpan') {
    echo "<script>alert('Data yang sudah tersimpan tidak dapat diedit'); window.location.href='purchase.php';</script>";
    exit;
}

// Process form update
if ($_POST) {
    try {
        $tanggal_pesan = $_POST['tanggal_pesan'];
        $tanggal_datang = $_POST['tanggal_datang'];
        $vendor = $_POST['vendor'];
        $jumlah_pesan = $_POST['jumlah_pesan'];
        $jumlah_diterima = $_POST['jumlah_diterima'];
        
        $queryUpdate = "UPDATE pemesanan SET tanggal_pesan=?, tanggal_datang=?, vendor=?, jumlah_pesan=?, jumlah_diterima=? WHERE id_pemesanan=?";
        $stmtUpdate = mysqli_prepare($connect, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "sssiid", $tanggal_pesan, $tanggal_datang, $vendor, $jumlah_pesan, $jumlah_diterima, $id);
        mysqli_stmt_execute($stmtUpdate);
        
        echo "<script>alert('Data berhasil diupdate'); window.location.href='purchase.php';</script>";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Purchase Order #<?= $purchase['id_pemesanan'] ?></title>
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
            max-width: 1000px;
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

        .form-label {
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
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

        .btn-primary {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--dark-text);
            box-shadow: var(--shadow-sm);
            border: 1px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: var(--dark-text);
        }

        .product-info-card {
            background-color: rgba(248, 249, 250, 0.7);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }

        .product-info-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-info-item {
            margin-bottom: 0.5rem;
        }

        .product-info-label {
            font-weight: 500;
            color: var(--secondary-color);
        }

        .product-info-value {
            font-weight: 400;
        }

        @media (max-width: 992px) {
            .detail-container { padding: 0 1rem; }
            .card-header { padding: 1.5rem; }
            .card-title { font-size: 1.5rem; }
        }

        @media (max-width: 768px) {
            .card-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .section-title { font-size: 1.25rem; }
        }

        @media (max-width: 576px) {
            .detail-container { margin: 1.5rem auto; }
            .card-header { padding: 1.25rem; }
            .card-title { font-size: 1.35rem; }
            .section-title { font-size: 1.15rem; }
        }
    </style>
</head>
<body>
<div class="detail-container">
    <div class="glass-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title">Edit Purchase Order #<?= $purchase['id_pemesanan'] ?></h2>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem; opacity: 0.9;">
                    <i class="fas fa-edit me-1"></i> 
                    Status: <?= $purchase['stat'] ?>
                </p>
            </div>
            <span class="badge status-badge badge-warning">
                <i class="fas fa-pencil-alt me-1"></i>
                Edit Mode
            </span>
        </div>
        
        <div class="card-body p-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <!-- Product Info Card -->
                <div class="product-info-card">
                    <h5 class="product-info-title">
                        <i class="fas fa-box-open me-2"></i>Informasi Produk
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Kode Produk:</span>
                                <span class="product-info-value d-block"><?= $purchase['kode_produk'] ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Nama Produk:</span>
                                <span class="product-info-value d-block"><?= $purchase['nama_produk'] ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Kategori:</span>
                                <span class="product-info-value d-block">
                                    <span class="badge badge-info"><?= $purchase['kategori'] ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Harga Satuan:</span>
                                <span class="product-info-value d-block">Rp <?= number_format($purchase['harga'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Stok Saat Ini:</span>
                                <span class="product-info-value d-block">
                                    <span class="badge bg-<?= $purchase['jumlah_stok'] > 0 ? 'success' : 'warning' ?>">
                                        <?= number_format($purchase['jumlah_stok']) ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="product-info-item">
                                <span class="product-info-label">Satuan:</span>
                                <span class="product-info-value d-block"><?= $purchase['satuan'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Editable Purchase Info -->
                <h3 class="section-title">
                    <i class="fas fa-edit me-2"></i>Edit Informasi Pemesanan
                </h3>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_pesan" class="form-label">Tanggal Pesan</label>
                            <input type="date" class="form-control" id="tanggal_pesan" name="tanggal_pesan" 
                                   value="<?= $purchase['tanggal_pesan'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_datang" class="form-label">Tanggal Datang</label>
                            <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang" 
                                   value="<?= $purchase['tanggal_datang'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" class="form-control" id="vendor" name="vendor" 
                                   value="<?= htmlspecialchars($purchase['vendor']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jumlah_pesan" class="form-label">Jumlah Pesan (<?= $purchase['satuan'] ?>)</label>
                            <input type="number" class="form-control" id="jumlah_pesan" name="jumlah_pesan" 
                                   value="<?= $purchase['jumlah_pesan'] ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jumlah_diterima" class="form-label">Jumlah Diterima (<?= $purchase['satuan'] ?>)</label>
                            <input type="number" class="form-control" id="jumlah_diterima" name="jumlah_diterima" 
                                   value="<?= $purchase['jumlah_diterima'] ?>" min="0">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="submit" class="btn-action btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="purchase.php" class="btn-action btn-secondary">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set minimum date for tanggal_datang to be >= tanggal_pesan
    document.getElementById('tanggal_pesan').addEventListener('change', function() {
        document.getElementById('tanggal_datang').min = this.value;
    });
    
    // Initialize the min date if tanggal_pesan already has value
    if(document.getElementById('tanggal_pesan').value) {
        document.getElementById('tanggal_datang').min = document.getElementById('tanggal_pesan').value;
    }
</script>
</body>
</html>