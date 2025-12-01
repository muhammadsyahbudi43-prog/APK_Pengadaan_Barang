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
if (isset($_POST['tambah_barang'])) {
    $nama_barang = $conn->real_escape_string($_POST['nama_barang']);
    $satuan = $conn->real_escape_string($_POST['satuan']);
    $stok_awal = (int)$_POST['stok_awal']; 

    $sql_insert = "INSERT INTO barang (nama_barang, satuan, stok_saat_ini) VALUES ('$nama_barang', '$satuan', $stok_awal)";

    if ($conn->query($sql_insert) === TRUE) {
        $pesan_sukses = "Barang **$nama_barang** berhasil ditambahkan!";
    } else {
        $pesan_error = "Error: " . $conn->error;
    }
}

// =======================================================
// 2. FUNGSI UBAH DATA (UPDATE)
// =======================================================
if (isset($_POST['edit_barang'])) {
    $id_barang = (int)$_POST['id_barang'];
    $nama_barang = $conn->real_escape_string($_POST['nama_barang_edit']);
    $satuan = $conn->real_escape_string($_POST['satuan_edit']);
    $stok_saat_ini = (int)$_POST['stok_saat_ini_edit'];

    $sql_update = "UPDATE barang SET 
                    nama_barang = '$nama_barang', 
                    satuan = '$satuan', 
                    stok_saat_ini = $stok_saat_ini
                   WHERE id_barang = $id_barang";

    if ($conn->query($sql_update) === TRUE) {
        $pesan_sukses = "Data Barang **$nama_barang** berhasil diubah!";
    } else {
        $pesan_error = "Error saat mengubah data: " . $conn->error;
    }
}

// =======================================================
// 3. FUNGSI HAPUS DATA (DELETE)
// =======================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_barang = (int)$_GET['id'];
    
    // Perlu cek apakah barang digunakan di tabel pengadaan_detail
    $sql_delete = "DELETE FROM barang WHERE id_barang = $id_barang";
    
    if ($conn->query($sql_delete) === TRUE) {
        $pesan_sukses = "Barang berhasil dihapus.";
    } else {
        $pesan_error = "Gagal menghapus barang. Mungkin data ini sudah digunakan dalam transaksi pengadaan.";
    }
}

// =======================================================
// 4. FUNGSI TAMPIL DATA (READ)
// =======================================================
$sql_select = "SELECT * FROM barang ORDER BY nama_barang ASC";
$result_barang = $conn->query($sql_select);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Barang - SIM Pengadaan</title>
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
                    <a class="nav-link active text-white fw-bold" href="master_barang.php">Master Barang</a>
                    <a class="nav-link" href="master_vendor.php">Master Vendor</a>
                    <a class="nav-link" href="pengadaan_data.php">Pengadaan Barang</a>
                    <a class="nav-link" href="laporan_bulanan.php">Laporan Pengadaan</a>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Master Barang</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                    <i class="fas fa-plus"></i> Tambah Barang
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
                    Data Barang Inventaris
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php if ($result_barang->num_rows > 0): ?>
                                    <?php while($row = $result_barang->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                            <td><?= htmlspecialchars($row['satuan']); ?></td>
                                            <td><span class="badge bg-success"><?= htmlspecialchars($row['stok_saat_ini']); ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditBarang"
                                                        data-id="<?= $row['id_barang']; ?>"
                                                        data-nama="<?= $row['nama_barang']; ?>"
                                                        data-satuan="<?= $row['satuan']; ?>"
                                                        data-stok="<?= $row['stok_saat_ini']; ?>">
                                                    Edit
                                                </button>
                                                <a href="?aksi=hapus&id=<?= $row['id_barang']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus barang <?= $row['nama_barang']; ?>?');">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data barang.</td>
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

<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-labelledby="modalTambahBarangLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahBarangLabel">Form Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    </div>
                    <div class="mb-3">
                        <label for="satuan" class="form-label">Satuan</label>
                        <select class="form-select" id="satuan" name="satuan" required>
                            <option value="">-- Pilih Satuan --</option>
                            <option value="PCS">PCS</option>
                            <option value="UNIT">UNIT</option>
                            <option value="BOX">BOX</option>
                            <option value="SET">SET</option>
                            <option value="LITER">LITER</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stok_awal" class="form-label">Stok Awal</label>
                        <input type="number" class="form-control" id="stok_awal" name="stok_awal" value="0" min="0" required>
                        <small class="form-text text-muted">Masukkan jumlah stok barang saat pertama kali diinput.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="tambah_barang" class="btn btn-primary">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditBarang" tabindex="-1" aria-labelledby="modalEditBarangLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditBarangLabel">Form Ubah Data Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="id_barang" id="id_barang_edit">
                    <div class="mb-3">
                        <label for="nama_barang_edit" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang_edit" name="nama_barang_edit" required>
                    </div>
                    <div class="mb-3">
                        <label for="satuan_edit" class="form-label">Satuan</label>
                        <select class="form-select" id="satuan_edit" name="satuan_edit" required>
                            <option value="PCS">PCS</option>
                            <option value="UNIT">UNIT</option>
                            <option value="BOX">BOX</option>
                            <option value="SET">SET</option>
                            <option value="LITER">LITER</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stok_saat_ini_edit" class="form-label">Stok Saat Ini</label>
                        <input type="number" class="form-control" id="stok_saat_ini_edit" name="stok_saat_ini_edit" min="0" required>
                        <small class="form-text text-muted">Hanya ubah ini jika ada penyesuaian fisik inventaris.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="edit_barang" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEditBarang = document.getElementById('modalEditBarang');
    modalEditBarang.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Tombol yang memicu modal
        
        // Ambil data dari atribut data-*
        var id = button.getAttribute('data-id');
        var nama = button.getAttribute('data-nama');
        var satuan = button.getAttribute('data-satuan');
        var stok = button.getAttribute('data-stok');
        
        // Isi data ke elemen form di dalam modal
        var modalTitle = modalEditBarang.querySelector('.modal-title');
        var idInput = modalEditBarang.querySelector('#id_barang_edit');
        var namaInput = modalEditBarang.querySelector('#nama_barang_edit');
        var satuanSelect = modalEditBarang.querySelector('#satuan_edit');
        var stokInput = modalEditBarang.querySelector('#stok_saat_ini_edit');
        
        modalTitle.textContent = 'Ubah Data Barang: ' + nama;
        idInput.value = id;
        namaInput.value = nama;
        stokInput.value = stok;
        satuanSelect.value = satuan; // Memilih opsi di dropdown
    });
});
</script>
</body>
</html>