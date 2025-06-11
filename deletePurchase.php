<?php
// delete_purchase.php - Hapus purchase order
session_start();
include 'koneksi.php';

if ($_POST && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Cek apakah sudah tersimpan (tidak bisa dihapus jika sudah masuk ke inventory)
        $queryCheck = "SELECT stat FROM pemesanan WHERE id_pemesanan = ?";
        $stmtCheck = mysqli_prepare($connect, $queryCheck);
        mysqli_stmt_bind_param($stmtCheck, "i", $id);
        mysqli_stmt_execute($stmtCheck);
        $result = mysqli_stmt_get_result($stmtCheck);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['stat'] === 'Tersimpan') {
            throw new Exception("Tidak dapat menghapus data yang sudah disimpan ke inventory");
        }
        
        // Hapus data
        $queryDelete = "DELETE FROM pemesanan WHERE id_pemesanan = ?";
        $stmtDelete = mysqli_prepare($connect, $queryDelete);
        mysqli_stmt_bind_param($stmtDelete, "i", $id);
        mysqli_stmt_execute($stmtDelete);
        
        if (mysqli_stmt_affected_rows($stmtDelete) > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Data tidak ditemukan");
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>