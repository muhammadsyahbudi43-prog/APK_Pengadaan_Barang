<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// =======================================================
// FUNGSI TAMPIL LAPORAN (READ)
// =======================================================
$laporan_ditemukan = false;
$result_laporan = null;
$total_semua_transaksi = 0;

// Default: Tampilkan data 3 bulan terakhir jika tidak ada filter POST
$tgl_awal = isset($_POST['tgl_awal']) ? $conn->real_escape_string($_POST['tgl_awal']) : date('Y-m-d', strtotime('-3 months'));
$tgl_akhir = isset($_POST['tgl_akhir']) ? $conn->real_escape_string($_POST['tgl_akhir']) : date('Y-m-d');


$sql_laporan = "SELECT 
                    h.kode_transaksi, 
                    h.tanggal_pengadaan, 
                    v.nama_vendor, 
                    h.total_biaya,
                    h.status_pengadaan
                FROM 
                    pengadaan_header h
                JOIN 
                    vendor v ON h.id_vendor = v.id_vendor
                WHERE 
                    h.status_pengadaan IN ('Disetujui', 'Dipesan', 'Diterima', 'Selesai')
                    AND h.tanggal_pengadaan BETWEEN '$tgl_awal' AND '$tgl_akhir'
                ORDER BY 
                    h.tanggal_pengadaan DESC";

$result_laporan = $conn->query($sql_laporan);

if ($result_laporan && $result_laporan->num_rows > 0) {
    $laporan_ditemukan = true;
    // Hitung total semua biaya untuk periode ini
    $result_laporan->data_seek(0); // Reset pointer
    while($row = $result_laporan->fetch_assoc()){
        $total_semua_transaksi += $row['total_biaya'];
    }
    $result_laporan->data_seek(0); // Reset pointer lagi untuk tampilan
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengadaan - SIM Pengadaan</title>
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
                    <a class="nav-link" href="pengadaan_data.php">Pengadaan Barang</a>
                    <a class="nav-link active text-white fw-bold" href="laporan_bulanan.php">Laporan Pengadaan</a>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Pengadaan per Periode</h1>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    Filter Periode
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tgl_awal" class="form-label">Tanggal Awal</label>
                                <input type="date" class="form-control" id="tgl_awal" name="tgl_awal" value="<?= $tgl_awal; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tgl_akhir" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" value="<?= $tgl_akhir; ?>" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end mb-3">
                                <button type="submit" class="btn btn-primary me-2">Tampilkan Laporan</button>
                                <a href="laporan_bulanan.php" class="btn btn-warning">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    Hasil Laporan (Periode: <?= date('d M Y', strtotime($tgl_awal)); ?> s/d <?= date('d M Y', strtotime($tgl_akhir)); ?>)
                </div>
                <div class="card-body">
                    <?php if ($laporan_ditemukan): ?>
                    
                    <div class="alert alert-success text-end fw-bold h4">
                        TOTAL BIAYA PENGADAAN: Rp <?= number_format($total_semua_transaksi, 0, ',', '.'); ?>
                    </div>
                        
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Vendor</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php while($row = $result_laporan->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['kode_transaksi']); ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_pengadaan'])); ?></td>
                                        <td><?= htmlspecialchars($row['nama_vendor']); ?></td>
                                        <td><span class="badge bg-primary"><?= $row['status_pengadaan']; ?></span></td>
                                        <td class="text-end">Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-primary fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">TOTAL KESELURUHAN</td>
                                    <td class="text-end">Rp <?= number_format($total_semua_transaksi, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-warning">Tidak ada data pengadaan yang sesuai dengan periode filter.</div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>