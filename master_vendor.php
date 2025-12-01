<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// =======================================================
// 1. FUNGSI TAMBAH DATA (CREATE)
// =======================================================
if (isset($_POST['tambah_vendor'])) {
    $nama_vendor = $conn->real_escape_string($_POST['nama_vendor']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $email = $conn->real_escape_string($_POST['email']);

    $sql_insert = "INSERT INTO vendor (nama_vendor, alamat, telepon, email) VALUES ('$nama_vendor', '$alamat', '$telepon', '$email')";

    if ($conn->query($sql_insert) === TRUE) {
        $pesan_sukses = "Vendor **$nama_vendor** berhasil ditambahkan!";
    } else {
        $pesan_error = "Error: " . $conn->error;
    }
}

// =======================================================
// 2. FUNGSI UBAH DATA (UPDATE)
// =======================================================
if (isset($_POST['edit_vendor'])) {
    $id_vendor = (int)$_POST['id_vendor'];
    $nama_vendor = $conn->real_escape_string($_POST['nama_vendor_edit']);
    $alamat = $conn->real_escape_string($_POST['alamat_edit']);
    $telepon = $conn->real_escape_string($_POST['telepon_edit']);
    $email = $conn->real_escape_string($_POST['email_edit']);

    $sql_update = "UPDATE vendor SET 
                    nama_vendor = '$nama_vendor', 
                    alamat = '$alamat', 
                    telepon = '$telepon', 
                    email = '$email'
                   WHERE id_vendor = $id_vendor";

    if ($conn->query($sql_update) === TRUE) {
        $pesan_sukses = "Data Vendor **$nama_vendor** berhasil diubah!";
    } else {
        $pesan_error = "Error saat mengubah data: " . $conn->error;
    }
}

// =======================================================
// 3. FUNGSI HAPUS DATA (DELETE)
// =======================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_vendor = (int)$_GET['id'];
    
    // PERINGATAN! Perlu cek apakah vendor digunakan di tabel pengadaan_header
    // Jika digunakan, hapus akan gagal (foreign key constraint)
    $sql_delete = "DELETE FROM vendor WHERE id_vendor = $id_vendor";
    
    if ($conn->query($sql_delete) === TRUE) {
        $pesan_sukses = "Vendor berhasil dihapus.";
    } else {
        $pesan_error = "Gagal menghapus vendor. Mungkin data ini sudah digunakan dalam transaksi pengadaan.";
    }
}

// =======================================================
// 4. FUNGSI TAMPIL DATA (READ)
// =======================================================
$sql_select = "SELECT * FROM vendor ORDER BY nama_vendor ASC";
$result_vendor = $conn->query($sql_select);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Vendor - SIM Pengadaan</title>
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
                    <a class="nav-link active text-white fw-bold" href="master_vendor.php">Master Vendor</a>
                    <a class="nav-link" href="pengadaan_data.php">Pengadaan Barang</a>
                    <a class="nav-link" href="laporan_bulanan.php">Laporan Pengadaan</a>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Master Vendor</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahVendor">
                    <i class="fas fa-plus"></i> Tambah Vendor
                </button>
            </div>

            <?php if (isset($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $pesan_sukses; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    Data Vendor Pemasok
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Vendor</th>
                                    <th>Alamat</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php if ($result_vendor->num_rows > 0): ?>
                                    <?php while($row = $result_vendor->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_vendor']); ?></td>
                                            <td><?= htmlspecialchars($row['alamat']); ?></td>
                                            <td><?= htmlspecialchars($row['telepon']); ?></td>
                                            <td><?= htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditVendor"
                                                        data-id="<?= $row['id_vendor']; ?>"
                                                        data-nama="<?= $row['nama_vendor']; ?>"
                                                        data-alamat="<?= $row['alamat']; ?>"
                                                        data-telp="<?= $row['telepon']; ?>"
                                                        data-email="<?= $row['email']; ?>">
                                                    Edit
                                                </button>
                                                <a href="?aksi=hapus&id=<?= $row['id_vendor']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus vendor <?= $row['nama_vendor']; ?>?');">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data vendor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<div class="modal fade" id="modalTambahVendor" tabindex="-1" aria-labelledby="modalTambahVendorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahVendorLabel">Form Tambah Vendor Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_vendor" class="form-label">Nama Vendor</label>
                        <input type="text" class="form-control" id="nama_vendor" name="nama_vendor" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="tambah_vendor" class="btn btn-primary">Simpan Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditVendor" tabindex="-1" aria-labelledby="modalEditVendorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditVendorLabel">Form Ubah Data Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="id_vendor" id="id_vendor_edit">
                    <div class="mb-3">
                        <label for="nama_vendor_edit" class="form-label">Nama Vendor</label>
                        <input type="text" class="form-control" id="nama_vendor_edit" name="nama_vendor_edit" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat_edit" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat_edit" name="alamat_edit" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="telepon_edit" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon_edit" name="telepon_edit">
                    </div>
                    <div class="mb-3">
                        <label for="email_edit" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_edit" name="email_edit">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="edit_vendor" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEditVendor = document.getElementById('modalEditVendor');
    modalEditVendor.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Tombol yang memicu modal
        
        // Ambil data dari atribut data-*
        var id = button.getAttribute('data-id');
        var nama = button.getAttribute('data-nama');
        var alamat = button.getAttribute('data-alamat');
        var telp = button.getAttribute('data-telp');
        var email = button.getAttribute('data-email');
        
        // Isi data ke elemen form di dalam modal
        var modalTitle = modalEditVendor.querySelector('.modal-title');
        var idInput = modalEditVendor.querySelector('#id_vendor_edit');
        var namaInput = modalEditVendor.querySelector('#nama_vendor_edit');
        var alamatInput = modalEditVendor.querySelector('#alamat_edit');
        var telpInput = modalEditVendor.querySelector('#telepon_edit');
        var emailInput = modalEditVendor.querySelector('#email_edit');
        
        modalTitle.textContent = 'Ubah Data Vendor: ' + nama;
        idInput.value = id;
        namaInput.value = nama;
        alamatInput.value = alamat;
        telpInput.value = telp;
        emailInput.value = email;
    });
});
</script>
</body>
</html>