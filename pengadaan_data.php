<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// =======================================================
// FUNGSI SIMPAN HEADER PENGADAAN BARU (CREATE)
// =======================================================
if (isset($_POST['buat_transaksi'])) {
    $id_vendor = (int)$_POST['id_vendor'];
    $tanggal = $conn->real_escape_string($_POST['tanggal_pengadaan']);
    $user_id = $_SESSION['id_user']; // ID pengguna yang sedang login
    
    // 1. GENERATE KODE TRANSAKSI OTOMATIS (Contoh: PO/Y-M/0001)
    $tahun_bulan = date('Y-m');
    
    // Ambil nomor urut terakhir
    $sql_last = "SELECT COUNT(*) as total FROM pengadaan_header WHERE DATE_FORMAT(tanggal_pengadaan, '%Y-%m') = '$tahun_bulan'";
    $res_last = $conn->query($sql_last)->fetch_assoc();
    $no_urut = $res_last['total'] + 1;
    $kode_transaksi = "PO/" . date('Y-m') . "/" . str_pad($no_urut, 4, '0', STR_PAD_LEFT);
    
    // 2. SIMPAN KE HEADER
    $sql_insert_header = "INSERT INTO pengadaan_header 
                          (kode_transaksi, tanggal_pengadaan, id_vendor, status_pengadaan, user_id_input) 
                          VALUES ('$kode_transaksi', '$tanggal', $id_vendor, 'Draft', $user_id)";

    if ($conn->query($sql_insert_header) === TRUE) {
        $id_pengadaan_baru = $conn->insert_id; // Dapatkan ID Transaksi yang baru dibuat!
        
        // Redirect ke halaman detail untuk mengisi barang
        header("Location: pengadaan_detail.php?id=$id_pengadaan_baru");
        exit;
    } else {
        $pesan_error = "Error saat membuat Header Transaksi: " . $conn->error;
    }
}

// =======================================================
// AMBIL DATA MASTER UNTUK FORM
// =======================================================
$sql_vendor = "SELECT id_vendor, nama_vendor FROM vendor ORDER BY nama_vendor ASC";
$result_vendor = $conn->query($sql_vendor);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengadaan Barang - SIM Pengadaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { height: 100vh; background-color: #2a5298; color: white; padding-top: 20px; }
        .main-content { padding: 30px; }
        .nav-link { color: rgba(255, 255, 255, 0.8); }
        .nav-link:hover { color: white; background-color: #1e3c72; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-center py-3 border-bottom text-warning">SIM Pengadaan</h4>
                <div class="nav flex-column">
                    <span class="d-block text-center mb-3 text-muted">Akses: <?= $_SESSION['hak_akses']; ?></span>
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="master_barang.php">Master Barang</a>
                    <a class="nav-link" href="master_vendor.php">Master Vendor</a>
                    <a class="nav-link active text-white fw-bold" href="pengadaan_data.php">Pengadaan Barang</a>
                    <a class="nav-link" href="laporan_bulanan.php">Laporan Pengadaan</a>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pengadaan Barang</h1>
            </div>

            <?php if (isset($pesan_error)): ?>
                <div class="alert alert-danger" role="alert"><?= $pesan_error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    Buat Transaksi Pengadaan Baru
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_pengadaan" class="form-label">Tanggal Pengadaan</label>
                                <input type="date" class="form-control" id="tanggal_pengadaan" name="tanggal_pengadaan" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_vendor" class="form-label">Pilih Vendor</label>
                                <select class="form-select" id="id_vendor" name="id_vendor" required>
                                    <option value="">-- Pilih Vendor --</option>
                                    <?php while($vendor = $result_vendor->fetch_assoc()): ?>
                                        <option value="<?= $vendor['id_vendor']; ?>">
                                            <?= htmlspecialchars($vendor['nama_vendor']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="buat_transaksi" class="btn btn-success mt-3">Lanjutkan ke Detail Barang</button>
                    </form>
                </div>
            </div>

            <h3 class="mt-5">Riwayat Pengadaan</h3>
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted">Di sini akan ditampilkan daftar transaksi pengadaan yang sudah dibuat, lengkap dengan statusnya (Draft, Disetujui, Diterima, dll.)</p>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Vendor</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="text-center">Belum ada transaksi pengadaan.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>