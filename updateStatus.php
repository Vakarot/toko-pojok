<?php
// updateStatus.php - File untuk update status dan stok
session_start();
include 'koneksi.php';

if ($_POST) {
    $id_pemesanan = $_POST['id'];
    $stat = $_POST['stat'];
    
    try {
        mysqli_begin_transaction($connect);
        
        // Jika status menjadi "Tersimpan", update stok produk dan catat riwayat
        if ($stat === 'Tersimpan') {
            // Ambil data pemesanan dengan detail produk
            $queryPemesanan = "SELECT p.kode_produk, p.jumlah_diterima, p.vendor, pr.nama_produk, pr.satuan, pr.jumlah_stok as current_stock 
                              FROM pemesanan p 
                              LEFT JOIN produk pr ON p.kode_produk = pr.kode_produk 
                              WHERE p.id_pemesanan = ?";
            $stmtPemesanan = mysqli_prepare($connect, $queryPemesanan);
            mysqli_stmt_bind_param($stmtPemesanan, "i", $id_pemesanan);
            mysqli_stmt_execute($stmtPemesanan);
            $result = mysqli_stmt_get_result($stmtPemesanan);
            $pemesanan = mysqli_fetch_assoc($result);
            
            if ($pemesanan) {
                // Update stok produk
                $queryUpdateStok = "UPDATE produk SET jumlah_stok = jumlah_stok + ? WHERE kode_produk = ?";
                $stmtUpdateStok = mysqli_prepare($connect, $queryUpdateStok);
                mysqli_stmt_bind_param($stmtUpdateStok, "is", $pemesanan['jumlah_diterima'], $pemesanan['kode_produk']);
                mysqli_stmt_execute($stmtUpdateStok);
                
                // Cek apakah stok berhasil diupdate
                if (mysqli_stmt_affected_rows($stmtUpdateStok) == 0) {
                    throw new Exception("Gagal mengupdate stok produk");
                }
                
                // Catat riwayat penerimaan barang
                if (isset($_SESSION['id_pengguna'])) {
                    $id_pengguna = $_SESSION['id_pengguna'];
                    $old_stock = $pemesanan['current_stock'];
                    $new_stock = $old_stock + $pemesanan['jumlah_diterima'];
                    $stock_change = $pemesanan['jumlah_diterima'];
                    
                    $action_description = "Penerimaan barang PO#" . $id_pemesanan . " - " . $pemesanan['nama_produk'] . 
                                         " | Vendor: " . $pemesanan['vendor'] . 
                                         " | Qty diterima: " . number_format($stock_change) . " " . 
                                         " | Stok: " . number_format($old_stock) . " → " . number_format($new_stock) . " ";
                    
                    $stmt_riwayat = mysqli_prepare($connect,
                        "INSERT INTO riwayat (id_pengguna, action, timestamp) VALUES (?, ?, NOW())"
                    );
                    mysqli_stmt_bind_param($stmt_riwayat, "ss", $id_pengguna, $action_description);
                    
                    if (!mysqli_stmt_execute($stmt_riwayat)) {
                        throw new Exception("Gagal mencatat riwayat: " . mysqli_stmt_error($stmt_riwayat));
                    }
                }
            }
        }
        
        // Update status pemesanan
        $queryUpdateStatus = "UPDATE pemesanan SET stat = ? WHERE id_pemesanan = ?";
        $stmtUpdateStatus = mysqli_prepare($connect, $queryUpdateStatus);
        mysqli_stmt_bind_param($stmtUpdateStatus, "si", $stat, $id_pemesanan);
        mysqli_stmt_execute($stmtUpdateStatus);
        
        if (mysqli_stmt_affected_rows($stmtUpdateStatus) == 0) {
            throw new Exception("Gagal mengupdate status pemesanan");
        }
        
        mysqli_commit($connect);
        echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
        
    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>