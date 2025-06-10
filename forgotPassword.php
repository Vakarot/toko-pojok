<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once "koneksi.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $connect->prepare("SELECT * FROM pengguna WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = rand(10000, 99999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Kirim OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'luthfank345@gmail.com'; // Ganti dengan email kamu
            $mail->Password = 'buqlyyohxoixoicv'; // Gunakan App Password Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('luthfank345@gmail.com', 'Toko Pojok');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'OTP Reset Password';
            $mail->Body = 'Kode OTP Anda adalah <b>' . $otp . '</b>';

            $mail->send();
            header("Location: enterOTP.php");
            exit();
        } catch (Exception $e) {
            $error = "Gagal mengirim email: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email tidak terdaftar.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="forgotPasswordStyle.css">
</head>
<body>
    <div class="kotak">
        <div class="row">
            <div class="kiri col-7">
                <img src="assets/imageLogin.png" alt="gambar">
                
            </div>
            <div class="kanan col-5">
            <a href="login.php" class="back btn" aria-disabled="true">< Back</a>
                <br><br>
                <h3>Forgot Password</h3>
                <p>Enter your registered email address. We'll send you code to reset your password</p>

                <form method="POST" action="forgotPassword.php">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control border border-success" placeholder="Email Address" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">SEND OTP</button>
                </div>
            </form>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mt-2"><?= $error ?></div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>