<?php
// edit_purchase.php - Edit purchase order (hanya jika status masih Pending)
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna']; // Ambil ID pengguna dari session

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

function addToHistory($connect, $id_pengguna, $action) {
    $query = "INSERT INTO riwayat (id_pengguna, action, timestamp) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $id_pengguna, $action);
    return mysqli_stmt_execute($stmt);
}

// Process form update
if ($_POST) {
    try {
        $tanggal_pesan = $_POST['tanggal_pesan'];
        $tanggal_datang = $_POST['tanggal_datang'];
        $vendor = $_POST['vendor'];
        $jumlah_pesan = $_POST['jumlah_pesan'];
        $jumlah_diterima = $_POST['jumlah_diterima'];
        
        // Ambil data produk untuk history
        $kode_produk = $purchase['kode_produk'];
        $nama_produk = $purchase['nama_produk'];
        
        $queryUpdate = "UPDATE pemesanan SET tanggal_pesan=?, tanggal_datang=?, vendor=?, jumlah_pesan=?, jumlah_diterima=? WHERE id_pemesanan=?";
        $stmtUpdate = mysqli_prepare($connect, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "sssiii", $tanggal_pesan, $tanggal_datang, $vendor, $jumlah_pesan, $jumlah_diterima, $id);
        
        if (mysqli_stmt_execute($stmtUpdate)) {
            // Buat action string dengan data yang benar
            $actionEdit = "Mengupdate pemesanan: $kode_produk - $nama_produk dari vendor $vendor (Jumlah: $jumlah_pesan, Tanggal pesan: $tanggal_pesan, Tanggal datang: $tanggal_datang)";
            
            // Tambahkan ke history
            if (addToHistory($connect, $id_pengguna, $actionEdit)) {
                echo "<script>alert('Data berhasil diupdate'); window.location.href='purchase.php';</script>";
            } else {
                echo "<script>alert('Data berhasil diupdate, namun gagal menyimpan ke riwayat');</script>";
            }
        } else {
            throw new Exception("Gagal mengupdate data pemesanan");
        }
        
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
    <link rel="stylesheet" href="editPurchaseStyle.css">
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