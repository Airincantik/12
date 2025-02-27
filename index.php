<?php
require_once '../config.php';

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Query to count the number of active participants (those active within the last hour)
$query_active_participants = "SELECT COUNT(DISTINCT username) AS active_count FROM participan_device WHERE access_time > NOW() - INTERVAL 1 HOUR";
$result_active = $conn->query($query_active_participants);

// Check for query errors
if (!$result_active) {
    die("Query failed: " . $conn->error);
}

$active_count = 0;
if ($row = $result_active->fetch_assoc()) {
    $active_count = $row['active_count'];
}

// Fetch device info of participants from the participants_device table
$query = "SELECT * FROM participan_device ORDER BY timestamp DESC"; // Sort by latest login time
$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* General Body Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header Styling */
        header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
        }

        header img {
            margin-bottom: 10px;
            width: 100px;
            height: 100px;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        /* Tombol Logout dengan warna biru */
        .logout-button {
            padding: 10px 20px;
            background-color: #007bff; /* Warna biru */
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .logout-button:hover {
            background-color: #0056b3; /* Warna biru lebih gelap saat hover */
        }

        /* Main Content Layout */
        main {
            display: flex;
            flex-grow: 1;
            padding: 20px;
            justify-content: space-between;
            gap: 20px;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 250px;
            background-color: #f4f4f4;
            padding: 20px;
            box-sizing: border-box;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #sidebar h2 {
            font-size: 1.2rem;
            color: #343a40;
            margin-bottom: 20px;
        }

        #sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        #sidebar ul li {
            margin: 10px 0;
        }

        #sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        /* Sidebar link hover effect */
        #sidebar ul li a:hover {
            background-color: #007bff;
            color: white;
            transform: scale(1.05);
            padding-left: 15px;
        }

        #sidebar ul li a:hover img {
            filter: none;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            color: #007bff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Active Participant Icon */
        .active-participant-icon {
            width: 40px;
            height: 40px;
            vertical-align: middle;
            margin-right: 10px;
        }

        /* Search Form Styling */
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
            padding: 10px 10px;
            background-color: #28a745;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }

        .search-form button:hover {
            background-color: #218838;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            header h1 {
                font-size: 1.4rem;
            }

            table,
            th,
            td {
                font-size: 0.9rem;
            }

            main {
                flex-direction: column;
            }

            #sidebar {
                width: 50%;
                margin-bottom: 20px;
            }

            #sidebar ul li a {
                font-size: 1rem;
                padding: 10px;
            }

            .search-form input[type="text"] {
                width: 100%;
            }

            table {
                margin-top: 10px;
            }
        }
        /* Ukuran gambar di sidebar */
        #sidebar ul li a img {
            width: 20px;  /* Menyesuaikan lebar gambar */
            height: 20px; /* Menyesuaikan tinggi gambar */
            margin-right: 10px;  /* Memberikan jarak antara gambar dan teks */
            object-fit: contain;  /* Memastikan gambar tetap proporsional */
        }
        /* Ukuran gambar dan teks di sidebar */
        #sidebar ul li a {
            font-size: 0.9rem;  /* Menyesuaikan ukuran font agar lebih kecil */
            padding: 8px 10px;  /* Mengurangi padding agar lebih seimbang */
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <header>
        <div>
            <img src="../soal/logo.png" alt="logo" width="100" height="100">
        </div>
        <h1>Admin Dashboard</h1>
        <a href="?logout=true" class="logout-button">Logout</a>
    </header>

    <main>
        <!-- Sidebar Section -->
        <section id="sidebar">
            <h2>Manage Content</h2>
            <ul>
                <li><a href="view_tests.php"><img src="ujicoba.png" alt="Test"> Lihat Test</a></li>
                <li><a href="view_questions.php"><img src="ssl.png" alt="Questions"> Lihat Soal</a></li>
                <li><a href="view_schedule.php"><img src="jwl.png" alt="Schedule"> Lihat Jadwal</a></li>
                <li><a href="view_access_codes.php"><img src="angka.jpg" alt="Access Code"> Lihat Kode Akses</a></li>
                <li><a href="admin.php"><img src="ss.png" alt="Admin"> Lihat Gambar Peserta</a></li>
                <li><a href="manage_guidelines.php"><img src="pan.png" alt="Guidelines"> Kelola Panduan Mata Pelajaran</a></li>
                <li><a href="create_access_code.php"><img src="dit.png" alt="Reset"> Buat kode akses</a></li>
                <li><a href="create_questions.php"><img src="lth.jpg" alt="Create"> Buat Soal</a></li>
                <li><a href="create_schedule.php"><img src="todo.png" alt="Create Schedule"> Buat Jadwal</a></li>
                <li><a href="create_test.php"><img src="kuis.jpg" alt="Create Test"> Buat Test</a></li>
                <li><a href="admin_add_trial_question.php"><img src="trial.jpg" alt="Trial"> Buat Soal Uji Coba</a></li>
                <li><a href="add_admin.php"><img src="add.png" alt="Add Admin"> Tambah User Admin</a></li>
                <li><a href="progres.php"><img src="progres.png" alt="Progress"> Tracking</a></li>
                <li><a href="admin_activity_log.php"><img src="log.jpg" alt="Admin Log"> Admin Log</a></li>
                <li><a href="user_admin_edit.php"><img src="dit.png" alt="Reset"> Edit User & Admin</a></li>
                <li><a href="addpkr.php"><img src="dit.png" alt="Reset">proktor</a></li>
            </ul>
        </section>

        <!-- Main Content Section -->
        <section>
            <h2>
                <a href="active_participants.php">
                    <img src="../soal/ikon.jpg" alt="Ikon Orang" class="active-participant-icon">
                </a>
                Peserta Aktif: <?= $active_count; ?>
            </h2>
        </section>

        <!-- Device Info Section -->
        <section>
            <h2>Device Info</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Browser</th>
                        <th>OS</th>
                        <th>IP Address</th>
                        <th>Waktu Akses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['device_name']) ?></td>
                            <td><?= htmlspecialchars($row['device_os']) ?></td>
                            <td><?= htmlspecialchars($row['public_ip']) ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
