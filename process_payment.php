<?php
session_start();
include 'koneksi.php';

if (!isset($_POST['payment_method']) || empty($_SESSION['cart'])) {
    header('Location: cashier.php');
    exit;
}

$payment_method = $_POST['payment_method'];

// Calculate totals
$total_items = 0;
$total_harga = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
    $total_harga += $item['harga'] * $item['quantity'];
}

// Start transaction
mysqli_begin_transaction($connect);

try {
    // Insert into penjualan table
    // Assuming user ID is stored in session, otherwise use default or get from login system
    $id_pengguna = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'USER001'; // Default user ID
    $tanggal_penjualan = date('Y-m-d H:i:s');
    
    $insert_penjualan = "INSERT INTO penjualan (id_pengguna, tanggal_penjualan, total_harga) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connect, $insert_penjualan);
    mysqli_stmt_bind_param($stmt, "ssd", $id_pengguna, $tanggal_penjualan, $total_harga);
    mysqli_stmt_execute($stmt);
    
    $id_penjualan = mysqli_insert_id($connect);
    
    // Insert into detail_penjualan and update stock
    foreach ($_SESSION['cart'] as $kode_produk => $item) {
        // Insert detail penjualan
        $insert_detail = "INSERT INTO detail_penjualan (id_penjualan, kode_produk, harga, jumlah) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $insert_detail);
        mysqli_stmt_bind_param($stmt, "isdi", $id_penjualan, $kode_produk, $item['harga'], $item['quantity']);
        mysqli_stmt_execute($stmt);
        
        // Update stock
        $update_stock = "UPDATE produk SET jumlah_stok = jumlah_stok - ? WHERE kode_produk = ?";
        $stmt = mysqli_prepare($connect, $update_stock);
        mysqli_stmt_bind_param($stmt, "is", $item['quantity'], $kode_produk);
        mysqli_stmt_execute($stmt);
    }
    
    // Commit transaction
    mysqli_commit($connect);
    
    // Store transaction data for receipt
    $_SESSION['transaction'] = [
        'id_penjualan' => $id_penjualan,
        'tanggal' => $tanggal_penjualan,
        'total_harga' => $total_harga,
        'total_items' => $total_items,
        'payment_method' => $payment_method,
        'items' => $_SESSION['cart']
    ];
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Redirect to receipt page
    header('Location: receipt.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($connect);
    
    // Redirect back with error
    $_SESSION['error'] = "Terjadi kesalahan saat memproses pembayaran: " . $e->getMessage();
    header('Location: cashier.php');
    exit;
}
?>