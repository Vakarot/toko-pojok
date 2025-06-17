<?php
// delete_purchase.php - Hapus purchase order
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

function addToHistory($connect, $id_pengguna, $action) {
    $query = "INSERT INTO riwayat (id_pengguna, action, timestamp) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $id_pengguna, $action);
    return mysqli_stmt_execute($stmt);
}

if ($_POST && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Ambil data pemesanan beserta detail produk untuk history
        $queryData = "SELECT p.*, pr.nama_produk 
                      FROM pemesanan p 
                      LEFT JOIN produk pr ON p.kode_produk = pr.kode_produk 
                      WHERE p.id_pemesanan = ?";
        $stmtData = mysqli_prepare($connect, $queryData);
        mysqli_stmt_bind_param($stmtData, "i", $id);
        mysqli_stmt_execute($stmtData);
        $resultData = mysqli_stmt_get_result($stmtData);
        $purchaseData = mysqli_fetch_assoc($resultData);
        
        if (!$purchaseData) {
            throw new Exception("Data pemesanan tidak ditemukan");
        }
        
        // Cek apakah sudah tersimpan (tidak bisa dihapus jika sudah masuk ke inventory)
        if ($purchaseData['stat'] === 'Tersimpan') {
            throw new Exception("Tidak dapat menghapus data yang sudah disimpan ke inventory");
        }
        
        // Siapkan data untuk history sebelum menghapus
        $kode_produk = $purchaseData['kode_produk'];
        $nama_produk = $purchaseData['nama_produk'];
        $vendor = $purchaseData['vendor'];
        $jumlah_pesan = $purchaseData['jumlah_pesan'];
        $tanggal_pesan = $purchaseData['tanggal_pesan'];
        $tanggal_datang = $purchaseData['tanggal_datang'];
        $jumlah_diterima = $purchaseData['jumlah_diterima'];
        $status = $purchaseData['stat'];
        
        // Mulai transaction
        mysqli_autocommit($connect, false);
        
        // Hapus data pemesanan
        $queryDelete = "DELETE FROM pemesanan WHERE id_pemesanan = ?";
        $stmtDelete = mysqli_prepare($connect, $queryDelete);
        mysqli_stmt_bind_param($stmtDelete, "i", $id);
        mysqli_stmt_execute($stmtDelete);
        
        if (mysqli_stmt_affected_rows($stmtDelete) > 0) {
            // Buat action string untuk history
            $actionDelete = "Menghapus pemesanan #$id: $kode_produk - $nama_produk dari vendor '$vendor' (Status: $status, Jumlah pesan: $jumlah_pesan, Jumlah diterima: $jumlah_diterima, Tanggal pesan: $tanggal_pesan, Tanggal datang: $tanggal_datang)";
            
            // Tambahkan ke history
            if (addToHistory($connect, $id_pengguna, $actionDelete)) {
                // Commit transaction jika semua berhasil
                mysqli_commit($connect);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Data pemesanan berhasil dihapus dan dicatat dalam riwayat'
                ]);
            } else {
                // Rollback jika gagal insert ke history
                mysqli_rollback($connect);
                throw new Exception("Gagal mencatat penghapusan ke riwayat");
            }
        } else {
            mysqli_rollback($connect);
            throw new Exception("Data tidak ditemukan atau gagal dihapus");
        }
        
    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        // Kembalikan autocommit ke true
        mysqli_autocommit($connect, true);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>