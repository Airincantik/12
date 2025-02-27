<?php
require_once 'config.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Cek koneksi
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $access_code = trim($_POST['access_code'] ?? '');

    if (empty($access_code)) {
        die("Access code is required.");
    }

    // Query untuk validasi kode akses dan ambil mapel dan test_name
    $query = "SELECT mapel, test_name FROM access_codes WHERE access_code = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param('s', $access_code);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $access_row = $result->fetch_assoc();
        $mapel = $access_row['mapel'];
        $test_name = $access_row['test_name'];

        // Ambil waktu mulai dari tabel schedule berdasarkan mapel dan test_name
        $schedule_query = "SELECT start_time FROM schedules WHERE mapel = ? AND test_name = ?";
        $schedule_stmt = $conn->prepare($schedule_query);

        if (!$schedule_stmt) {
            die("Query preparation failed: " . $conn->error);
        }

        $schedule_stmt->bind_param('ss', $mapel, $test_name);
        $schedule_stmt->execute();
        $schedule_result = $schedule_stmt->get_result();

        if ($schedule_result->num_rows === 1) {
            $schedule_row = $schedule_result->fetch_assoc();

            // Start session
            session_start();

            // Simpan mapel, test_name dan start_time ke dalam session
            $_SESSION['mapel'] = $mapel;
            $_SESSION['test_name'] = $test_name;
            $_SESSION['start_time'] = strtotime($schedule_row['start_time']);

            // Redirect ke halaman tunggu.php
            header("Location: tunggu.php");
            exit;
        } else {
            // Jika tidak ada jadwal, tampilkan pesan error
            echo '<div id="alert-box" class="alert">
                    <span class="close-btn" onclick="closeAlert()">&times;</span>
                    <strong>Error!</strong> Schedule not found for the selected subject and test.
                  </div>';
        }

    } else {
        // Jika kode akses tidak valid
        echo '<div id="alert-box" class="alert">
                <span class="close-btn" onclick="closeAlert()">&times;</span>
                <strong>Error!</strong> Invalid access code.
              </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7fc;
            text-align: center;
        }

        h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }

        form input,
        form button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
        }

        form input {
            border: 1px solid #ccc;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Styling for the alert box */
        .alert {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .alert .close-btn {
            font-size: 1.5em;
            cursor: pointer;
            color: white;
        }

        /* Animasi untuk menghilangkan alert */
        .alert.hide {
            animation: fadeOut 2s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                display: none;
            }
        }
    </style>
</head>

<body>

    <h2>Enter Access Code</h2>
    <form method="POST">
        <input type="text" name="access_code" placeholder="Enter Access Code" required>
        <button type="submit">Submit</button>
    </form>

    <script>
        // Function to automatically close the alert box after 5 seconds
        setTimeout(function () {
            var alertBox = document.getElementById('alert-box');
            if (alertBox) {
                alertBox.classList.add('hide');
            }
        }, 5000);

        // Function to close the alert manually
        function closeAlert() {
            var alertBox = document.getElementById('alert-box');
            alertBox.classList.add('hide');
        }
    </script>

</body>

</html>
