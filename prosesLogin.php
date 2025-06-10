<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama']; // nama digunakan untuk login
    $passwordInput = $_POST['password'];

    // Cek user berdasarkan nama
    $query = "SELECT * FROM pengguna WHERE nama='$nama'";
    $result = mysqli_query($connect, $query);

    if (mysqli_num_rows($result) === 1) {
        $pengguna = mysqli_fetch_assoc($result);
        $passwordDB = $pengguna['password'];

        // Bandingkan langsung (tanpa hash)
        if ($passwordInput === $passwordDB) {
            // Simpan data ke session
            $_SESSION['id_pengguna'] = $pengguna['id_pengguna'];
            $_SESSION['nama'] = $pengguna['nama'];
            $_SESSION['role'] = $pengguna['role'];

            echo "<script>alert('Login berhasil!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Nama pengguna tidak ditemukan!'); window.location.href='login.php';</script>";
    }
}
?>
