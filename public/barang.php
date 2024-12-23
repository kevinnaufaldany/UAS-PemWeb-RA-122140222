<?php
// Mulai session untuk memeriksa apakah pengguna sudah login
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['ID_karyawan'])) {
    // Jika tidak login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom Styles */
        .table-container {
            margin-top: 20px;
        }
        .pagination {
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/public/dashboard.php">Toko Delfy</a>
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

    <div class="container mt-4">
    <?php if (!empty($message)) echo $message; ?>

    <!-- Statistik Barang -->
    <div class="row mb-4">
        <h3 style="margin-bottom: 30px;">Barang di Toko Defy</h3>
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Jenis Barang</h5>
                    <p class="card-text">
                        <?php echo $stats['jumlah_jenis']; ?> Jenis
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Barang</h5>
                    <p class="card-text">
                        <?php echo $stats['total_barang']; ?> Unit
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Harga Barang</h5>
                    <p class="card-text">
                        Rp <?php echo number_format($stats['total_harga'], 2, ',', '.'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Tambah Barang -->
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addBarangModal" style="margin-bottom: 20px;">Tambah Barang</button>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['ID_barang']; ?></td>
                        <td><?php echo $row['nama_barang']; ?></td>
                        <td><?php echo $row['harga_barang']; ?></td>
                        <td><?php echo $row['total_barang']; ?></td>
                        <td>
                              <!-- Tombol untuk membuka modal edit -->
                              <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editBarangModal" 
                                    data-id="<?php echo $row['ID_barang']; ?>"
                                    data-nama="<?php echo $row['nama_barang']; ?>"
                                    data-harga="<?php echo $row['harga_barang']; ?>"
                                    data-total="<?php echo $row['total_barang']; ?>">
                                 <i class="ri-edit-line"></i>
                              </button>
                              <a href="hapus_barang.php?id=<?php echo $row['ID_barang']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                 <i class="ri-delete-bin-line"></i>
                              </a>
                        </td>
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

            <!-- Modal Tambah Barang -->
      <div class="modal fade" id="addBarangModal" tabindex="-1" aria-labelledby="addBarangModalLabel" aria-hidden="true">
         <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="addBarangModalLabel">Tambah Barang</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <!-- Form untuk menambah barang -->
                     <!-- Form untuk menambah barang -->
                     <form action="barang.php" method="POST">
                        <input type="hidden" name="tambah_barang" value="1">
                        <div class="mb-3">
                           <label for="ID_barang" class="form-label">ID Barang</label>
                           <input type="text" class="form-control" id="ID_barang" name="ID_barang" required>
                        </div>
                        <div class="mb-3">
                           <label for="nama_barang" class="form-label">Nama Barang</label>
                           <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                           <label for="harga_barang" class="form-label">Harga Barang</label>
                           <input type="number" class="form-control" id="harga_barang" name="harga_barang" step="0.01" required>
                        </div>
                        <div class="mb-3">
                           <label for="total_barang" class="form-label">Total Barang</label>
                           <input type="number" class="form-control" id="total_barang" name="total_barang" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Barang</button>
                     </form>

                  </div>
               </div>
         </div>
      </div>

        <!-- Modal Edit Barang -->
      <div class="modal fade" id="editBarangModal" tabindex="-1" aria-labelledby="editBarangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBarangModalLabel">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form untuk edit barang -->
                    <form action="barang.php" method="POST">
                        <input type="hidden" name="edit_barang" value="1">
                        <input type="hidden" id="edit_ID_barang" name="ID_barang">
                        <div class="mb-3">
                           <label for="edit_nama_barang" class="form-label">Nama Barang</label>
                           <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                           <label for="edit_harga_barang" class="form-label">Harga Barang</label>
                           <input type="number" class="form-control" id="edit_harga_barang" name="harga_barang" step="0.01" required>
                        </div>
                        <div class="mb-3">
                           <label for="edit_total_barang" class="form-label">Total Barang</label>
                           <input type="number" class="form-control" id="edit_total_barang" name="total_barang" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Perbarui Barang</button>
                     </form>

                </div>
            </div>
        </div>
    </div>

    <script>
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

<?php
$conn->close();
?>