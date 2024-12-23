<?php
// Konfigurasi Database
$servername = "localhost";
$username = "root";
$password = "";
$database = "db_tokodelfy";

// Membuat Koneksi
$conn = new mysqli($servername, $username, $password, $database);

// Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil!";
?>