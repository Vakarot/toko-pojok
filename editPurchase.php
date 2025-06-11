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
$query = "SELECT p.*, pr.nama_produk, pr.kategori 
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
    <title>Edit Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Purchase Order #<?= $purchase['id_pemesanan'] ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <!-- Info Produk (Read Only) -->
                        <div class="alert alert-info">
                            <h6>Informasi Produk:</h6>
                            <strong>Kode:</strong> <?= $purchase['kode_produk'] ?><br>
                            <strong>Nama:</strong> <?= $purchase['nama_produk'] ?><br>
                            <strong>Kategori:</strong> <?= $purchase['kategori'] ?>
                        </div>
                        
                        <!-- Data Pemesanan yang bisa diedit -->
                        <div class="row">
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
                        </div>
                        
                        <div class="mb-3">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" class="form-control" id="vendor" name="vendor" 
                                   value="<?= htmlspecialchars($purchase['vendor']) ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_pesan" class="form-label">Jumlah Pesan</label>
                                    <input type="number" class="form-control" id="jumlah_pesan" name="jumlah_pesan" 
                                           value="<?= $purchase['jumlah_pesan'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_diterima" class="form-label">Jumlah Diterima</label>
                                    <input type="number" class="form-control" id="jumlah_diterima" name="jumlah_diterima" 
                                           value="<?= $purchase['jumlah_diterima'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update Purchase Order</button>
                            <a href="purchase.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>