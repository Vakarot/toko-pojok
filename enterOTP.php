<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="enterOTPStyle.css">
</head>
<body>
    <div class="kotak">
        <div class="row">
            <div class="kiri col-7">
                <img src="assets/imageLogin.png" alt="gambar">
                
            </div>
            <div class="kanan col-5">
            <a href="forgotPassword.php" class="back btn" aria-disabled="true">< Back</a>
                <br><br>
                <h3>Enter OTP</h3>
                <p>Enter your registered email address. We'll send you code to reset your password</p>

                <form method="POST" action="enterOTP.php">
                <div class="input-field mb-3">
                    <input type="number" >
                    <input type="number" disabled>
                    <input type="number" disabled>
                    <input type="number" disabled>
                    <input type="number" disabled>
                </div>
                <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">SEND OTP</button>
                </div>
                </form>
        </div>
    </div>

    <script src="enterOTPScript.js"></script>
    
</body>
</html>