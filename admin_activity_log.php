<?php

require_once '../config.php'; // Koneksi ke database

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Jika bukan admin, redirect ke login
    exit();
}

// Query untuk mengambil log aktivitas login admin
$query = "SELECT a.username, l.login_time, l.ip_address, l.device_info, l.login_method 
          FROM admin_activity_log l 
          JOIN admins a ON l.admin_id = a.id  -- Adjusted column name
          ORDER BY l.login_time DESC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .back-button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <header>
        <h1>Log Aktivitas Login Admin</h1>
        <a href="index.php"><button class="back-button">Kembali ke Dashboard</button></a>
    </header>

    <div class="container">
        <!-- Tabel untuk menampilkan log login admin -->
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Waktu Login</th>
                    <th>IP Address</th>
                    <th>Device Info</th>
                    <th>Metode Login</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Loop untuk menampilkan setiap baris data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['login_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['device_info']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['login_method']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada aktivitas login yang ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Button untuk kembali ke halaman admin -->
        <br>
        <a href="index.php" class="btn">Kembali ke Dashboard</a>
    </div>

</body>

</html>
