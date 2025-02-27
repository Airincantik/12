<?php
require_once '../config.php';

// Cek apakah schedule_id tersedia di URL
if (!isset($_GET['schedule_id'])) {
    die("ID tidak valid.");
}

// Ambil data berdasarkan schedule_id
$schedule_id = $_GET['schedule_id'];
$query = "SELECT * FROM schedules WHERE schedule_id = ?";  // Ganti 'access_codes' menjadi 'schedules' dan 'id' menjadi 'schedule_id'
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Jadwal tidak ditemukan.");
}

$row = $result->fetch_assoc();

// Tangkap data yang sudah ada di form saat POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mapel = $_POST['mapel'];
    $test_name = $_POST['test_name'];  // Ganti 'access_code' dengan 'test_name'
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Update query
    $update_query = "UPDATE schedules SET mapel = ?, test_name = ?, start_time = ?, end_time = ? WHERE schedule_id = ?";  // Ganti 'access_codes' menjadi 'schedules' dan 'id' menjadi 'schedule_id'
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $mapel, $test_name, $start_time, $end_time, $schedule_id); // Bind parameter

    if ($stmt->execute()) {
        header("Location: view_schedules.php"); // Redirect setelah update
    } else {
        echo "Gagal mengupdate data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal</title>
    <link rel="stylesheet" href="../cs/1.css">
    <style>
        /* CSS Umum */
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

        h1 {
            margin: 0;
        }

        /* Tautan kembali */
        a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #007bff;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
        }

        a:hover {
            background-color: #0056b3;
        }

        /* Form Styling */
        main {
            width: 50%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        form label {
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input,
        form textarea {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form input[type="text"],
        form textarea,
        form input[type="datetime-local"] {
            width: 100%;
        }

        form button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        /* Responsiveness */
        @media screen and (max-width: 768px) {
            main {
                width: 80%;
            }

            header h1 {
                font-size: 1.5rem;
            }

            form button {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <img src="../soal/logo.png" align="left" alt="logo" width="100" height="100">
        <h1>Edit Jadwal</h1>
        <a href="index.php">Kembali ke Dashboard</a>
    </header>

    <main>
        <form method="POST">
            <label for="mapel">Mata Pelajaran:</label>
            <input type="text" id="mapel" name="mapel" value="<?= htmlspecialchars($row['mapel']) ?>" required><br><br>

            <label for="test_name">Nama Ujian:</label>
            <input type="text" id="test_name" name="test_name" value="<?= htmlspecialchars($row['test_name']) ?>" required><br><br>

            <label for="start_time">Waktu Mulai:</label>
            <input type="datetime-local" id="start_time" name="start_time" value="<?= htmlspecialchars($row['start_time']) ?>" required><br><br>

            <label for="end_time">Waktu Selesai:</label>
            <input type="datetime-local" id="end_time" name="end_time" value="<?= htmlspecialchars($row['end_time']) ?>" required><br><br>

            <button type="submit">Perbarui</button>
        </form>
    </main>
</body>

</html>
