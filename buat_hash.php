<?php
// File: buat_hash.php

// 1. Password yang ingin kita buat hash-nya
$password_plain = 'admin123';

// 2. Buat hash menggunakan fungsi PHP yang aman
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

echo "Password Asli: " . $password_plain . "<br>";
echo "Hash Baru: <strong>" . $password_hash . "</strong><br><br>";
echo "Salin kode hash di atas untuk di-UPDATE ke database Anda.";
?>