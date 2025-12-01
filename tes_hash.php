<?php
// File: test_hash.php

// Password yang kita inginkan
$password_baru = 'simcepat'; 

// Hasilkan hash yang aman
$hash_password = password_hash($password_baru, PASSWORD_DEFAULT);

echo "Password Asli: " . $password_baru . "<br>";
echo "Hash yang akan dimasukkan ke database: <strong>" . $hash_password . "</strong>";

// Contoh output: Hash yang akan Anda salin
// $2y$10$C8.c7oGvU0c5Jt8gE2yNq.8qG3yZ2S5X4T6R8O9L0M1K2I4H3
?>