<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user dari session
$nama_user = $_SESSION['nama'];
$hak_akses = $_SESSION['hak_akses'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIM Pengadaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #2a5298; /* Warna Sidebar Keren */
            color: white;
            padding-top: 20px;
        }
        .main-content {
            padding: 30px;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .nav-link:hover {
            color: white;
            background-color: #1e3c72;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-center py-3 border-bottom text-warning">SIM Pengadaan</h4>
                <div class="nav flex-column">
                    <span class="d-block text-center mb-3 text-muted">Akses: <?= $hak_akses; ?></span>
                    <a class="nav-link active text-white" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="master_barang.php">Master Barang</a>
                    <a class="nav-link" href="master_vendor.php">Master Vendor</a>
                    <a class="nav-link" href="pengadaan_data.php">Pengadaan Barang</a>
                    <a class="nav-link" href="laporan_bulanan.php">Laporan Pengadaan</a>
                    <?php if ($hak_akses === 'Admin'): ?>
                        <a class="nav-link" href="master_user.php">Manajemen User</a>
                    <?php endif; ?>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="logout.php">Logout (<?= $nama_user; ?>)</a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Selamat Datang, <?= $nama_user; ?>!</h1>
            </div>

            <div class="alert alert-success" role="alert">
                Anda berhasil login sebagai **<?= $hak_akses; ?>**. Ini adalah halaman Dashboard utama Anda.
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Total Pengadaan Bulan Ini</h5>
                            <p class="card-text h1">Rp 15.000.000</p>
                            <small class="text-muted">Dummy Data</small>
                        </div>
                    </div>
                </div>
                </div>

            </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>