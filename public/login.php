<?php
session_start();

// Tambah counter untuk halaman login
if (!isset($_SESSION['login_count'])) {
    $_SESSION['login_count'] = 1; // Nilai awal
} else {
    $_SESSION['login_count']++; // Tambah setiap kali halaman dikunjungi
}

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "db_tokodelfy";
    public $connection;

    public function __construct()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die("Koneksi gagal: " . $this->connection->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}

class User
{
    private $db;
    private $conn;

    public function __construct($db)
    {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }

    public function login($ID_karyawan, $username, $password)
    {
        // Query untuk mencari user berdasarkan username
        $query = "SELECT * FROM karyawan WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $row['password'])) {
                // Set session
                $_SESSION['ID_karyawan'] = $row['ID_karyawan'];
                $_SESSION['username'] = $row['username'];

                return ["success" => true, "message" => "Login berhasil! Anda akan diarahkan ke dashboard."];
            } else {
                return ["success" => false, "message" => "Password salah. Silakan coba lagi."];
            }
        } else {
            return ["success" => false, "message" => "Username atau ID Karyawan tidak ditemukan."];
        }
    }
}

// Inisialisasi database
$database = new Database();
$user = new User($database);
$message = "";

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['ID_karyawan']) && !empty($_POST['username']) && !empty($_POST['password'])) {
        $ID_karyawan = $_POST['ID_karyawan'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $result = $user->login($ID_karyawan, $username, $password);
        if ($result['success']) {
            $message = '<div class="alert alert-success" role="alert">
                            <div>' . $result['message'] . '</div>
                        </div>';
            header("refresh:0.5;url=dashboard.php");
        } else {
            $message = '<div class="alert alert-danger" role="alert">
                            <div>' . $result['message'] . '</div>
                        </div>';
        }
    } else {
        $message = '<div class="alert alert-danger" role="alert">
                        <div>Semua field harus diisi.</div>
                    </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card card-custom">
            <div class="card-body">
                <!-- <img src="assets/logo.png" alt="Login Logo" class="card-img-top"> -->
                <h3 class="card-title text-center">Login</h3>
                <form id="loginForm" action="login.php" method="POST" class="needs-validation" novalidate>
                    <?php if (!empty($message)) echo $message; ?>
                    <!-- ID Karyawan -->
                    <div class="mb-3">
                        <label for="ID_kar$ID_karyawan" class="form-label">ID Karyawan</label>
                        <input type="text" class="form-control" id="ID_kar$ID_karyawan" name="ID_karyawan" required>
                        <div class="invalid-feedback">
                            Please enter your employee ID.
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

                    <!-- Remember Me -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                        <label for="rememberMe" class="form-check-label">Remember Me</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
            </div>
        </div>
    </div>

    <script>
        // Bootstrap custom validation
        (function() {
            'use strict'
            // Get all forms to apply custom Bootstrap validation
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>

</html>