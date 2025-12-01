<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 1. Ambil ID Pengadaan dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pengadaan_data.php");
    exit;
}

$id_pengadaan = (int)$_GET['id'];

// 2. Ambil Data Header Transaksi (Perbaikan: u.nama -> u.nama_lengkap, u.id -> u.id_user)
$sql_header = "SELECT h.*, v.nama_vendor, u.nama_lengkap AS user_input_name 
               FROM pengadaan_header h
               JOIN vendor v ON h.id_vendor = v.id_vendor
               LEFT JOIN users u ON h.user_id_input = u.id_user 
               WHERE h.id_pengadaan = $id_pengadaan";
$res_header = $conn->query($sql_header);

if ($res_header->num_rows == 0) {
    echo "<h1>Error: Transaksi tidak ditemukan!</h1>";
    exit;
}

$header = $res_header->fetch_assoc();

// Cek status agar tidak bisa diubah jika sudah disetujui/diterima
if ($header['status_pengadaan'] !== 'Draft') {
    $read_only = true;
    $pesan_warning = "Transaksi sudah berstatus **" . $header['status_pengadaan'] . "** dan tidak dapat diubah.";
} else {
    $read_only = false;
}

// 3. Ambil Data Master Barang untuk Form Tambah Item
$sql_master_barang = "SELECT id_barang, nama_barang, satuan FROM barang ORDER BY nama_barang ASC";
$result_master_barang = $conn->query($sql_master_barang);


// =======================================================
// 4. FUNGSI TAMBAH ITEM DETAIL (CREATE)
// =======================================================
if (isset($_POST['tambah_item']) && !$read_only) {
    $id_barang = (int)$_POST['id_barang'];
    $kuantitas_input = (int)$_POST['kuantitas']; // Variabel input dari form
    
    // Gunakan fungsi str_replace untuk menghilangkan titik/koma pemisah ribuan
    $harga_satuan_str = str_replace(['.', ','], '', $_POST['harga_satuan']);
    $harga_satuan = (int)$harga_satuan_str;
    
    // Hitung subtotal
    $subtotal = $kuantitas_input * $harga_satuan;

    // Perbaikan: Kolom kuantitas diganti jadi jumlah_pesan
    $sql_insert_detail = "INSERT INTO pengadaan_detail 
                          (id_pengadaan, id_barang, jumlah_pesan, harga_satuan, subtotal) 
                          VALUES ($id_pengadaan, $id_barang, $kuantitas_input, $harga_satuan, $subtotal)";

    if ($conn->query($sql_insert_detail) === TRUE) {
        $pesan_sukses = "Item barang berhasil ditambahkan.";
    } else {
        $pesan_error = "Error saat menambahkan item: " . $conn->error;
    }
}

// =======================================================
// 5. FUNGSI HAPUS ITEM (DELETE)
// =======================================================
if (isset($_GET['delete_item']) && !$read_only) {
    $id_detail_hapus = (int)$_GET['delete_item'];
    
    $sql_delete_detail = "DELETE FROM pengadaan_detail WHERE id_detail = $id_detail_hapus";
    
    if ($conn->query($sql_delete_detail) === TRUE) {
        // Redirect untuk menghilangkan parameter GET dan me-refresh data
        header("Location: pengadaan_detail.php?id=$id_pengadaan&pesan_sukses=Item berhasil dihapus.");
        exit;
    } else {
        $pesan_error = "Gagal menghapus item: " . $conn->error;
    }
}


// =======================================================
// 6. FUNGSI TAMPIL DETAIL ITEM (READ)
// =======================================================
// Kita tampilkan kolom jumlah_pesan dari tabel detail
$sql_detail = "SELECT d.*, b.nama_barang, b.satuan 
               FROM pengadaan_detail d
               JOIN barang b ON d.id_barang = b.id_barang
               WHERE d.id_pengadaan = $id_pengadaan
               ORDER BY b.nama_barang ASC";
$result_detail = $conn->query($sql_detail);

// 7. Hitung Total Keseluruhan
$total_biaya = 0;
// Clone result set karena akan digunakan dua kali (untuk loop dan perhitungan)
$result_detail_recalc = $conn->query($sql_detail);
while($row = $result_detail_recalc->fetch_assoc()){
    $total_biaya += $row['subtotal'];
}

// 8. Update Total Biaya di Header
$sql_update_total = "UPDATE pengadaan_header SET total_biaya = $total_biaya WHERE id_pengadaan = $id_pengadaan";
$conn->query($sql_update_total); 


// =======================================================
// 9. FUNGSI UPDATE STATUS DAN STOK KE 'DITERIMA'
// =======================================================
if (isset($_POST['set_diterima']) && !$read_only && $result_detail->num_rows > 0) {
    // 1. Ambil semua item detail yang ada di transaksi ini
    // Perbaikan: Kolom kuantitas diganti jadi jumlah_pesan
    $sql_items = "SELECT id_barang, jumlah_pesan FROM pengadaan_detail WHERE id_pengadaan = $id_pengadaan";
    $res_items = $conn->query($sql_items);
    
    $stok_berhasil_diupdate = true;

    // 2. Loop dan Update Stok Master Barang
    while($item = $res_items->fetch_assoc()) {
        $id_barang = $item['id_barang'];
        $kuantitas_update = $item['jumlah_pesan']; // Ambil dari kolom 'jumlah_pesan'
        
        // Query untuk menambahkan kuantitas ke stok_saat_ini
        $sql_update_stok = "UPDATE barang SET stok_saat_ini = stok_saat_ini + $kuantitas_update WHERE id_barang = $id_barang";
        
        if (!$conn->query($sql_update_stok)) {
            $stok_berhasil_diupdate = false;
            $pesan_error = "Gagal update stok barang ID $id_barang: " . $conn->error;
            break; 
        }
    }

    // 3. Update Status Header jika semua stok berhasil diupdate
    if ($stok_berhasil_diupdate) {
        $sql_update_status = "UPDATE pengadaan_header SET status_pengadaan = 'Diterima' WHERE id_pengadaan = $id_pengadaan";
        if ($conn->query($sql_update_status) === TRUE) {
            // Redirect untuk me-refresh data dan status read_only
            header("Location: pengadaan_detail.php?id=$id_pengadaan&pesan_sukses=Transaksi berhasil diselesaikan! Stok barang telah diperbarui.");
            exit;
        } else {
            $pesan_error = "Error saat update status: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengadaan: <?= $header['kode_transaksi']; ?></title>
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
                <h1 class="h2">Detail Pengadaan</h1>
                <a href="pengadaan_data.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>

            <?php if (isset($pesan_sukses)): ?>
                <div class="alert alert-success" role="alert"><?= $pesan_sukses; ?></div>
            <?php elseif (isset($pesan_error)): ?>
                <div class="alert alert-danger" role="alert"><?= $pesan_error; ?></div>
            <?php elseif (isset($pesan_warning)): ?>
                <div class="alert alert-warning" role="alert"><?= $pesan_warning; ?></div>
            <?php endif; ?>

            <div class="card bg-light shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Kode Transaksi:</strong> <span class="badge bg-dark"><?= $header['kode_transaksi']; ?></span></p>
                            <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($header['tanggal_pengadaan'])); ?></p>
                            <p><strong>Diinput Oleh:</strong> <?= htmlspecialchars($header['user_input_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Vendor Pemasok:</strong> <span class="text-primary fw-bold"><?= htmlspecialchars($header['nama_vendor']); ?></span></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= ($header['status_pengadaan'] == 'Draft' ? 'secondary' : 'success'); ?>"><?= $header['status_pengadaan']; ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!$read_only): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    Tambah Item Pengadaan
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label for="id_barang" class="form-label">Nama Barang</label>
                                <select class="form-select" id="id_barang" name="id_barang" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php 
                                    // Reset pointer result master barang karena mungkin sudah di-fetch sebelumnya
                                    $result_master_barang->data_seek(0);
                                    while($barang = $result_master_barang->fetch_assoc()): ?>
                                        <option value="<?= $barang['id_barang']; ?>">
                                            <?= htmlspecialchars($barang['nama_barang']) . " (" . $barang['satuan'] . ")"; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="kuantitas" class="form-label">Kuantitas</label>
                                <input type="number" class="form-control" id="kuantitas" name="kuantitas" required min="1" value="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
                                <input type="text" class="form-control" id="harga_satuan" name="harga_satuan" required placeholder="Cth: 150000">
                                <small class="text-muted">Masukkan angka tanpa pemisah.</small>
                            </div>
                            <div class="col-md-2 mb-3">
                                <button type="submit" name="tambah_item" class="btn btn-success w-100">Tambah</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <h3>Daftar Barang (Item)</h3>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                    <?php if (!$read_only): ?><th class="text-center">Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php if ($result_detail->num_rows > 0): ?>
                                    <?php 
                                    // Reset pointer untuk loop tampilan
                                    $result_detail->data_seek(0);
                                    while($row = $result_detail->fetch_assoc()): 
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['satuan']); ?></td>
                                            <td class="text-center"><?= number_format($row['jumlah_pesan'], 0, ',', '.'); ?></td>
                                            <td class="text-end">Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td class="text-end">Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                            <?php if (!$read_only): ?>
                                            <td class="text-center">
                                                <a href="?id=<?= $id_pengadaan; ?>&delete_item=<?= $row['id_detail']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus item ini?');">Hapus</a>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                    <tr class="table-primary fw-bold">
                                        <td colspan="5" class="text-end">TOTAL KESELURUHAN</td>
                                        <td class="text-end">Rp <?= number_format($total_biaya, 0, ',', '.'); ?></td>
                                        <?php if (!$read_only): ?><td class="text-center"></td><?php endif; ?>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= $read_only ? '6' : '7'; ?>" class="text-center">Belum ada item barang dalam transaksi ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!$read_only): ?>
            <div class="mt-4 text-end">
                <form method="POST" action="">
                    <button type="submit" name="set_diterima" class="btn btn-lg btn-success" 
                            onclick="return confirm('ANDA YAKIN INGIN MENYELESAIKAN TRANSAKSI INI DAN MENAMBAH STOK BARANG? Aksi ini tidak dapat dibatalkan.');"
                            <?= ($result_detail->num_rows == 0 ? 'disabled' : ''); ?>>
                        <i class="fas fa-check"></i> Selesaikan & Tambah Stok
                    </button>
                    <?php if ($result_detail->num_rows == 0): ?>
                        <small class="text-danger d-block">Tambahkan item barang terlebih dahulu untuk menyelesaikan transaksi.</small>
                    <?php endif; ?>
                </form>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>