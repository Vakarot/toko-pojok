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
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Cashier - TokoPojok</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="cashierStyle.css" />
</head>
<body class="bg-white text-black">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success']) && isset($_SESSION['success_message'])): ?>
        <div id="successAlert" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50 max-w-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($_SESSION['success_message']); ?></span>
                </div>
                <button onclick="document.getElementById('successAlert').style.display='none'" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php if (isset($_SESSION['order_details'])): ?>
                <div class="mt-2 text-sm">
                    <p>Total: <?= formatRupiah($_SESSION['order_details']['total_harga']); ?></p>
                    <p>Items: <?= $_SESSION['order_details']['total_items']; ?></p>
                    <p>Metode: <?= ucfirst($_SESSION['order_details']['payment_method']); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php 
        unset($_SESSION['success_message']);
        unset($_SESSION['order_details']);
        ?>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && isset($_SESSION['error_message'])): ?>
        <div id="errorAlert" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50 max-w-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($_SESSION['error_message']); ?></span>
                </div>
                <button onclick="document.getElementById('errorAlert').style.display='none'" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo text-center">
                <img src="assets/logo.png" alt="Logo TokoPojok" />
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Owner') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'InventoryControl') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="purchase.php"><i class="fas fa-shopping-cart"></i>Purchase</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'Cashier') { ?> 
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="cashier.php"><i class="fas fa-cash-register"></i>Cashier</a></li>
                    <li><a href="notifikasi.php"><i class="fas fa-bell"></i>Notifikasi</a></li>
                </ul>
            </nav>
            <?php } ?>
        </aside>
        
        <!-- Main content -->
        <main class="flex-1 p-6">
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-6">
                <div>
                    <h1 class="font-bold text-lg leading-6">Cashier</h1>
                    <p class="text-xs text-gray-400">Transaction Calculation</p>
                </div>
                <div class="flex items-center space-x-4 w-full sm:w-auto">
                    <form method="GET" class="relative flex-1 sm:flex-none">
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search" aria-label="Search" class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm placeholder-gray-300 focus:outline-none focus:ring-1 focus:ring-gray-300" />
                        <button type="submit" class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="cashier.php" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm hover:text-gray-600" aria-label="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                    <button class="flex items-center space-x-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm hover:shadow-md focus:outline-none" type="button">
                        <img alt="User profile picture" class="w-8 h-8 rounded-md object-cover" height="32" src="https://storage.googleapis.com/a1aa/image/8ee84d53-3068-4468-8c0a-770acd8c2d19.jpg" width="32" />
                        <div class="text-left">
                            <p class="font-semibold text-sm leading-5">Luthfan Kafi</p>
                            <p class="text-xs text-gray-400 leading-4">Owner</p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-600 text-xs"></i>
                    </button>
                </div>
            </header>
            
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Table container -->
                <section class="flex-1 bg-white rounded-xl border border-gray-100 p-6 shadow-sm overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-600 border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 text-xs font-semibold">
                                <th class="py-3 px-4">Kode Item</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-center">Satuan</th>
                                <th class="py-3 px-4">Harga</th>
                                <th class="py-3 px-4 text-center">Stok</th>
                                <th class="py-3 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($data = mysqli_fetch_assoc($result)) : ?>
                                    <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 cursor-pointer">
                                        <td class="py-4 px-4 whitespace-nowrap"><?= htmlspecialchars($data['kode_produk']); ?></td>
                                        <td class="py-4 px-4"><?= htmlspecialchars($data['nama_produk']); ?></td>
                                        <td class="py-4 px-4 text-center"><?= htmlspecialchars($data['satuan']); ?></td>
                                        <td class="py-4 px-4"><?= formatRupiah($data['harga']); ?></td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="<?= $data['jumlah_stok'] <= 10 ? 'text-red-600 font-semibold' : 'text-green-600' ?>">
                                                <?= $data['jumlah_stok']; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="kode_produk" value="<?= $data['kode_produk']; ?>">
                                                <input type="hidden" name="nama_produk" value="<?= $data['nama_produk']; ?>">
                                                <input type="hidden" name="harga" value="<?= $data['harga']; ?>">
                                                <input type="hidden" name="stok" value="<?= $data['jumlah_stok']; ?>">
                                                <button type="submit" name="add_to_cart" class="bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 text-xs">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-400 font-semibold">Tidak ada produk ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                
                <!-- Order section -->
                <section class="flex flex-col justify-between w-full lg:w-96 bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div>
                        <header class="flex items-center justify-between border-b border-gray-200 p-4">
                            <div class="flex items-center space-x-3">
                                <div class="bg-green-800 rounded-md p-2 text-white flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-sm text-black">Pesanan</h2>
                                    <p class="text-xs text-gray-400">Order ID <?= $display_order_id ?></p>
                                </div>
                            </div>
                            <form method="POST" class="inline">
                                <button type="submit" name="clear_cart" class="text-red-600 hover:text-red-800 focus:outline-none" title="Clear Cart">
                                <a href="riwayatPenjualan.php">Riwayat</a>
                                    <i class="fas fa-trash text-lg"></i>
                                </button>
                            </form>
                        </header>
                        
                        <div class="max-h-[480px] overflow-y-auto">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <div class="h-[480px] flex items-center justify-center text-gray-300 text-sm font-semibold">
                                    Silakan Pilih Produk...
                                </div>
                            <?php else: ?>
                                <div class="p-2">
                                    <?php foreach ($_SESSION['cart'] as $kode_produk => $item): ?>
                                        <div class="border-b border-gray-100 py-3 px-2">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-xs text-black"><?= htmlspecialchars($item['nama_produk']); ?></p>
                                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($kode_produk); ?></p>
                                                    <p class="text-xs text-green-600 font-semibold"><?= formatRupiah($item['harga']); ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                                        <input type="hidden" name="action" value="decrease">
                                                        <button type="submit" name="update_quantity" class="bg-red-500 text-white w-6 h-6 rounded-full text-xs hover:bg-red-600 flex items-center justify-center">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <span class="bg-gray-100 px-3 py-1 rounded-md text-xs font-semibold min-w-[40px] text-center">
                                                        <?= $item['quantity']; ?>
                                                    </span>
                                                    
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="kode_produk" value="<?= $kode_produk; ?>">
                                                        <input type="hidden" name="action" value="increase">
                                                        <button type="submit" name="update_quantity" class="bg-green-500 text-white w-6 h-6 rounded-full text-xs hover:bg-green-600 flex items-center justify-center" <?= $item['quantity'] >= $item['stok'] ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                
                                                <div class="text-right">
                                                    <p class="text-xs font-semibold text-black"><?= formatRupiah($item['harga'] * $item['quantity']); ?></p>
                                                    <p class="text-xs text-gray-400">Stok: <?= $item['stok']; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bg-green-800 rounded-b-xl p-6 text-white shadow-[0_10px_15px_-3px_rgba(31,91,0,0.3)]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs font-normal"><?= $total_items; ?> Items</p>
                                <p class="font-semibold text-lg"><?= formatRupiah($total_harga); ?></p>
                            </div>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <button onclick="showPaymentModal()" class="bg-white text-green-800 font-semibold rounded-md px-6 py-2 hover:bg-gray-100 focus:outline-none" type="button">
                                    Order
                                </button>
                            <?php else: ?>
                                <button class="bg-gray-300 text-gray-500 font-semibold rounded-md px-6 py-2 cursor-not-allowed" type="button" disabled>
                                    Order
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Payment Modal -->
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