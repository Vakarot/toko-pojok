<?php
include 'koneksi.php';

$id = $_POST['id'];
$status = $_POST['status'];

$query = "UPDATE pemesanan SET status = ? WHERE id_pemesanan = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, 'si', $status, $id);
mysqli_stmt_execute($stmt);

if (mysqli_affected_rows($connect) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>