<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini.'); window.location.href='index.php';</script>";
    exit();
}

$id_pengguna = $_SESSION['id_pengguna']; // Default user ID for testing

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Generate unique order ID for display
function generateOrderId() {
    return 'ORD' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $kode_produk = $_POST['kode_produk'];
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    if (isset($_SESSION['cart'][$kode_produk])) {
        if ($_SESSION['cart'][$kode_produk]['quantity'] < $stok) {
            $_SESSION['cart'][$kode_produk]['quantity']++;
        }
    } else {
        $_SESSION['cart'][$kode_produk] = [
            'nama_produk' => $nama_produk,
            'harga' => $harga,
            'quantity' => 1,
            'stok' => $stok
        ];
    }
    
    header('Location: cashier.php');
    exit;
}

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $kode_produk = $_POST['kode_produk'];
    $action = $_POST['action'];
    
    if (isset($_SESSION['cart'][$kode_produk])) {
        if ($action == 'increase' && $_SESSION['cart'][$kode_produk]['quantity'] < $_SESSION['cart'][$kode_produk]['stok']) {
            $_SESSION['cart'][$kode_produk]['quantity']++;
        } elseif ($action == 'decrease') {
            $_SESSION['cart'][$kode_produk]['quantity']--;
            if ($_SESSION['cart'][$kode_produk]['quantity'] <= 0) {
                unset($_SESSION['cart'][$kode_produk]);
            }
        }
    }
    
    header('Location: cashier.php');
    exit;
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header('Location: cashier.php');
    exit;
}

// Calculate totals
$total_items = 0;
$total_harga = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
    $total_harga += $item['harga'] * $item['quantity'];
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT kode_produk, nama_produk, satuan, harga, jumlah_stok FROM produk WHERE jumlah_stok > 0";

if (!empty($search)) {
    $query .= " AND (nama_produk LIKE ? OR kode_produk LIKE ?)";
    $stmt = mysqli_prepare($connect, $query);
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $searchParam, $searchParam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($connect, $query);
}

// Generate order ID for display
$display_order_id = generateOrderId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="cashierStyle.css">
</head>
<body>
    <div class="wrapper">
        <!-- Success/Error Messages Modals -->
        <?php if (isset($_GET['success']) && isset($_SESSION['success_message'])): ?>
        <div class="modal fade" id="successToast" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-body p-0">
                        <div class="success-alert">
                            <div class="alert-header bg-success">
                                <div class="alert-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="white"/>
                                    </svg>
                                </div>
                                <h5 class="alert-title">Transaction Successful</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="alert-content">
                                <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                                
                                <?php if (isset($_SESSION['order_details'])): ?>
                                <div class="transaction-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Order ID:</span>
                                        <span class="detail-value"><?= $_SESSION['order_details']['order_id'] ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Total Amount:</span>
                                        <span class="detail-value text-success"><?= formatRupiah($_SESSION['order_details']['total_harga']) ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Payment Method:</span>
                                        <span class="detail-value"><?= ucfirst($_SESSION['order_details']['payment_method']) ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Items Purchased:</span>
                                        <span class="detail-value"><?= $_SESSION['order_details']['total_items'] ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="alert-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="button" class="btn btn-success" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        unset($_SESSION['success_message']);
        unset($_SESSION['order_details']);
        endif; ?>

        <?php if (isset($_GET['error']) && isset($_SESSION['error_message'])): ?>
        <div class="modal fade" id="errorToast" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-body p-0">
                        <div class="error-alert">
                            <div class="alert-header bg-danger">
                                <div class="alert-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 15H13V17H11V15ZM11 7H13V13H11V7ZM12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z" fill="white"/>
                                    </svg>
                                </div>
                                <h5 class="alert-title">Transaction Failed</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="alert-content">
                                <p><?= htmlspecialchars($_SESSION['error_message']) ?></p>
                                
                                <?php if (isset($_SESSION['error_details'])): ?>
                                <div class="error-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Reference:</span>
                                        <span class="detail-value"><?= $_SESSION['error_details']['order_id'] ?? 'N/A' ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Time:</span>
                                        <span class="detail-value"><?= $_SESSION['error_details']['attempt_time'] ?? date('Y-m-d H:i:s') ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Payment Method:</span>
                                        <span class="detail-value"><?= $_SESSION['error_details']['payment_method'] ?? 'Unknown' ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="alert-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Close
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                                    <i class="fas fa-redo me-2"></i>Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        unset($_SESSION['error_message']);
        unset($_SESSION['error_details']);
        endif; ?>

        <!-- Sidebar -->
        <!-- <div class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="Logo TokoPojok" class="img-fluid">
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link" href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
                <a class="nav-link" href="purchase.php"><i class="fas fa-shopping-cart"></i> Purchase</a>
                <a class="nav-link active" href="kasir.php"><i class="fas fa-cash-register"></i> Cashier</a>
                <a class="nav-link" href="history.php"><i class="fas fa-history"></i> History</a>
                <a class="nav-link" href="notifikasi.php"><i class="fas fa-bell"></i> Notifikasi</a>
            </nav>
        </div> -->

        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div> 
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php" class="active"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="main-content">
            <!-- <div class="header-top d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="header-title">Cashier</h1>
                    <div class="header-subtitle">Transaction Calculation</div>
                </div>
                <div class="search-profile d-flex align-items-center gap-3">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="per_page" value="<?= $per_page ?>">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." aria-label="Search products" class="form-control me-2" />
                    </form>

                    <div class="profile-dropdown dropdown">
                        <div class="profile-icon rounded-circle shadow-sm" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" role="button" tabindex="0" title="Profil">
                            <i class="fas fa-user"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end mt-2 rounded-3" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div> -->

            <div class="header-top">
                <div>
                    <h1 class="header-title">Cashier</h1>
                    <div class="header-subtitle">Transaction Calculation</div>
                </div>
                <div class="search-profile">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="per_page" value="<?= $per_page ?>">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." aria-label="Search products" class="form-control me-2" />
                    </form>

                    <div class="profile-dropdown dropdown">
                        <div
                            class="profile-icon rounded-circle shadow-sm"
                            id="profileDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            role="button"
                            tabindex="0"
                            title="Profil"
                        >
                            <i class="fas fa-user"></i>
                        </div>
                        <ul
                            class="dropdown-menu dropdown-menu-end mt-2 rounded-3"
                            aria-labelledby="profileDropdown"
                        >
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Product Table -->
                <div class="col-lg-8">
                    <div class="product-table-container">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="sticky-top">
                                    <tr>
                                        <th>Kode Item</th>
                                        <th>Nama Produk</th>
                                        <th class="text-center">Satuan</th>
                                        <th>Harga</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($data['kode_produk']); ?></td>
                                                <td><?= htmlspecialchars($data['nama_produk']); ?></td>
                                                <td class="text-center"><?= htmlspecialchars($data['satuan']); ?></td>
                                                <td><?= formatRupiah($data['harga']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= $data['jumlah_stok'] <= 10 ? 'bg-warning' : 'bg-success' ?>">
                                                        <?= $data['jumlah_stok']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="kode_produk" value="<?= $data['kode_produk']; ?>">
                                                        <input type="hidden" name="nama_produk" value="<?= $data['nama_produk']; ?>">
                                                        <input type="hidden" name="harga" value="<?= $data['harga']; ?>">
                                                        <input type="hidden" name="stok" value="<?= $data['jumlah_stok']; ?>">
                                                        <button type="submit" name="add_to_cart" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                                            <i class="fas fa-plus"></i> <span>Add</span>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada produk ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Panel -->
                <div class="col-lg-4">
                    <div class="order-panel">
                        <div class="order-header d-flex justify-content-between align-items-center">
                            <div class="order-title d-flex gap-3 align-items-center">
                                <div class="order-icon"><i class="fas fa-clipboard-list"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Pesanan</h6>
                                    <small class="text-muted">Order <?= $display_order_id ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="riwayatPenjualan.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-history"></i>
                                </a>
                                <form method="POST" class="d-inline">
                                    <button type="submit" name="clear_cart" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="order-body">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <div class="order-empty">
                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                    <p class="mb-0">Silakan Pilih Produk...</p>
                                </div>
                            <?php else: ?>
                                <div class="order-item">
                                    <?php foreach ($_SESSION['cart'] as $kode_produk => $item): ?>
                                        <div class="border-b border-gray-100 py-3 px-2">
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($item['nama_produk']); ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($kode_produk); ?></small>
                                                </div>
                                                <div class="text-success fw-bold"><?= formatRupiah($item['harga']); ?></div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="quantity-control d-flex align-items-center">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                                        <input type="hidden" name="action" value="decrease">
                                                        <button type="submit" name="update_quantity" class="quantity-btn btn-danger">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </form>
                                                    <span class="fw-bold px-2"><?= $item['quantity']; ?></span>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                                        <input type="hidden" name="action" value="increase">
                                                        <button type="submit" name="update_quantity" class="quantity-btn btn-success">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold"><?= formatRupiah($item['harga'] * $item['quantity']); ?></div>
                                                    <small class="text-muted">Stok: <?= $item['stok']; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
  
                        <div class="order-footer d-flex justify-content-between align-items-center">
                            <div>
                                <small class="d-block"><?= $total_items; ?> Items</small>
                                <h5 class="mb-0 fw-bold"><?= formatRupiah($total_harga); ?></h5>
                            </div>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <button onclick="showPaymentModal()" class="btn btn-light text-success fw-bold">Order</button>
                            <?php else: ?>
                                <button class="btn btn-light text-muted" disabled>Order</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Pilih Metode Pembayaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="payment-summary mb-4 p-3 bg-light rounded">
                        <p class="text-muted mb-1">Total Pembayaran:</p>
                        <h3 class="fw-bold text-success"><?= formatRupiah($total_harga); ?></h3>
                        <small class="text-muted">Order ID: <?= $display_order_id ?></small>
                    </div>
                    
                    <div class="payment-options">
                        <button onclick="processPayment('tunai')" class="btn btn-payment btn-primary w-100 py-3 mb-3 d-flex align-items-center justify-content-center gap-3">
                            <i class="fas fa-money-bill-wave fs-4"></i>
                            <span class="fs-5 fw-bold">Tunai</span>
                        </button>
                        
                        <button onclick="processPayment('qris')" class="btn btn-payment btn-purple w-100 py-3 d-flex align-items-center justify-content-center gap-3">
                            <i class="fas fa-qrcode fs-4"></i>
                            <span class="fs-5 fw-bold">QRIS</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Payment Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-check-circle me-2"></i> Pembayaran Berhasil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                    </div>
                    
                    <div class="receipt-container bg-light p-3 rounded">
                        <div class="text-center mb-3">
                            <h5 class="fw-bold">Struk Pembayaran</h5>
                            <small class="text-muted"><?= $_SESSION['order_details']['tanggal'] ?></small>
                        </div>
                        
                        <div class="receipt-details">
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Order ID:</span>
                                <span class="fw-bold"><?= $_SESSION['order_details']['order_id'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Kasir:</span>
                                <span><?= $_SESSION['order_details']['kasir'] ?></span>
                            </div>
                            
                            <div class="items-list my-3">
                                <h6 class="fw-bold">Items:</h6>
                                <?php foreach ($_SESSION['order_details']['items'] as $item): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= htmlspecialchars($item) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="total-section border-top pt-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total:</span>
                                    <span class="fw-bold text-success"><?= formatRupiah($_SESSION['order_details']['total_harga']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Metode:</span>
                                    <span><?= ucfirst($_SESSION['order_details']['payment_method']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Payment Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> Pembayaran Gagal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                    </div>
                    
                    <?php if (isset($_SESSION['error_details'])): ?>
                    <div class="error-details bg-light p-3 rounded">
                        <h6 class="fw-bold">Detail Transaksi:</h6>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Waktu:</span>
                            <span><?= $_SESSION['error_details']['attempt_time'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Metode:</span>
                            <span><?= ucfirst($_SESSION['error_details']['payment_method']) ?></span>
                        </div>
                        <?php if (isset($_SESSION['error_details']['order_id'])): ?>
                        <div class="d-flex justify-content-between py-2">
                            <span>Referensi:</span>
                            <span><?= $_SESSION['error_details']['order_id'] ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto show modals on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['success']) && isset($_SESSION['success_message'])): ?>
            new bootstrap.Modal(document.getElementById('successToast')).show();
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && isset($_SESSION['error_message'])): ?>
            new bootstrap.Modal(document.getElementById('errorToast')).show();
            <?php endif; ?>
        });

        function showPaymentModal() {
            var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        }
        
        function hidePaymentModal() {
            var paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            paymentModal.hide();
        }
        
        function processPayment(method) {
            // Show loading state
            const buttons = document.querySelectorAll('#paymentModal .btn-payment');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            });
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'process_payment.php';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = 'payment_method';
            methodInput.value = method;
            
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
            
            // Show loading state
            // const modal = document.getElementById('paymentModal');
            // const buttons = modal.querySelectorAll('button');
            // buttons.forEach(btn => btn.disabled = true);
            
            // Create form and submit
            // const form = document.createElement('form');
            // form.method = 'POST';
            // form.action = 'process_payment.php';
            
            // const methodInput = document.createElement('input');
            // methodInput.type = 'hidden';
            // methodInput.name = 'payment_method';
            // methodInput.value = method;
            
            // form.appendChild(methodInput);
            // document.body.appendChild(form);
            // form.submit();
        }
        
        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hidePaymentModal();
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            if (successAlert) successAlert.style.display = 'none';
            if (errorAlert) errorAlert.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>