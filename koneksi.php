<?php
// Konfigurasi Database
$host = "localhost"; // Biasanya localhost
$user = "root";      // User MySQL Anda
$pass = "";          // Password MySQL Anda (Kosong jika tidak ada)
$db   = "sim_pengadaan"; // Nama database yang sudah kita buat

// Buat Koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Set Timezone (Opsional, tapi penting untuk waktu transaksi)
date_default_timezone_set('Asia/Jakarta');
?>