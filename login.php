<?php
session_start();
include 'koneksi.php'; // Hubungkan ke file koneksi

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password_input = $_POST['password'];

    // 1. Ambil data user dari database
    $sql = "SELECT id_user, username, password, hak_akses, nama_lengkap FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 2. Verifikasi Password (Penting: menggunakan password_verify untuk hash)
        if (password_verify($password_input, $user['password'])) {
            // Password cocok! Buat session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['hak_akses'] = $user['hak_akses'];

            header("Location: dashboard.php"); // Redirect ke dashboard
            exit;
        } else {
            $error_msg = "Username atau Password salah!";
        }
    } else {
        $error_msg = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Pengadaan - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS Tambahan untuk Desain Keren */
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); /* Warna Biru Elegan */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background-color: rgba(255, 255, 255, 0.95); /* Sedikit Transparan */
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            color: #1e3c72;
            font-weight: 700;
            margin-bottom: 30px;
            border-bottom: 3px solid #2a5298;
            padding-bottom: 10px;
        }
        .btn-login {
            background-color: #2a5298;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-login:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center login-title">SIM PENGADAAN</h3>
        <p class="text-center text-muted">Akses Sistem Anda</p>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg btn-login">MASUK</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>