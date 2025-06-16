<?php
session_start();
include 'koneksi.php';

// Check if cart is not empty
if (empty($_SESSION['cart'])) {
    header('Location: cashier.php');
    exit;
}

// Check if payment method is provided
if (!isset($_POST['payment_method'])) {
    header('Location: cashier.php');
    exit;
}

// For testing purposes, set a default user ID if not exists
// Replace this with proper login system
if (!isset($_SESSION['id_pengguna']) || empty($_SESSION['id_pengguna'])) {
    // User is not logged in, redirect to login page
    header('Location: login.php');
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

// Generate unique order ID
$order_id = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Start transaction
mysqli_begin_transaction($connect);

try {
    $id_pengguna = $_SESSION['id_pengguna'];
    
    // First, let's check if the user exists
    $check_user = mysqli_prepare($connect, "SELECT id_pengguna FROM pengguna WHERE id_pengguna = ?");
    mysqli_stmt_bind_param($check_user, "s", $id_pengguna);
    mysqli_stmt_execute($check_user);
    $user_result = mysqli_stmt_get_result($check_user);
    
    if (mysqli_num_rows($user_result) == 0) {
        throw new Exception("User tidak ditemukan: " . $id_pengguna);
    }
    
    // Insert into penjualan table
    $stmt_penjualan = mysqli_prepare($connect,
        "INSERT INTO penjualan (id_penjualan, id_pengguna, tanggal_penjualan, total_harga, metode_pembayaran) VALUES (?, ?, NOW(), ?, ?)"
    );
    mysqli_stmt_bind_param($stmt_penjualan, "ssds", $order_id, $id_pengguna, $total_harga, $payment_method);
    
    if (!mysqli_stmt_execute($stmt_penjualan)) {
        throw new Exception("Error inserting penjualan: " . mysqli_stmt_error($stmt_penjualan));
    }
    
    // Verify the penjualan was inserted
    $verify_penjualan = mysqli_prepare($connect, "SELECT id_penjualan FROM penjualan WHERE id_penjualan = ?");
    mysqli_stmt_bind_param($verify_penjualan, "s", $order_id);
    mysqli_stmt_execute($verify_penjualan);
    $verify_result = mysqli_stmt_get_result($verify_penjualan);
    
    if (mysqli_num_rows($verify_result) == 0) {
        throw new Exception("Failed to insert penjualan record");
    }
    
    // Insert into detail_penjualan and update stock
    foreach ($_SESSION['cart'] as $kode_produk => $item) {
        // Check if product exists and has sufficient stock
        $check_product = mysqli_prepare($connect, 
            "SELECT jumlah_stok FROM produk WHERE kode_produk = ?"
        );
        mysqli_stmt_bind_param($check_product, "s", $kode_produk);
        mysqli_stmt_execute($check_product);
        $product_result = mysqli_stmt_get_result($check_product);
        
        if (mysqli_num_rows($product_result) == 0) {
            throw new Exception("Produk tidak ditemukan: " . $kode_produk);
        }
        
        $product_data = mysqli_fetch_assoc($product_result);
        if ($product_data['jumlah_stok'] < $item['quantity']) {
            throw new Exception("Stok tidak mencukupi untuk produk: " . $kode_produk);
        }
        
        // Insert detail penjualan
        $stmt_detail = mysqli_prepare($connect,
            "INSERT INTO detail_penjualan (id_penjualan, kode_produk, harga, jumlah) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt_detail, "ssdi", $order_id, $kode_produk, $item['harga'], $item['quantity']);
        
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception("Error inserting detail_penjualan: " . mysqli_stmt_error($stmt_detail));
        }
        
        // Update stock
        $stmt_update_stock = mysqli_prepare($connect,
            "UPDATE produk SET jumlah_stok = jumlah_stok - ? WHERE kode_produk = ?"
        );
        mysqli_stmt_bind_param($stmt_update_stock, "is", $item['quantity'], $kode_produk);
        
        if (!mysqli_stmt_execute($stmt_update_stock)) {
            throw new Exception("Error updating stock: " . mysqli_stmt_error($stmt_update_stock));
        }
        
        // Verify stock was updated
        if (mysqli_stmt_affected_rows($stmt_update_stock) == 0) {
            throw new Exception("Failed to update stock for product: " . $kode_produk);
        }
    }
    
    // Commit transaction
    mysqli_commit($connect);
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Set success message
    $_SESSION['success_message'] = "Transaksi berhasil! Order ID: " . $order_id;
    $_SESSION['order_details'] = [
        'order_id' => $order_id,
        'total_harga' => $total_harga,
        'total_items' => $total_items,
        'payment_method' => $payment_method,
        'tanggal' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to success page or back to cashier with success message
    header('Location: cashier.php?success=1');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($connect);
    
    // Set error message
    $_SESSION['error_message'] = "Transaksi gagal: " . $e->getMessage();
    
    // Redirect back to cashier
    header('Location: cashier.php?error=1');
    exit;
}
?>