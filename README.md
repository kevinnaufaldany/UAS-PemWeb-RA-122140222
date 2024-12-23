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
<!-- File: form.html -->
<form id="inputForm">
  <input type="text" id="name" placeholder="Nama">
  <input type="checkbox" id="agree"> Setuju?
  <input type="radio" name="gender" value="L"> Laki-laki
  <input type="radio" name="gender" value="P"> Perempuan
</form>
<table id="dataTable"></table>
```

```javascript
// File: script.js
const form = document.getElementById('inputForm');
const table = document.getElementById('dataTable');

form.addEventListener('submit', (e) => {
  e.preventDefault();
  const name = document.getElementById('name').value;
  const agree = document.getElementById('agree').checked;
  const gender = document.querySelector('input[name="gender"]:checked').value;

  const row = table.insertRow();
  row.insertCell(0).innerText = name;
  row.insertCell(1).innerText = agree ? 'Yes' : 'No';
  row.insertCell(2).innerText = gender;
});
```

#### **1.2 Event Handling**
- Menambahkan minimal 3 event berbeda seperti `onchange`, `onclick`, dan `onsubmit`.
- Validasi input sebelum diproses oleh PHP.

**Hasil Implementasi:**
![Event Handling](images/event_handling.png)

```javascript
// File: validation.js
form.addEventListener('submit', (e) => {
  if (name.trim() === "") {
    alert("Nama tidak boleh kosong");
    e.preventDefault();
  }
});

form.addEventListener('change', (e) => {
  console.log(`${e.target.id} changed.`);
});
```

### **Bagian 2: Server-side Programming**

#### **2.1 Pengelolaan Data dengan PHP**
- Menggunakan metode POST pada formulir.
- Validasi data di sisi server.
- Menyimpan data termasuk jenis browser dan IP pengguna ke database.

**Hasil Implementasi:**
![Server-side Processing](images/server_side.png)

```php
// File: process.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $ip = $_SERVER['REMOTE_ADDR'];
  $browser = $_SERVER['HTTP_USER_AGENT'];

  $stmt = $db->prepare("INSERT INTO users (name, ip, browser) VALUES (?, ?, ?)");
  $stmt->execute([$name, $ip, $browser]);
}
```

#### **2.2 Objek PHP Berbasis OOP**
- Membuat class dengan metode seperti `saveData` dan `getData`.

**Hasil Implementasi:**
![PHP OOP](images/php_oop.png)

```php
// File: User.php
class User {
  private $db;

  public function __construct($dbConnection) {
    $this->db = $dbConnection;
  }

  public function saveData($name, $ip, $browser) {
    $stmt = $this->db->prepare("INSERT INTO users (name, ip, browser) VALUES (?, ?, ?)");
    $stmt->execute([$name, $ip, $browser]);
  }
}
```

### **Bagian 3: Database Management**

#### **3.1 Pembuatan Tabel Database**
- Membuat tabel `users` menggunakan SQL.

**Hasil Implementasi:**
![Database Table](images/database_table.png)

```sql
-- File: create_table.sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  ip VARCHAR(50),
  browser TEXT
);
```

#### **3.2 Konfigurasi Koneksi Database**
- Menghubungkan PHP ke database menggunakan PDO.

```php
// File: db.php
try {
  $db = new PDO('mysql:host=localhost;dbname=tokodelfy', 'root', '');
} catch (PDOException $e) {
  die("Koneksi gagal: " . $e->getMessage());
}
```

### **Bagian 4: State Management**

#### **4.1 State Management dengan Session**
- Menggunakan session untuk menyimpan informasi pengguna.

**Hasil Implementasi:**
![Session Management](images/session_management.png)

```php
// File: session.php
session_start();
$_SESSION['user'] = "Kevin";
$_SESSION['role'] = "Admin";
```

#### **4.2 Pengelolaan State dengan Cookie dan Browser Storage**
- Menyimpan data ke cookie dan memanipulasinya.

**Hasil Implementasi:**
![Cookie Management](images/cookie_management.png)

```php
// File: cookie.php
setcookie("username", "Kevin", time() + 3600, "/");
echo $_COOKIE['username'];
```

```javascript
// File: storage.js
localStorage.setItem("theme", "dark");
console.log(localStorage.getItem("theme"));
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
