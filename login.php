<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="loginStyle.css">
</head>
<body>
    <div class="kotak">
        <div class="row">
            <div class="kiri col-7">
                <img src="assets/imageLogin.png" alt="gambar">
                
            </div>
            <div class="kanan col-5">
                <img src="assets/logo.png" alt="logo">
                <br><br>
                <h3>Selamat Datang 👋</h3>
                <p>Please login here</p>

            <form method="POST" action="prosesLogin.php">
                <div class="mb-3">
                    <input type="text" name="nama" class="panjang form-control border border-success" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="panjang form-control border border-success" placeholder="Password" required>
                </div>
                <!-- bagian remember me tetap -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                    <a class="forgotPassword" href="forgotPassword.php">Forgot password?</a>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Login</button>
                </div>
            </form>

            </div>
        </div>
    </div>
</body>
</html>