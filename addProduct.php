<?php
// addProduct.php 

session_start();
include 'koneksi.php';

// AKTIFKAN KEMBALI PENGECEKAN SESSION
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Fungsi untuk mencatat riwayat aktivitas
function addToHistory($connect, $id_pengguna, $action) {
    $query = "INSERT INTO riwayat (id_pengguna, action, timestamp) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $id_pengguna, $action);
    return mysqli_stmt_execute($stmt);
}

// Fungsi untuk mendapatkan nama pengguna berdasarkan ID
function getUserName($connect, $id_pengguna) {
    $query = "SELECT nama FROM pengguna WHERE id_pengguna = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "s", $id_pengguna);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['nama'] : 'Unknown User';
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
    $checkUser = "SELECT id_pengguna, nama FROM pengguna WHERE id_pengguna = ?";
    $stmtCheck = mysqli_prepare($connect, $checkUser);
    mysqli_stmt_bind_param($stmtCheck, "s", $id_pengguna);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    
    if (mysqli_num_rows($resultCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan. Silakan login kembali.']);
        exit;
    }
    
    $userData = mysqli_fetch_assoc($resultCheck);
    $userName = $userData['nama'];
    
    try {
        mysqli_begin_transaction($connect);
        
        if ($isNewProduct) {
            // Input produk baru
            $kategori = $_POST['kategori'];
            $nama_produk = $_POST['nama_produk'];
            $satuan = $_POST['satuan'];
            $kadaluwarsa = $_POST['kadaluwarsa'];
            $harga = $_POST['harga'];
            
            // Validasi data produk baru
            if (empty($kategori) || empty($nama_produk) || empty($satuan) || empty($harga)) {
                throw new Exception("Data produk baru tidak lengkap");
            }
            
            // Generate kode produk otomatis
            $kode_produk = generateProductCode($connect, $kategori);
            
            // Insert produk baru dengan stok awal 0
            $queryProduk = "INSERT INTO produk (kode_produk, kategori, nama_produk, satuan, kadaluwarsa, harga, jumlah_stok) 
                           VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmtProduk = mysqli_prepare($connect, $queryProduk);
            mysqli_stmt_bind_param($stmtProduk, "sssssd", $kode_produk, $kategori, $nama_produk, $satuan, $kadaluwarsa, $harga);
            
            if (!mysqli_stmt_execute($stmtProduk)) {
                throw new Exception("Gagal menyimpan produk baru: " . mysqli_error($connect));
            }
            
            // Catat riwayat penambahan produk baru
            $actionNewProduct = "Menambahkan produk baru: $kode_produk - $nama_produk (Kategori: $kategori, Harga: Rp " . number_format($harga, 0, ',', '.') . ")";
            addToHistory($connect, $id_pengguna, $actionNewProduct);
            
        } else {
            // Produk yang sudah ada
            $kode_produk = $_POST['kode_produk_existing'];
            
            // Validasi kode produk existing
            if (empty($kode_produk)) {
                throw new Exception("Kode produk tidak valid. Silakan pilih produk.");
            }
            
            // Validasi apakah produk benar-benar ada di database
            $queryCheckProduct = "SELECT kode_produk, nama_produk FROM produk WHERE kode_produk = ?";
            $stmtCheckProduct = mysqli_prepare($connect, $queryCheckProduct);
            mysqli_stmt_bind_param($stmtCheckProduct, "s", $kode_produk);
            mysqli_stmt_execute($stmtCheckProduct);
            $resultCheckProduct = mysqli_stmt_get_result($stmtCheckProduct);
            
            if (mysqli_num_rows($resultCheckProduct) == 0) {
                throw new Exception("Produk dengan kode $kode_produk tidak ditemukan di database.");
            }
            
            // Ambil nama produk untuk riwayat
            $productData = mysqli_fetch_assoc($resultCheckProduct);
            $nama_produk = $productData['nama_produk'];
        }
        
        // Validasi data pemesanan
        $tanggal_pesan = $_POST['tanggal_pesan'];
        $tanggal_datang = $_POST['tanggal_datang'];
        $vendor = $_POST['vendor'];
        $jumlah_pesan = $_POST['jumlah_pesan'];
        $jumlah_diterima = $_POST['jumlah_diterima'];
        $stat = 'Pending'; // Status awal
        
        // VALIDASI SEMUA DATA PEMESANAN
        if (empty($kode_produk) || empty($tanggal_pesan) || empty($tanggal_datang) || empty($vendor) || empty($jumlah_pesan)) {
            throw new Exception("Data pemesanan tidak lengkap. Semua field wajib diisi.");
        }
        
        // Validasi format tanggal
        if (!DateTime::createFromFormat('Y-m-d', $tanggal_pesan) || !DateTime::createFromFormat('Y-m-d', $tanggal_datang)) {
            throw new Exception("Format tanggal tidak valid.");
        }
        
        // Validasi jumlah
        if (!is_numeric($jumlah_pesan) || $jumlah_pesan <= 0) {
            throw new Exception("Jumlah pesanan harus berupa angka positif.");
        }
        
        if (!is_numeric($jumlah_diterima) || $jumlah_diterima < 0) {
            throw new Exception("Jumlah diterima harus berupa angka non-negatif.");
        }
        
        // Insert data pemesanan
        $queryPemesanan = "INSERT INTO pemesanan (id_pengguna, kode_produk, tanggal_pesan, tanggal_datang, vendor, jumlah_pesan, jumlah_diterima, stat) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtPemesanan = mysqli_prepare($connect, $queryPemesanan);
        mysqli_stmt_bind_param($stmtPemesanan, "sssssiis", $id_pengguna, $kode_produk, $tanggal_pesan, $tanggal_datang, $vendor, $jumlah_pesan, $jumlah_diterima, $stat);
        
        if (!mysqli_stmt_execute($stmtPemesanan)) {
            throw new Exception("Gagal menyimpan pemesanan: " . mysqli_error($connect));
        }
        
        // Ambil ID pemesanan yang baru saja dibuat
        $id_pemesanan = mysqli_insert_id($connect);
        
        // Catat riwayat pemesanan
        $actionPemesanan = "Membuat pemesanan baru: $kode_produk - $nama_produk dari vendor $vendor (Jumlah: $jumlah_pesan, Tanggal pesan: $tanggal_pesan, Tanggal datang: $tanggal_datang)";
        addToHistory($connect, $id_pengguna, $actionPemesanan);
        
        mysqli_commit($connect);
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan dan riwayat tercatat', 'id_pemesanan' => $id_pemesanan]);
        
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
    <title>Input Purchase Order | Agricultural System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="addProductStyle.css">
</head>
<body>
<div class="page-container">
    <div class="card">
        <div class="card-header">
            <div>
                <h1 class="card-title">Input Purchase Order</h1>
                <p class="card-subtitle">
                    <i class="fas fa-calendar-alt me-2"></i> 
                    <?= date('d F Y') ?>
                </p>
            </div>
        </div>
        
        <div class="card-body p-4">
            <form id="purchaseForm" method="POST">
                <!-- Product Type Selection -->
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-boxes"></i>Jenis Produk
                    </h2>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="radio-card" onclick="document.getElementById('existing_product').click()">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_new_product" id="existing_product" value="0" checked>
                                    <label class="form-check-label" for="existing_product">
                                        <h6 class="mb-1">Pilih Produk Existing</h6>
                                        <p class="small mb-0">Pilih dari daftar produk yang sudah ada di sistem</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="radio-card" onclick="document.getElementById('new_product').click()">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_new_product" id="new_product" value="1">
                                    <label class="form-check-label" for="new_product">
                                        <h6 class="mb-1">Tambah Produk Baru</h6>
                                        <p class="small mb-0">Tambahkan produk baru ke dalam sistem</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Product Section -->
                <div id="existing_product_section" class="section">
                    <h2 class="section-title">
                        <i class="fas fa-search"></i>Pilih Produk
                    </h2>
                    
                    <div class="mb-4">
                        <label for="kode_produk_existing" class="form-label">Cari Produk <span class="text-danger">*</span></label>
                        <select class="form-select" id="kode_produk_existing" name="kode_produk_existing" style="width: 100%">
                            <option value="">-- Pilih Produk --</option>
                            <?php foreach ($existingProducts as $product): ?>
                            <option value="<?= $product['kode_produk'] ?>" 
                                    data-nama="<?= htmlspecialchars($product['nama_produk']) ?>"
                                    data-kategori="<?= $product['kategori'] ?>"
                                    data-satuan="<?= htmlspecialchars($product['satuan']) ?>"
                                    data-harga="<?= $product['harga'] ?>">
                                <?= $product['kode_produk'] ?> - <?= htmlspecialchars($product['nama_produk']) ?> (<?= $product['kategori'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Product Info -->
                    <div id="product_info" class="product-info" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-semibold">Informasi Produk</h6>
                            <span class="badge badge-success" id="product_code_badge"></span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Nama:</strong> <span id="info_nama"></span></p>
                                <p class="mb-0"><strong>Kategori:</strong> <span class="badge badge-info" id="info_kategori"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Satuan:</strong> <span id="info_satuan"></span></p>
                                <p class="mb-0"><strong>Harga:</strong> Rp <span id="info_harga"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Product Section -->
                <div id="new_product_section" class="section" style="display: none;">
                    <h2 class="section-title">
                        <i class="fas fa-plus-circle"></i>Detail Produk Baru
                    </h2>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
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
                                <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Masukkan nama produk">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
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
                                <label for="harga" class="form-label">Harga <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Data -->
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-clipboard-list"></i>Data Pemesanan
                    </h2>
                    
                    <div class="alert alert-info bg-opacity-10 border border-info border-opacity-25 mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Purchase order ini akan tercatat dalam riwayat sistem dengan detail lengkap.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_pesan" class="form-label">Tanggal Pesan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_pesan" name="tanggal_pesan" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_datang" class="form-label">Tanggal Datang <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vendor" class="form-label">Vendor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vendor" name="vendor" required placeholder="Nama vendor/supplier">
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah_pesan" class="form-label">Jumlah Pesan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah_pesan" name="jumlah_pesan" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah_diterima" class="form-label">Jumlah Diterima</label>
                                <input type="number" class="form-control" id="jumlah_diterima" name="jumlah_diterima" min="0" value="0">
                                <small class="text-muted">Biarkan 0 jika barang belum diterima</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 button-group">
                    <a href="purchase.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 with search
    $('#kode_produk_existing').select2({
        placeholder: "Cari produk...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#existing_product_section')
    });
    
    // Initialize date pickers with Indonesian locale
    flatpickr("#tanggal_pesan", {
        dateFormat: "Y-m-d",
        defaultDate: "today",
        locale: "id"
    });
    
    flatpickr("#tanggal_datang", {
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: "id"
    });
    
    flatpickr("#kadaluwarsa", {
        dateFormat: "Y-m-d",
        locale: "id"
    });
    
    // Toggle sections
    $('input[name="is_new_product"]').change(function() {
        if ($(this).val() == '1') {
            $('#existing_product_section').slideUp(300, function() {
                $('#new_product_section').slideDown(300);
            });
            $('#product_info').hide();
        } else {
            $('#new_product_section').slideUp(300, function() {
                $('#existing_product_section').slideDown(300);
            });
        }
        
        // Update radio card active state
        $('.radio-card').removeClass('active');
        $(this).closest('.radio-card').addClass('active');
    });
    
    // Show product info when selected
    $('#kode_produk_existing').change(function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#product_code_badge').text(selected.val());
            $('#info_nama').text(selected.data('nama'));
            $('#info_kategori').text(selected.data('kategori'));
            $('#info_satuan').text(selected.data('satuan'));
            $('#info_harga').text(parseFloat(selected.data('harga')).toLocaleString('id-ID'));
            $('#product_info').slideDown(300);
        } else {
            $('#product_info').slideUp(300);
        }
    });
    
    // Form validation and submission
    $('#purchaseForm').submit(function(e) {
        e.preventDefault();
        
        const isNewProduct = $('input[name="is_new_product"]:checked').val() == '1';
        let isValid = true;
        let errorMessage = '';
        
        // Debug: log form data
        console.log('Form data:', $(this).serializeArray());
        console.log('Is new product:', isNewProduct);
        console.log('Selected product:', $('#kode_produk_existing').val());
        
        // Validate based on product type
        if (!isNewProduct) {
            const selectedProduct = $('#kode_produk_existing').val();
            console.log('Validating existing product:', selectedProduct);
            if (!selectedProduct || selectedProduct.trim() === '') {
                isValid = false;
                errorMessage = 'Silakan pilih produk terlebih dahulu';
                $('#kode_produk_existing').focus();
            }
        }
        
        if (isNewProduct) {
            if (!$('#kategori').val()) {
                isValid = false;
                errorMessage = 'Silakan pilih kategori produk';
                $('#kategori').focus();
            } else if (!$('#nama_produk').val()) {
                isValid = false;
                errorMessage = 'Silakan isi nama produk';
                $('#nama_produk').focus();
            } else if (!$('#satuan').val()) {
                isValid = false;
                errorMessage = 'Silakan isi satuan produk';
                $('#satuan').focus();
            } else if (!$('#harga').val()) {
                isValid = false;
                errorMessage = 'Silakan isi harga produk';
                $('#harga').focus();
            }
        }
        
        // Validate order data
        if (isValid && !$('#tanggal_pesan').val()) {
            isValid = false;
            errorMessage = 'Silakan isi tanggal pesan';
            $('#tanggal_pesan').focus();
        } else if (isValid && !$('#tanggal_datang').val()) {
            isValid = false;
            errorMessage = 'Silakan isi tanggal datang';
            $('#tanggal_datang').focus();
        } else if (isValid && !$('#vendor').val()) {
            isValid = false;
            errorMessage = 'Silakan isi nama vendor';
            $('#vendor').focus();
        } else if (isValid && !$('#jumlah_pesan').val()) {
            isValid = false;
            errorMessage = 'Silakan isi jumlah pesan';
            $('#jumlah_pesan').focus();
        } else if (isValid && parseInt($('#jumlah_pesan').val()) <= 0) {
            isValid = false;
            errorMessage = 'Jumlah pesan harus lebih dari 0';
            $('#jumlah_pesan').focus();
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: errorMessage,
                confirmButtonColor: 'var(--primary)'
            });
            return;
        }
        
        // Disable submit button to prevent double submission
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...');
        
        // Submit via AJAX
        $.ajax({
            url: '',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                console.log('Sending data:', $('#purchaseForm').serialize());
            },
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Purchase order berhasil disimpan!',
                        confirmButtonColor: 'var(--primary)',
                        willClose: () => {
                            window.location.href = 'purchase.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonColor: 'var(--primary)'
                    });
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Purchase Order');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Sistem',
                    text: 'Terjadi kesalahan sistem: ' + error,
                    confirmButtonColor: 'var(--primary)'
                });
                submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Purchase Order');
            }
        });
    });
    
    // Set active state for radio cards on load
    $('.radio-card').first().addClass('active');

    // Efek parallax sederhana pada header
    $('.card').mousemove(function(e) {
        const x = e.pageX - $(this).offset().left;
        const y = e.pageY - $(this).offset().top;
        const centerX = $(this).width() / 2;
        const centerY = $(this).height() / 2;
        
        const angleX = (y - centerY) / 20;
        const angleY = (centerX - x) / 20;
        
        $(this).find('.card-header').css({
            'background-position': `${50 + angleY}% ${50 + angleX}%`
        });
    });

    // Reset saat mouse keluar
    $('.card').mouseleave(function() {
        $(this).find('.card-header').css({
            'background-position': 'center center'
        });
    });
});
</script>
</body>
</html>