<?php
// input_purchase.php - Form untuk input pemesanan baru

session_start();
include 'koneksi.php';

// AKTIFKAN KEMBALI PENGECEKAN SESSION
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Fungsi untuk mendapatkan semua produk yang sudah ada
function getExistingProducts($connect) {
    $query = "SELECT kode_produk, nama_produk, kategori, satuan, harga FROM produk ORDER BY nama_produk";
    $result = mysqli_query($connect, $query);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    return $products;
}

// Fungsi untuk generate kode produk otomatis
function generateProductCode($connect, $kategori) {
    $prefix = [
        'pestisida' => 'PST',
        'pupuk' => 'PPK', 
        'perlengkapan' => 'PLK',
        'bibit' => 'BBT',
        'benih' => 'BNH'
    ];
    
    $pre = $prefix[$kategori];
    $query = "SELECT kode_produk FROM produk WHERE kode_produk LIKE '$pre%' ORDER BY kode_produk DESC LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastCode = $row['kode_produk'];
        $number = intval(substr($lastCode, 3)) + 1;
    } else {
        $number = 1;
    }
    
    return $pre . sprintf('%03d', $number);
}

// Proses form submission
if ($_POST) {
    $isNewProduct = $_POST['is_new_product'] == '1';
    
    // VALIDASI SESSION ID_PENGGUNA
    if (!isset($_SESSION['id_pengguna']) || empty($_SESSION['id_pengguna'])) {
        echo json_encode(['success' => false, 'message' => 'Session tidak valid. Silakan login kembali.']);
        exit;
    }
    
    $id_pengguna = $_SESSION['id_pengguna'];
    
    // VALIDASI APAKAH ID_PENGGUNA ADA DI DATABASE
    $checkUser = "SELECT id_pengguna FROM pengguna WHERE id_pengguna = ?";
    $stmtCheck = mysqli_prepare($connect, $checkUser);
    mysqli_stmt_bind_param($stmtCheck, "i", $id_pengguna);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    
    if (mysqli_num_rows($resultCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan. Silakan login kembali.']);
        exit;
    }
    
    try {
        mysqli_begin_transaction($connect);
        
        if ($isNewProduct) {
            // Input produk baru
            $kategori = $_POST['kategori'];
            $nama_produk = $_POST['nama_produk'];
            $satuan = $_POST['satuan'];
            $kadaluwarsa = $_POST['kadaluwarsa'];
            $harga = $_POST['harga'];
            
            // Generate kode produk otomatis
            $kode_produk = generateProductCode($connect, $kategori);
            
            // Insert produk baru dengan stok awal 0
            $queryProduk = "INSERT INTO produk (kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok) 
                           VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmtProduk = mysqli_prepare($connect, $queryProduk);
            mysqli_stmt_bind_param($stmtProduk, "sssssd", $kode_produk, $kategori, $nama_produk, $satuan, $kadaluwarsa, $harga);
            mysqli_stmt_execute($stmtProduk);
            
        } else {
            // Produk yang sudah ada
            $kode_produk = $_POST['kode_produk_existing'];
        }
        
        // Insert data pemesanan
        $tanggal_pesan = $_POST['tanggal_pesan'];
        $tanggal_datang = $_POST['tanggal_datang'];
        $vendor = $_POST['vendor'];
        $jumlah_pesan = $_POST['jumlah_pesan'];
        $jumlah_diterima = $_POST['jumlah_diterima'];
        $stat = 'Pending'; // Status awal
        
        // PASTIKAN SEMUA VARIABEL TIDAK KOSONG
        if (empty($kode_produk) || empty($tanggal_pesan) || empty($tanggal_datang) || empty($vendor)) {
            throw new Exception("Data tidak lengkap");
        }
        
        $queryPemesanan = "INSERT INTO pemesanan (id_pengguna, kode_produk, tanggal_pesan, tanggal_datang, vendor, jumlah_pesan, jumlah_diterima, stat) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtPemesanan = mysqli_prepare($connect, $queryPemesanan);
        mysqli_stmt_bind_param($stmtPemesanan, "sssssiis", $id_pengguna, $kode_produk, $tanggal_pesan, $tanggal_datang, $vendor, $jumlah_pesan, $jumlah_diterima, $stat);
        
        if (!mysqli_stmt_execute($stmtPemesanan)) {
            throw new Exception("Gagal menyimpan pemesanan: " . mysqli_error($connect));
        }
        
        mysqli_commit($connect);
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan']);
        
    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

$existingProducts = getExistingProducts($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Input Purchase Order</h4>
                    <!-- DEBUG INFO - HAPUS SETELAH TESTING -->
                    <small class="text-muted">
                        User ID: <?= isset($_SESSION['id_pengguna']) ? $_SESSION['id_pengguna'] : 'TIDAK ADA' ?>
                    </small>
                </div>
                <div class="card-body">
                    <form id="purchaseForm" method="POST">
                        
                        <!-- Toggle Produk Baru/Existing -->
                        <div class="mb-3">
                            <label class="form-label">Jenis Input Produk</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_new_product" id="existing_product" value="0" checked>
                                    <label class="form-check-label" for="existing_product">
                                        Pilih Produk Existing
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_new_product" id="new_product" value="1">
                                    <label class="form-check-label" for="new_product">
                                        Tambah Produk Baru
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section untuk Produk Existing -->
                        <div id="existing_product_section">
                            <div class="mb-3">
                                <label for="kode_produk_existing" class="form-label">Pilih Produk</label>
                                <select class="form-select" id="kode_produk_existing" name="kode_produk_existing">
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($existingProducts as $product): ?>
                                    <option value="<?= $product['kode_produk'] ?>" 
                                            data-nama="<?= $product['nama_produk'] ?>"
                                            data-kategori="<?= $product['kategori'] ?>"
                                            data-satuan="<?= $product['satuan'] ?>"
                                            data-harga="<?= $product['harga'] ?>">
                                        <?= $product['kode_produk'] ?> - <?= $product['nama_produk'] ?> (<?= $product['kategori'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Info produk yang dipilih -->
                            <div id="product_info" class="alert alert-info" style="display: none;">
                                <h6>Informasi Produk:</h6>
                                <p id="info_display"></p>
                            </div>
                        </div>

                        <!-- Section untuk Produk Baru -->
                        <div id="new_product_section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kategori" class="form-label">Kategori</label>
                                        <select class="form-select" id="kategori" name="kategori">
                                            <option value="">-- Pilih Kategori --</option>
                                            <option value="pestisida">Pestisida</option>
                                            <option value="pupuk">Pupuk</option>
                                            <option value="perlengkapan">Perlengkapan</option>
                                            <option value="bibit">Bibit</option>
                                            <option value="benih">Benih</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_produk" class="form-label">Nama Produk</label>
                                        <input type="text" class="form-control" id="nama_produk" name="nama_produk">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="satuan" class="form-label">Satuan</label>
                                        <input type="text" class="form-control" id="satuan" name="satuan" placeholder="kg, liter, pcs, dll">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kadaluwarsa" class="form-label">Kadaluwarsa</label>
                                        <input type="date" class="form-control" id="kadaluwarsa" name="kadaluwarsa">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga</label>
                                        <input type="number" class="form-control" id="harga" name="harga" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Data Pemesanan -->
                        <h5>Data Pemesanan</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_pesan" class="form-label">Tanggal Pesan</label>
                                    <input type="date" class="form-control" id="tanggal_pesan" name="tanggal_pesan" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_datang" class="form-label">Tanggal Datang</label>
                                    <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" class="form-control" id="vendor" name="vendor" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_pesan" class="form-label">Jumlah Pesan</label>
                                    <input type="number" class="form-control" id="jumlah_pesan" name="jumlah_pesan" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_diterima" class="form-label">Jumlah Diterima</label>
                                    <input type="number" class="form-control" id="jumlah_diterima" name="jumlah_diterima" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan Purchase Order</button>
                            <a href="purchase.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#kode_produk_existing').select2({
        placeholder: "Cari produk...",
        allowClear: true
    });
    
    // Toggle sections
    $('input[name="is_new_product"]').change(function() {
        if ($(this).val() == '1') {
            $('#existing_product_section').hide();
            $('#new_product_section').show();
            $('#product_info').hide();
        } else {
            $('#existing_product_section').show();
            $('#new_product_section').hide();
        }
    });
    
    // Show product info when selected
    $('#kode_produk_existing').change(function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            const info = `
                <strong>Nama:</strong> ${selected.data('nama')}<br>
                <strong>Kategori:</strong> ${selected.data('kategori')}<br>
                <strong>Satuan:</strong> ${selected.data('satuan')}<br>
                <strong>Harga:</strong> Rp ${parseFloat(selected.data('harga')).toLocaleString('id-ID')}
            `;
            $('#info_display').html(info);
            $('#product_info').show();
        } else {
            $('#product_info').hide();
        }
    });
    
    // Form submission
    $('#purchaseForm').submit(function(e) {
        e.preventDefault();
        
        // Validation
        const isNewProduct = $('input[name="is_new_product"]:checked').val() == '1';
        
        if (!isNewProduct && !$('#kode_produk_existing').val()) {
            alert('Silakan pilih produk terlebih dahulu');
            return;
        }
        
        if (isNewProduct) {
            if (!$('#kategori').val() || !$('#nama_produk').val() || !$('#satuan').val() || !$('#harga').val()) {
                alert('Semua field produk baru harus diisi');
                return;
            }
        }
        
        // Submit via AJAX
        $.ajax({
            url: '',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Data berhasil disimpan!');
                    window.location.href = 'purchase.php';
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan sistem');
            }
        });
    });
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_pesan').val(today);
});
</script>

</body>
</html>