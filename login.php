<?php
session_start(); // Mulai sesi
if (isset($_SESSION['username'])) {
    // Jika pengguna sudah login, arahkan ke index.php
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login & Registrasi</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="light"></div>
    <div class="light"></div>
    <div class="light"></div>

    <div class="container" id="container">
        <!-- Form Login -->
        <div class="form-container sign-in-container">
            <form id="loginForm">
            <h1>Login / Sign In</h1>
                <div class="social-container">
                    <a href="#"><span>f</span></a>
                    <a href="#"><span>G</span></a>
                    <a href="#"><span>in</span></a>
                </div>
                <p>Masukkan Username</p>
                <div class="form-group">
                    <input type="text" id="login-username" name="username" placeholder=" " required>
                    <label for="login-username">Username</label>
                </div>
                <div class="form-group">
                    <input type="password" id="login-password" name="password" placeholder=" " required>
                    <label for="login-password">Password</label>
                </div>
                <div class="forgot-password">
                    <a href="#">Lupa password?</a>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>