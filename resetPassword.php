<?php
session_start();
include 'koneksi.php';

// Pastikan user telah melalui proses OTP dan email tersimpan di session
if (!isset($_SESSION['email'])) {
    header("Location: forgotPassword.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $email = $_SESSION['email'];

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Password tidak cocok'); window.location.href='resetPassword.php';</script>";
        exit();
    }

    // Tidak menggunakan hashing
    $query = "UPDATE pengguna SET password='$newPassword' WHERE email='$email'";
    $result = mysqli_query($connect, $query);

    if ($result) {
        echo "<script>alert('Password berhasil diperbarui'); window.location.href='login.php';</script>";
        session_destroy(); // hapus session email & otp
    } else {
        echo "<script>alert('Gagal memperbarui password'); window.location.href='resetPassword.php';</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3>Reset Your Password</h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Reset Password</button>
        </form>
    </div>
</body>
</html>

