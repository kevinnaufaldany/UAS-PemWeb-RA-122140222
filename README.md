# UAS Pemrograman Web RA
### **Kevin Naufal Dany - 122140222**
### **tokodelfy.web.id**

## **Daftar Fitur**

### **Bagian 1: Client-side Programming**

#### **1.1 Manipulasi DOM dengan JavaScript**
- Membuat form input dengan minimal 4 elemen seperti teks, checkbox, dan radio.
- Menampilkan data dari server ke dalam tabel HTML.
- Melakukan manipulasi DOM menggunakan JavaScript untuk memperbarui elemen secara dinamis.

**Hasil Implementasi:**
![Manipulasi DOM](/public/assets/1.png)

```html
<!-- File: register.php -->
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
```

```javascript
// File: register.php
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
```

#### **1.2 Event Handling**
- Menambahkan minimal 3 event berbeda seperti `onchange`, `onclick`, dan `onsubmit`.
- Validasi input sebelum diproses oleh PHP.

**Hasil Implementasi:**
![Event Handling](/public/assets/2.png)

```javascript
// File: register.php
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
```

### **Bagian 2: Server-side Programming**

#### **2.1 Pengelolaan Data dengan PHP**
- Menggunakan metode POST pada formulir.
- Validasi data di sisi server.
- Menyimpan data termasuk jenis browser dan IP pengguna ke database.

**Hasil Implementasi:**

```php
// File: login.php
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
```

#### **2.2 Objek PHP Berbasis OOP**
- Membuat class dengan metode seperti `User` dan `login`.

**Hasil Implementasi:**

```php
// File: login.php
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
```

### **Bagian 3: Database Management**

#### **3.1 Pembuatan Tabel Database**
- Membuat tabel `karyawan` menggunakan SQL.

**Hasil Implementasi:**

```sql
-- File: db_tokodelfy.sql
CREATE TABLE karyawan
   ID_karyawan VARCHAR(50) PRIMARY KEY,
   nama_karyawan VARCHAR(100),
   email VARCHAR(100) UNIQUE,
   username VARCHAR(50) UNIQUE,
   password VARCHAR(255)
);
```

#### **3.2 Konfigurasi Koneksi Database**
- Menghubungkan PHP ke database menggunakan PDO.

```php
// File: login.php
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
```

### **Bagian 4: State Management**

#### **4.1 State Management dengan Session**
- Menggunakan session untuk menyimpan informasi pengguna.

**Hasil Implementasi:**
![Session Management](/public/assets/6.png)

```php
// File: state_management.php
session_start();

// Ambil data session untuk jumlah klik
$login_count = $_SESSION['login_count'] ?? 0;
$register_count = $_SESSION['register_count'] ?? 0;

if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: state_management.php');
    exit();
}
```

#### **4.2 Pengelolaan State dengan Cookie dan Browser Storage**
- Menyimpan data ke cookie dan memanipulasinya.

**Hasil Implementasi:**
- Buat fungsi untuk menetapkan, mendapatkan, dan menghapus cookie.
![Cookie Management](/public/assets/4.png)

```javascript
// File: browser_storage.html
        // Fungsi untuk set localStorage
        function setLocalStorage() {
            const key = document.getElementById("local_key").value;
            const value = document.getElementById("local_value").value;
            localStorage.setItem(key, value);
            alert(`localStorage '${key}' disimpan dengan nilai: ${value}`);
        }

        // Fungsi untuk get localStorage
        function getLocalStorage() {
            const key = document.getElementById("local_key").value;
            const value = localStorage.getItem(key);
            alert(value ? `localStorage '${key}': ${value}` : `localStorage '${key}' tidak ditemukan.`);
        }

        // Fungsi untuk delete localStorage
        function deleteLocalStorage() {
            const key = document.getElementById("local_key").value;
            localStorage.removeItem(key);
            alert(`localStorage '${key}' dihapus.`);
        }

        // Fungsi untuk set sessionStorage
        function setSessionStorage() {
            const key = document.getElementById("session_key").value;
            const value = document.getElementById("session_value").value;
            sessionStorage.setItem(key, value);
            alert(`sessionStorage '${key}' disimpan dengan nilai: ${value}`);
        }

        // Fungsi untuk get sessionStorage
        function getSessionStorage() {
            const key = document.getElementById("session_key").value;
            const value = sessionStorage.getItem(key);
            alert(value ? `sessionStorage '${key}': ${value}` : `sessionStorage '${key}' tidak ditemukan.`);
        }

        // Fungsi untuk delete sessionStorage
        function deleteSessionStorage() {
            const key = document.getElementById("session_key").value;
            sessionStorage.removeItem(key);
            alert(`sessionStorage '${key}' dihapus.`);
        }
```
- Gunakan browser storage untuk menyimpan informasi secara lokal.
![Cookie Management](/public/assets/5.png)
```php
// File: cookie_management.php
<?php
/**
 * Menetapkan cookie dengan nama, nilai, dan durasi yang diberikan.
 *
 * @param string $name Nama cookie
 * @param string $value Nilai cookie
 * @param int $duration Waktu berlaku cookie dalam detik (default: 3600 detik = 1 jam)
 */
function set_cookie($name, $value, $duration = 3600) {
    // Durasi dalam detik. time() + durasi.
    $expiry = time() + $duration;
    setcookie($name, $value, $expiry, "/"); // Path "/" agar cookie berlaku di seluruh domain
}

/**
 * Mendapatkan nilai dari cookie dengan nama yang diberikan.
 *
 * @param string $name Nama cookie
 * @return string|null Nilai cookie atau null jika tidak ditemukan
 */
function get_cookie($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
}

/**
 * Menghapus cookie dengan nama yang diberikan.
 *
 * @param string $name Nama cookie
 */
function delete_cookie($name) {
    // Set waktu kedaluwarsa ke waktu lampau untuk menghapus cookie
    setcookie($name, "", time() - 3600, "/");
}
?>
```

```php
// test_cookie.php
<?php
require_once 'cookie_management.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        $cookie_value = $_POST['cookie_value'] ?? 'default_value';
        set_cookie($cookie_name, $cookie_value, 3600); // Cookie berlaku 1 jam
        $message = "Cookie '$cookie_name' telah diset dengan nilai: $cookie_value.";
    } elseif (isset($_POST['get_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        $cookie_value = get_cookie($cookie_name);
        $message = $cookie_value ? "Cookie '$cookie_name': $cookie_value" : "Cookie '$cookie_name' tidak ditemukan.";
    } elseif (isset($_POST['delete_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        delete_cookie($cookie_name);
        $message = "Cookie '$cookie_name' telah dihapus.";
    }
}
?>
```

### **Bagian Bonus: Hosting Aplikasi Web**
- Menggunakan hosting seperti GitHub Pages atau Netlify.
- Menyediakan konfigurasi untuk keamanan.

**Hasil Implementasi:**
![Hosting Setup](images/hosting_setup.png)

```bash
# Deploy menggunakan GitHub Pages
$ git init
$ git add .
$ git commit -m "Deploy"
$ git push origin main
```
