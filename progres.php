<?php
// Menghubungkan ke database
require_once '../config.php';

// Ambil data dari database
$sql = "SELECT * FROM user_tracking ORDER BY created_at DESC"; // Pastikan kolom 'created_at' ada di tabel user_tracking
$result = $conn->query($sql);

// Menangani error jika query gagal
if ($result === false) {
    die('Error: ' . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Pelacakan Admin</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
    /* Gaya Umum */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Header */
header {
    background-color: #343a40;
    color: white;
    padding: 40px 10px; /* Padding besar untuk header */
    text-align: center;
    margin-bottom: 40px; /* Jarak bawah untuk memisahkan header dari tombol */
}

header h1 {
    margin: 0;
    font-size: 1.8rem;
}

/* Container untuk tombol kembali */
.container {
    display: flex;
    flex-direction: column;
    align-items: center; /* Memusatkan tombol secara horizontal */
}

/* Tombol Kembali */
.back-button {
    display: inline-block;
    padding: 12px 24px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 20px; /* Menambah jarak dari judul */
    font-size: 16px;
}

.back-button:hover {
    background-color: #0056b3;
}

/* Formulir Pencarian */
.search-form {
    margin: 20px auto;
    text-align: center;
}

.search-form input[type="text"] {
    padding: 10px;
    width: 250px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.search-form button {
    padding: 10px 15px;
    background-color: #28a745;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    color: white;
}

.search-form button:hover {
    background-color: #218838;
}

/* Tabel Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-radius: 8px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #ffffff;
    color: black;
    font-size: 16px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

td {
    color: black;
    font-size: 14px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Tombol Aksi dalam Tabel */
td a {
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 5px;
    background-color: #28a745;
    color: white;
}

td a:hover {
    background-color: #218838;
}

td a:nth-child(2) {
    background-color: #dc3545;
}

td a:nth-child(2):hover {
    background-color: #c82333;
}

/* Desain Responsif */
@media screen and (max-width: 768px) {
    header h1 {
        font-size: 1.4rem;
    }

    table, th, td {
        font-size: 0.9rem;
    }

    .back-button {
        font-size: 14px;
        padding: 10px 15px;
    }

    .search-form input[type="text"] {
        width: 200px;
    }

    .search-form button {
        padding: 8px 12px;
    }
}
</style>
</head>

<body>

    <!-- Header Section -->
    <header>
        <h1>Data Pelacakan</h1>
        <!-- Button Back -->
        <a href="javascript:history.back()" class="back-button">Kembali</a>
    </header>

    <!-- Form pencarian -->
    

    <!-- Tabel untuk menampilkan data tracking -->
    <table id="trackingTable">
        <thead>
            <tr>
                <th>Username</th>
                <th>Test ID</th>
                <th>Mapel</th>
                <th>Step</th>
                <th>Waktu Dihabiskan (detik)</th>
                <th>Waktu Perekaman</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Menampilkan data dari query
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['test_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['mapel']) . "</td>";
                echo "<td>" . htmlspecialchars($row['step']) . "</td>";
                echo "<td>" . htmlspecialchars($row['time_spent']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>"; // Kolom waktu
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            var table = $('#trackingTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [[5, 'desc']], // Mengurutkan berdasarkan kolom waktu (created_at)
                "columnDefs": [
                    {
                        "targets": [0, 1, 2], // Kolom yang bisa dicari
                        "searchable": true
                    },
                    {
                        "targets": [3, 4, 5], // Kolom yang tidak bisa dicari
                        "searchable": false
                    }
                ]
            });

            // Fungsi untuk pencarian berdasarkan username
            $('#usernameSearch').on('keyup', function () {
                table.column(0).search(this.value).draw(); // Cari di kolom Username (kolom 0)
            });
        });
    </script>

</body>

</html>
