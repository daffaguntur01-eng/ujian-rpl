<?php
// Konfigurasi database
$host = "localhost";    
$user = "root";         
$password = "";         
$db = "ujian"; // Nama database Anda

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $user, $password, $db);

// Periksa koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>