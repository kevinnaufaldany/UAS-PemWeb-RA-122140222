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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang Rusak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
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
                        <a class="nav-link active" href="#">Barang Rusak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><?php echo $_SESSION['ID_karyawan']; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/server/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (!empty($message)) echo $message; ?>
        <!-- Statistik Barang Rusak -->
        <div class="row mb-4">
            <h3 style="margin-bottom: 30px;">Barang Rusak di Toko Defy</h3>
            <div class="col-md-4">
               <div class="card text-white bg-primary">
                     <div class="card-body">
                        <h5 class="card-title">Jumlah Jenis Barang Rusak</h5>
                        <p class="card-text">
                           <?php echo $stats_rusak['jumlah_jenis']; ?> Jenis
                        </p>
                     </div>
               </div>
            </div>
            <div class="col-md-4">
               <div class="card text-white bg-success">
                     <div class="card-body">
                        <h5 class="card-title">Jumlah Barang Rusak</h5>
                        <p class="card-text">
                           <?php echo $stats_rusak['total_barang_rusak']; ?> Unit
                        </p>
                     </div>
               </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Total Harga Kerugian Barang Rusak</h5>
                        <p class="card-text">
                            Rp <?php echo number_format($total_kerugian, 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>
         </div>

        <!-- Tombol Tambah Barang Rusak -->
        <button class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#addRusakModal" style="margin-bottom: 20px;">Tambah Barang Rusak</button>

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
                        <th>Aksi</th>
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
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?php echo $row['ID_barang_rusak']; ?>"
                                    data-jumlah="<?php echo $row['total_barang_rusak']; ?>"
                                    data-keterangan="<?php echo $row['keterangan']; ?>">
                                <i class="ri-edit-line"></i>
                                </button>
                                <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');" style="display:inline;">
                                    <input type="hidden" name="id_barang_rusak" value="<?php echo $row['ID_barang_rusak']; ?>">
                                    <button type="submit" name="delete_barang_rusak" class="btn btn-danger btn-sm">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
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

        <!-- Modal Tambah Barang Rusak -->
        <div class="modal fade" id="addRusakModal" tabindex="-1" aria-labelledby="addRusakModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRusakModalLabel">Tambah Barang Rusak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="barang_rusak.php" method="POST">
                            <input type="hidden" name="tambah_barang_rusak" value="1">
                            <div class="mb-3">
                                <label for="ID_barang" class="form-label">ID Barang</label>
                                <input type="text" class="form-control" id="ID_barang" name="ID_barang" required>
                            </div>
                            <div class="mb-3">
                                 <label for="total_barang_rusak" class="form-label">Jumlah Barang Rusak</label>
                                 <input type="number" class="form-control" id="total_barang_rusak" name="total_barang_rusak" required>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">Tambah Barang Rusak</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
         <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <form method="POST" action="">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h5 class="modal-title" id="editModalLabel">Edit Barang Rusak</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                           <input type="hidden" name="id_barang_rusak" id="edit_id_barang_rusak">
                           <div class="mb-3">
                                 <label for="edit_total_barang_rusak" class="form-label">Jumlah Barang Rusak</label>
                                 <input type="number" class="form-control" name="total_barang_rusak" id="edit_total_barang_rusak" required>
                           </div>
                           <div class="mb-3">
                                 <label for="edit_keterangan" class="form-label">Keterangan</label>
                                 <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
                           </div>
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                           <button type="submit" name="edit_barang_rusak" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                     </div>
               </form>
            </div>
         </div>
    </div>

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

    </script>

</body>
</html>

<?php
$conn->close();
?>