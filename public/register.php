<?php
session_start();

// Tambah counter untuk halaman register
if (!isset($_SESSION['register_count'])) {
    $_SESSION['register_count'] = 1; // Nilai awal
} else {
    $_SESSION['register_count']++; // Tambah setiap kali halaman dikunjungi
}

// Koneksi ke database
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$database = "db_tokodelfy";

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$message = ""; // Untuk menyimpan pesan alert

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ID_karyawan = $conn->real_escape_string($_POST['ID_karyawan']);
    $nama_karyawan = $conn->real_escape_string($_POST['nama_karyawan']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // Validasi password
    if ($password !== $confirm_password) {
        $message = '<div class="alert alert-danger" role="alert">
                        <div>Password dan konfirmasi password tidak cocok.</div>
                    </div>';
    } else {
        // Periksa apakah ID_karyawan sudah ada
        $check_id_query = "SELECT * FROM karyawan WHERE ID_karyawan = '$ID_karyawan' OR email = '$email'";
        $result = $conn->query($check_id_query);

        if ($result->num_rows > 0) {
            // Jika ID_karyawan atau Email sudah ada
            $message = '<div class="alert alert-danger" role="alert">
                            <div><strong>ID Karyawan</strong> atau <strong>Email</strong> sudah terdaftar. Silakan gunakan data lain.</div>
                        </div>';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Query untuk insert data
            $query = "INSERT INTO karyawan (ID_karyawan, nama_karyawan, email, username, password) 
                      VALUES ('$ID_karyawan', '$nama_karyawan', '$email', '$username', '$hashed_password')";

            if ($conn->query($query) === TRUE) {
                $message = '<div class="alert alert-success d-flex" role="alert">
                                <div>Registrasi berhasil! Silakan <a href="login.php" class="alert-link">login</a>.</div>
                            </div>';
            } else {
                // Jika terjadi kesalahan lain
                $message = '<div class="alert alert-danger" role="alert">
                                <div>Terjadi kesalahan: ' . $conn->error . '</div>
                            </div>';
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card card-custom"> <!-- Lebar lebih besar, ganti w-50 menjadi w-75 -->
            <div class="card-body">
                <?php if (!empty($message)) echo $message; ?>
                <h5 class="card-title text-center">Register</h5>
                <form id="registerForm" action="register.php" method="POST" class="needs-validation" novalidate>
                    <!-- ID Karyawan -->
                    <div class="mb-3">
                        <label for="ID_karyawan" class="form-label">ID Karyawan</label>
                        <input type="text" class="form-control" id="ID_karyawan" name="ID_karyawan" required>
                        <div class="invalid-feedback">
                            Please enter your employee ID.
                        </div>
                    </div>
                    <!-- Nama Karyawan -->
                    <div class="mb-3">
                        <label for="nama_karyawan" class="form-label">Nama Karyawan</label>
                        <input type="text" class="form-control" id="nama_karyawan" name="nama_karyawan" required>
                        <div class="invalid-feedback">
                            Please enter your name.
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="invalid-feedback">
                            Please enter your username.
                        </div>
                    </div>
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>
                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        <div class="invalid-feedback">
                            Please confirm your password.
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
        </div>
    </div>

    <script>
        // Bootstrap custom validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        document.getElementById('registerForm').addEventListener('submit', function (event) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm-password').value;
            if (password !== confirmPassword) {
                event.preventDefault();
                event.stopPropagation();
                var confirmPasswordField = document.getElementById('confirm-password');
                confirmPasswordField.setCustomValidity('Passwords do not match');
                confirmPasswordField.classList.add('is-invalid');
            } else {
                document.getElementById('confirm-password').setCustomValidity('');
            }
        });
    </script>
</body>
</html>

