<?php
// File: buat_user_pasti.php

// 1. Definisikan koneksi (ambil dari koneksi.php)
$conn = new mysqli("localhost", "root", "", "sim_pengadaan");
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// 2. Data User Baru
$username_baru = 'superadmin'; // Username baru
$password_plain = 'simok2025';  // Password yang akan Anda gunakan
$nama = 'Super Admin Proyek';
$hak_akses = 'Admin';

// 3. Buat Hash yang Aman
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// 4. Masukkan ke Database
$stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, hak_akses) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username_baru, $password_hash, $nama, $hak_akses);

if ($stmt->execute()) {
    echo "<h2>✅ SELAMAT! User baru berhasil ditambahkan!</h2>";
    echo "<h3>Detail Login Anda:</h3>";
    echo "<ul>";
    echo "<li>**Username:** <code>" . $username_baru . "</code></li>";
    echo "<li>**Password:** <code>" . $password_plain . "</code></li>";
    echo "</ul>";
    echo "<p>Silakan hapus file ini setelah berhasil login.</p>";
} else {
    echo "<h2>❌ GAGAL MENAMBAHKAN USER!</h2>";
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>