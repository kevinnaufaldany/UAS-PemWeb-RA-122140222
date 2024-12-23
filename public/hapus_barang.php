<?php
session_start(); // Mulai session

// Koneksi ke database
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "db_tokodelfy";

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $ID_barang = $_GET['id'];

    // Query untuk menghapus barang
    $query = "DELETE FROM barang WHERE ID_barang = '$ID_barang'";

    if ($conn->query($query) === TRUE) {
        // Simpan pesan sukses ke session
        $_SESSION['message'] = '<div class="alert alert-success" role="alert">Barang berhasil dihapus!</div>';
    } else {
        // Simpan pesan error ke session
        $_SESSION['message'] = '<div class="alert alert-danger" role="alert">Gagal menghapus barang: ' . $conn->error . '</div>';
    }
}

$conn->close();

// Redirect kembali ke halaman barang.php
header("Location: barang.php");
exit();
?>
