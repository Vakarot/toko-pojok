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
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Cashier - TokoPojok</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="cashierStyle.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ========== Base Styles ========== */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            margin: 0;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
            background: #f9fafb;
            overflow-x: hidden;
        }

        /* ========== Sidebar (Same as inventory) ========== */
        .sidebar {
            width: 240px;
            background: #e6f4ea;
            padding: 2rem 1.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            border-radius: 0 20px 20px 0;
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        /* ... (Same sidebar styles as inventory.css) ... */

        /* ========== Main Content ========== */
        .main-content {
            flex: 1;
            padding: 2rem 2.5rem;
            display: flex;
            flex-direction: column;
            width: calc(100% - 240px);
            max-width: 100%;
            overflow: hidden;
        }

        /* ========== Header (Same as inventory) ========== */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem 2rem;
            margin-bottom: 1.5rem;
        }

        .header-title {
            font-size: 2rem;
            color: #2f4f4f;
            font-weight: 700;
        }

        .header-subtitle {
            color: #6c757d;
            font-weight: 400;
        }

        /* ========== Flex Container ========== */
        .flex-container {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        /* ========== Table Container (Same as inventory) ========== */
        .table-container {
            flex: 1;
            min-width: 60%;
            padding: 1rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
            overflow: hidden;
            position: relative;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 10px;
        }

        /* ========== Order Container ========== */
        .order-container {
            width: 380px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }

        .order-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .order-title h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .order-title small {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .order-items {
            flex: 1;
            overflow-y: auto;
            max-height: 60vh;
            padding: 1rem;
        }

        .empty-cart {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            padding: 2rem;
            text-align: center;
        }

        .order-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .item-info h4 {
            margin: 0;
            font-size: 0.95rem;
        }

        .item-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .item-price {
            color: #28a745;
            font-weight: 600;
            margin-top: 0.3rem;
        }

        .item-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }

        .quantity {
            min-width: 30px;
            text-align: center;
            font-weight: 500;
        }

        .item-total {
            margin-left: auto;
            text-align: right;
            font-weight: 600;
        }

        .item-total small {
            display: block;
            color: #6c757d;
            font-size: 0.7rem;
            font-weight: normal;
        }

        .order-footer {
            padding: 1.5rem;
            background: #28a745;
            color: white;
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-summary h3 {
            margin: 0.2rem 0 0;
            font-size: 1.5rem;
        }

        .order-summary small {
            opacity: 0.8;
        }

        /* ========== Responsive ========== */
        @media (max-width: 1200px) {
            .flex-container {
                flex-direction: column;
            }
            
            .order-container {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .wrapper {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                flex-direction: row;
                justify-content: space-around;
                padding: 1rem 0;
                border-radius: 0 0 20px 20px;
            }
            
            .main-content {
                padding: 1.5rem;
                width: 100%;
            }
            
            .header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-profile {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar (Same as inventory.php) -->
    <aside class="sidebar">
        <div class="logo text-center">
            <img src="assets/logo.png" alt="Logo TokoPojok" />
        </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Owner') { ?> 
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
        <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                <li><a href="cashier.php" class="active"><i class="fas fa-cash-register"></i>Cashier</a></li>
                <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
            </ul>
        </nav>
        <?php } ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header-top">
            <div>
                <h1 class="header-title">Cashier</h1>
                <div class="header-subtitle">Transaction Calculation</div>
            </div>
            <div class="search-profile">
                <form method="GET" class="d-flex">
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

        <!-- Alert Messages -->
        <?php if (isset($_GET['success']) && isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="flex-container">
            <!-- Product List -->
            <div class="table-container">
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode Item</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['kode_produk']); ?></td>
                                    <td><?= htmlspecialchars($data['nama_produk']); ?></td>
                                    <td><?= htmlspecialchars($data['satuan']); ?></td>
                                    <td><?= formatRupiah($data['harga']); ?></td>
                                    <td class="<?= $data['jumlah_stok'] <= 10 ? 'stok-rendah' : '' ?>">
                                        <?= $data['jumlah_stok']; ?>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="kode_produk" value="<?= $data['kode_produk']; ?>">
                                            <input type="hidden" name="nama_produk" value="<?= $data['nama_produk']; ?>">
                                            <input type="hidden" name="harga" value="<?= $data['harga']; ?>">
                                            <input type="hidden" name="stok" value="<?= $data['jumlah_stok']; ?>">
                                            <button type="submit" name="add_to_cart" class="btn btn-success btn-sm">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada produk ditemukan.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Section -->
            <div class="order-container">
                <div class="order-header">
                    <div class="order-title">
                        <i class="fas fa-clipboard-list me-2"></i>
                        <div>
                            <h3>Pesanan</h3>
                            <small>Order ID: <?= $display_order_id ?></small>
                        </div>
                    </div>
                    <form method="POST">
                        <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

                <div class="order-items">
                    <?php if (empty($_SESSION['cart'])): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                            <p class="text-muted">Silakan pilih produk...</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['cart'] as $kode_produk => $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <h4><?= htmlspecialchars($item['nama_produk']); ?></h4>
                                <small><?= $kode_produk; ?></small>
                                <div class="item-price"><?= formatRupiah($item['harga']); ?></div>
                            </div>
                            <div class="item-controls">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <button type="submit" name="update_quantity" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </form>
                                <span class="quantity"><?= $item['quantity']; ?></span>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <button type="submit" name="update_quantity" class="btn btn-outline-success btn-sm" <?= $item['quantity'] >= $item['stok'] ? 'disabled' : '' ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                <div class="item-total">
                                    <?= formatRupiah($item['harga'] * $item['quantity']); ?>
                                    <small>Stok: <?= $item['stok']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="order-footer">
                    <div class="order-summary">
                        <div>
                            <small><?= $total_items; ?> Items</small>
                            <h3><?= formatRupiah($total_harga); ?></h3>
                        </div>
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <button onclick="showPaymentModal()" class="btn btn-light">
                                <i class="fas fa-credit-card me-2"></i> Order
                            </button>
                        <?php else: ?>
                            <button class="btn btn-light" disabled>
                                <i class="fas fa-credit-card me-2"></i> Order
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Payment Modal (Same as before) -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Pilih Metode Pembayaran</h3>
                    <button onclick="hidePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-2">Total Pembayaran:</p>
                    <p class="text-2xl font-bold text-green-600"><?= formatRupiah($total_harga); ?></p>
                    <p class="text-sm text-gray-500 mt-1">Order ID: <?= $display_order_id ?></p>
                </div>
                
                <div class="space-y-3">
                    <button onclick="processPayment('tunai')" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center space-x-2">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Tunai</span>
                    </button>
                    
                    <button onclick="processPayment('qris')" class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 flex items-center justify-center space-x-2">
                        <i class="fas fa-qrcode"></i>
                        <span>QRIS</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        
        function hidePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
        
        function processPayment(method) {
            // Show loading state
            const modal = document.getElementById('paymentModal');
            const buttons = modal.querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = true);
            
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