<?php
require_once '../config.php'; // Koneksi database

// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $mapel = trim($_POST['mapel']);
    $test_name = trim($_POST['test_name']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);

    // Validasi input
    if (empty($mapel) || empty($start_time) || empty($end_time)) {
        $error = "All fields are required.";
    } else {
        // Query untuk menambahkan jadwal baru ke database
        $query = "INSERT INTO schedules (mapel, test_name , start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }

        $stmt->bind_param('ssss', $mapel, $test_name, $start_time, $end_time);  // Corrected bind_param (added for 'test_name')
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Jika berhasil, arahkan ke halaman view_schedule.php dengan pesan sukses
            header("Location: view_schedule.php?message=Schedule successfully created.");
            exit();
        } else {
            // Jika gagal, tampilkan pesan error
            $error = "Failed to create schedule.";
        }

        // Menutup statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Schedule</title>
    <link rel="icon" href="../soal/logo.png" type="image/x-icon">
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
            padding: 50px;
            text-align: center;
            position: relative;
        }

        header h1 {
            margin: 0;
            font-size: 1.9rem;
        }

        /* Container for Back Button */
        .back-btn-container {
            position: absolute;
            top: 5px;
            left: 5px;
        }

        .back-btn {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 1rem;
            color: #333;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="datetime-local"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        input[type="text"]:focus,
        input[type="datetime-local"]:focus {
            outline-color: #007bff;
            border-color: #007bff;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #218838;
        }

        .error-message {
            font-size: 1rem;
            color: red;
            text-align: center;
        }

        main {
            margin: 40px auto;
            width: 80%;
            max-width: 900px;
        }

        main h2 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

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

        @media screen and (max-width: 768px) {
            header h1 {
                font-size: 1.4rem;
            }

            table, th, td {
                font-size: 0.9rem;
            }

            .back-btn {
                font-size: 14px;
                padding: 8px 15px;
            }

            .form-group input[type="text"],
            .form-group input[type="datetime-local"] {
                width: 100%;
            }

            .submit-btn {
                font-size: 14px;
                padding: 8px 12px;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Create Schedule</h1>
        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </header>

    <main>
        <h2>Enter Schedule Details</h2>

        <?php if (isset($error)) : ?>
            <p class="error-message"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="mapel">Mata Pelajaran</label>
                <input type="text" name="mapel" id="mapel" placeholder="Enter Subject" required>
            </div>

            <div class="form-group">
                <label for="test_name">Jenis Test</label>
                <input type="text" name="test_name" id="test_name" placeholder="Enter Test Name" required>
            </div>

            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="datetime-local" name="start_time" id="start_time" required>
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="datetime-local" name="end_time" id="end_time" required>
            </div>

            <button type="submit" class="submit-btn">Create Schedule</button>
        </form>
    </main>
</body>

</html>
