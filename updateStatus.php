<?php
// updateStatus.php - File untuk update status dan stok
session_start();
include 'koneksi.php';

if ($_POST) {
    $id_pemesanan = $_POST['id'];
    $stat = $_POST['stat'];
    
    try {
        mysqli_begin_transaction($connect);
        
        // Jika status menjadi "Tersimpan", update stok produk
        if ($stat === 'Tersimpan') {
            // Ambil data pemesanan
            $queryPemesanan = "SELECT kode_produk, jumlah_diterima FROM pemesanan WHERE id_pemesanan = ?";
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