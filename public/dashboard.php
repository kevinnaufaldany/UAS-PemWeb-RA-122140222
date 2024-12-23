<?php
// Mulai session untuk memeriksa apakah pengguna sudah login
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['ID_karyawan'])) {
    // Jika tidak login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_tokodelfy"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk menghitung jumlah karyawan
$sql = "SELECT COUNT(*) as total_karyawan FROM karyawan";
$result = $conn->query($sql);

$total_karyawan = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_karyawan = $row['total_karyawan'];
}
// Statistik barang
$stats_query = "SELECT 
                COUNT(*) AS jumlah_jenis, 
                SUM(total_barang) AS total_barang, 
                SUM(harga_barang * total_barang) AS total_harga 
                FROM barang";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Query untuk menghitung total barang rusak
$query = "SELECT SUM(total_barang_rusak) AS total_barang_rusak FROM barang_rusak";
$result = mysqli_query($conn, $query);

// Ambil hasil query
$total_barang_rusak = 0;
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_barang_rusak = $row['total_barang_rusak'];
}

$total_harga_barang = $stats['total_harga'];
$total_kerugian_rusak = 0;

$kerugian_query = "
    SELECT SUM(barang_rusak.total_barang_rusak * barang.harga_barang) AS total_kerugian
    FROM barang_rusak 
    INNER JOIN barang ON barang_rusak.ID_barang = barang.ID_barang";
$kerugian_result = $conn->query($kerugian_query);

if ($kerugian_result && $kerugian_result->num_rows > 0) {
    $kerugian_row = $kerugian_result->fetch_assoc();
    $total_kerugian_rusak = $kerugian_row['total_kerugian'];
}

$total_aset = $total_harga_barang - $total_kerugian_rusak;

// barang rusak
// Ambil jumlah total barang rusak untuk pagination
$query = "SELECT COUNT(*) as total FROM barang_rusak";
$result = $conn->query($query);
$total_rows = $result->fetch_assoc()['total'];

$rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 10;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $rows_per_page;

// Ambil data barang rusak dari database
$query = "SELECT * FROM barang_rusak LIMIT $start_from, $rows_per_page";
$result = $conn->query($query);

$message = ""; // Untuk menyimpan pesan alert

// Menangani submit form Tambah Barang Rusak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang_rusak'])) {
    $ID_barang = $_POST['ID_barang'];
    $total_barang_rusak = $_POST['total_barang_rusak'];
    $keterangan = $_POST['keterangan'];

    $check_query = "SELECT ID_barang FROM barang WHERE ID_barang = '$ID_barang'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows == 0) {
        $message = '<div class="alert alert-danger" role="alert">ID Barang tidak ditemukan! Silakan gunakan ID yang valid.</div>';
    } else {
        $query = "INSERT INTO barang_rusak (ID_barang, total_barang_rusak, keterangan) 
                  VALUES ('$ID_barang', '$total_barang_rusak', '$keterangan')";

        if ($conn->query($query) === TRUE) {
            $message = '<div class="alert alert-success" role="alert">Barang rusak berhasil ditambahkan!</div>';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = '<div class="alert alert-danger" role="alert">Gagal menambahkan barang rusak: ' . $conn->error . '</div>';
        }
    }
}

// Query data barang rusak dengan join untuk mendapatkan nama barang
$query_rusak = "SELECT 
    barang_rusak.ID_barang_rusak, 
    barang_rusak.ID_barang, 
    barang.nama_barang, 
    barang_rusak.total_barang_rusak, 
    barang_rusak.keterangan
FROM barang_rusak
INNER JOIN barang ON barang_rusak.ID_barang = barang.ID_barang
ORDER BY barang_rusak.ID_barang_rusak ASC
LIMIT $start_from, $rows_per_page";

$result_rusak = $conn->query($query_rusak);

// Query untuk menghitung statistik barang rusak
$query_stats_rusak = "SELECT 
    COUNT(DISTINCT ID_barang) AS jumlah_jenis,
    SUM(total_barang_rusak) AS total_barang_rusak
FROM barang_rusak";

$result_stats_rusak = $conn->query($query_stats_rusak);

if ($result_stats_rusak && $result_stats_rusak->num_rows > 0) {
    $stats_rusak = $result_stats_rusak->fetch_assoc();
} else {
    $stats_rusak = ['jumlah_jenis' => 0, 'total_barang_rusak' => 0];
}

// Proses Edit Barang Rusak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang_rusak'])) {
   $id_barang_rusak = $_POST['id_barang_rusak'];
   $total_barang_rusak = $_POST['total_barang_rusak'];
   $keterangan = $_POST['keterangan'];

   $query = "UPDATE barang_rusak SET 
             total_barang_rusak='$total_barang_rusak', 
             keterangan='$keterangan' 
             WHERE ID_barang_rusak='$id_barang_rusak'";

   if ($conn->query($query) === TRUE) {
       $message = '<div class="alert alert-success" role="alert">Barang rusak berhasil diperbarui!</div>';
       header("Location: " . $_SERVER['PHP_SELF']); // Redirect setelah update
       exit;
   } else {
       $message = '<div class="alert alert-danger" role="alert">Gagal memperbarui barang rusak: ' . $conn->error . '</div>';
   }
}

// Proses Delete Barang Rusak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_barang_rusak'])) {
   $id_barang_rusak = $_POST['id_barang_rusak'];

   $query = "DELETE FROM barang_rusak WHERE ID_barang_rusak = '$id_barang_rusak'";

   if ($conn->query($query) === TRUE) {
       $message = '<div class="alert alert-success" role="alert">Barang rusak berhasil dihapus!</div>';
       header("Location: " . $_SERVER['PHP_SELF']); // Redirect setelah update
       exit;
   } else {
       $message = '<div class="alert alert-danger" role="alert">Gagal menghapus barang rusak: ' . $conn->error . '</div>';
   }
}

$rows_per_page = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : 10;

// Query untuk menghitung total harga kerugian barang rusak
$query_total_rusak = "
    SELECT SUM(barang_rusak.total_barang_rusak * barang.harga_barang) AS total_kerugian
    FROM barang_rusak 
    INNER JOIN barang ON barang_rusak.ID_barang = barang.ID_barang";

$result_total_rusak = $conn->query($query_total_rusak);

if ($result_total_rusak && $result_total_rusak->num_rows > 0) {
    $data_rusak = $result_total_rusak->fetch_assoc();
    $total_kerugian = $data_rusak['total_kerugian'];
} else {
    $total_kerugian = 0;
}

// barang
// Ambil jumlah total barang untuk pagination
$query = "SELECT COUNT(*) as total FROM barang";
$result = $conn->query($query);
$total_rows = $result->fetch_assoc()['total'];

$rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 10;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $rows_per_page;

// Ambil data barang dari database
$query = "SELECT * FROM barang LIMIT $start_from, $rows_per_page";
$result = $conn->query($query);

$message = ""; // Untuk menyimpan pesan alert

// Menangani submit form Tambah Barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang'])) {
    // Ambil data dari form
    $ID_barang = $_POST['ID_barang'];
    $nama_barang = $_POST['nama_barang'];
    $harga_barang = $_POST['harga_barang'];
    $total_barang = $_POST['total_barang'];

    // Query untuk menambah barang ke dalam tabel
    $query = "INSERT INTO barang (ID_barang, nama_barang, harga_barang, total_barang) 
              VALUES ('$ID_barang', '$nama_barang', '$harga_barang', '$total_barang')";

    // Periksa apakah ID_barang sudah ada
    $check_query = "SELECT ID_barang FROM barang WHERE ID_barang = '$ID_barang'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        // Jika ID_barang sudah ada
        $message = '<div class="alert alert-danger" role="alert">ID Barang sudah ada! Silakan gunakan ID yang berbeda.</div>';

    } else {
        // Jika ID_barang belum ada, lakukan INSERT
        $query = "INSERT INTO barang (ID_barang, nama_barang, harga_barang, total_barang) 
                  VALUES ('$ID_barang', '$nama_barang', '$harga_barang', '$total_barang')";

        if ($conn->query($query) === TRUE) {
            $message = '<div class="alert alert-success" role="alert">Barang berhasil ditambahkan!</div>';
        } else {
            $message = '<div class="alert alert-danger" role="alert">Gagal menambahkan barang: ' . $conn->error . '</div>';
        }
    }
}

// Menangani submit form Edit Barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang'])) {
    $ID_barang = $_POST['ID_barang'];
    $nama_barang = $_POST['nama_barang'];
    $harga_barang = $_POST['harga_barang'];
    $total_barang = $_POST['total_barang'];

    $query = "UPDATE barang SET 
              nama_barang='$nama_barang', 
              harga_barang='$harga_barang', 
              total_barang='$total_barang' 
              WHERE ID_barang='$ID_barang'";

    if ($conn->query($query) === TRUE) {
        $message = '<div class="alert alert-success" role="alert">Barang berhasil diperbarui!</div>';
    } else {
        $message = '<div class="alert alert-danger" role="alert">Gagal memperbarui barang: ' . $conn->error . '</div>';
    }
}

// Statistik barang
$stats_query = "SELECT 
                COUNT(*) AS jumlah_jenis, 
                SUM(total_barang) AS total_barang, 
                SUM(harga_barang * total_barang) AS total_harga 
                FROM barang";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Toko Delfy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><?php echo $_SESSION['ID_karyawan']; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/server/logout.php">Logout <i class="ri-logout-box-r-line"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Dashboard Content -->
    <div class="container text-center">
        <h3 style="margin-bottom: 30px;"></h3>
        <div class="row g-2">
        <!-- Statistik (Right Side) -->
            <div class="row">
                <!-- Card 1: Total Karyawan -->
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Karyawan</h5>
                            <p class="card-text"><?php echo $total_karyawan; ?></p>
                        </div>
                    </div>
                </div>
                    
                <!-- Card 2: Total Aset -->
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Aset</h5>
                            <p class="card-text">
                                <?php 
                                    echo "Rp " . number_format($total_aset, 2, ',', '.');
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Card 3: Total Barang  -->
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-warning" onclick="window.location.href='barang.php'" style="cursor: pointer;">
                        <div class="card-body">
                            <h5 class="card-title">Total Jenis Barang </h5>
                            <p class="card-text">
                                <?php echo $stats['jumlah_jenis']; ?> 
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Total Barang Rusak -->
                <div class="col-md-6 mb-4">
                    <div class="card text-white bg-danger" onclick="window.location.href='barang_rusak.php'" style="cursor: pointer;">
                        <div class="card-body">
                            <h5 class="card-title">Total Barang Rusak</h5>
                            <p class="card-text">
                                <?php echo $total_barang_rusak; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- barang -->
    <div class="container mt-5">
        <h3 style="margin-bottom: 30px;">Barang Rusak</h3>
        <!-- Filter and Search -->
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text">Show</span>
                <select class="form-select" id="entriesSelect" onchange="filterEntries()">
                    <option value="10" <?php if ($rows_per_page == 10) echo "selected"; ?>>10</option>
                    <option value="25" <?php if ($rows_per_page == 25) echo "selected"; ?>>25</option>
                    <option value="50" <?php if ($rows_per_page == 50) echo "selected"; ?>>50</option>
                    <option value="100" <?php if ($rows_per_page == 100) echo "selected"; ?>>100</option>
                </select>
                <span class="input-group-text">entries</span>
            </div>

            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Cari Data" onkeyup="searchTable()">
            </div>
        </div>

        <!-- Tabel Data Barang -->
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga Barang</th>
                        <th>Total Barang</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['ID_barang']; ?></td>
                        <td><?php echo $row['nama_barang']; ?></td>
                        <td><?php echo $row['harga_barang']; ?></td>
                        <td><?php echo $row['total_barang']; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end" id="pagination">
                <?php
                $total_pages = ceil($total_rows / $rows_per_page);
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo "<li class='page-item'><a class='page-link' href='?page=$i&rows_per_page=$rows_per_page'>$i</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>

<!-- barang rusak -->
    <!-- Filter and Search -->
    <div class="container mt-5">
        <h3 style="margin-bottom: 30px;">Barang Rusak</h3>
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text">Show</span>
                <select class="form-select" id="entriesSelect" onchange="filterEntries()">
                    <option value="10" <?php if ($rows_per_page == 10) echo "selected"; ?>>10</option>
                    <option value="25" <?php if ($rows_per_page == 25) echo "selected"; ?>>25</option>
                    <option value="50" <?php if ($rows_per_page == 50) echo "selected"; ?>>50</option>
                    <option value="100" <?php if ($rows_per_page == 100) echo "selected"; ?>>100</option>
                </select>
                <span class="input-group-text">entries</span>
            </div>
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Cari Data" onkeyup="searchTable()">
            </div>
        </div>

        <!-- Tabel Data Barang Rusak -->
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Barang Rusak</th>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Barang Rusak</th>
                        <th>Keterangan</th>
                        <!-- <th>Aksi</th> -->
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php if ($result_rusak && $result_rusak->num_rows > 0) { 
                  while ($row = $result_rusak->fetch_assoc()) { ?>
                    <tr>
                            <td><?php echo $row['ID_barang_rusak']; ?></td>
                            <td><?php echo $row['ID_barang']; ?></td>
                            <td><?php echo $row['nama_barang']; ?></td>
                            <td><?php echo $row['total_barang_rusak']; ?></td>
                            <td><?php echo $row['keterangan']; ?></td>
                        </tr>
                    <?php }
                     } else { ?>
                        <tr>
                           <td colspan="5" class="text-center">Tidak ada data barang rusak</td>
                        </tr>
                     <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end" id="pagination">
                <?php
                $total_pages = ceil($total_rows / $rows_per_page);
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo "<li class='page-item'><a class='page-link' href='?page=$i&rows_per_page=$rows_per_page'>$i</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>
    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <p>&copy; 2024 UAS Pemograman Web RA. Kevin Naufal Dany.</p>
    </footer>

    <!-- JS Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        function filterEntries() {
            var entriesSelect = document.getElementById('entriesSelect');
            var selectedValue = entriesSelect.value;
            window.location.href = "?rows_per_page=" + selectedValue;
        }

        function searchTable() {
            // Ambil nilai input dan ubah menjadi huruf kapital untuk pencarian case-insensitive
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("tableBody"); // Targetkan tbody
            var tr = table.getElementsByTagName("tr"); // Ambil semua baris di tbody

            // Loop untuk setiap baris di tabel
            for (var i = 0; i < tr.length; i++) {
               var visible = false; // Default: baris tidak terlihat
               var td = tr[i].getElementsByTagName("td"); // Ambil semua kolom di baris

               // Loop untuk setiap kolom di baris
               for (var j = 0; j < td.length; j++) {
                     if (td[j]) {
                        var txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                           visible = true; // Baris terlihat jika ada kecocokan
                           break;
                        }
                     }
               }

               // Tampilkan atau sembunyikan baris berdasarkan hasil pencarian
               tr[i].style.display = visible ? "" : "none";
            }
         }


        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var total = button.getAttribute('data-total');
            var keterangan = button.getAttribute('data-keterangan');

            var modalIdInput = document.getElementById('edit_id_barang_rusak');
            var modalTotalInput = document.getElementById('edit_total_barang_rusak');
            var modalKeteranganInput = document.getElementById('edit_keterangan');

            modalIdInput.value = id;
            modalTotalInput.value = total;
            modalKeteranganInput.value = keterangan;
        });

                // Function for handling pagination and filtering entries per page
                function filterEntries() {
            var entries = document.getElementById('entriesSelect').value;
            window.location.href = '?page=1&rows_per_page=' + entries;
        }

        // Function for handling search table
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("tableBody");
            tr = table.getElementsByTagName("tr");
            
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1]; // searching by column "Nama Barang"
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        var editModal = document.getElementById('editBarangModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var idBarang = button.getAttribute('data-id');
            var namaBarang = button.getAttribute('data-nama');
            var hargaBarang = button.getAttribute('data-harga');
            var totalBarang = button.getAttribute('data-total');

            var modalId = editModal.querySelector('#edit_ID_barang');
            var modalNama = editModal.querySelector('#edit_nama_barang');
            var modalHarga = editModal.querySelector('#edit_harga_barang');
            var modalTotal = editModal.querySelector('#edit_total_barang');

            modalId.value = idBarang;
            modalNama.value = namaBarang;
            modalHarga.value = hargaBarang;
            modalTotal.value = totalBarang;
        });
    </script>
</body>
</html>
