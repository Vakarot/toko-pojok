<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | Your Brand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="loginStyle.css">
</head>
<body>
    <div class="login-wrapper">
        <!-- Image Section -->
        <div class="login-hero">
            <div class="hero-image-container">
                <img src="assets/imageLogin.png" alt="Workspace illustration" class="hero-image">
                <div class="hero-overlay">
                    <h2>Welcome to Our Platform</h2>
                    <p>Streamline your workflow with our powerful tools</p>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="login-content">
            <div class="login-form-container">
                <div class="brand-header">
                    <img src="assets/logo.png" alt="Company Logo" class="brand-logo">
                    <div class="welcome-message">
                        <h1>Welcome Back👋</h1>
                        <p>Please login here</p>
                    </div>
                </div>

                <form method="POST" action="prosesLogin.php" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="nama" placeholder="Enter your username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="rememberMe">
                            <label for="rememberMe">Remember me</label>
                        </div>
                        <a href="forgotPassword.php" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.password-toggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
        });
    </script>
</body>
</html>