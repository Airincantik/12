<?php
require_once '../config.php'; // Include your database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    $mapel = trim($_POST['mapel']);
    $access_code = trim($_POST['access_code']);
    $guide = trim($_POST['guide']); // Panduan yang dimasukkan admin
    $test_name = trim($_POST['test_name']); // Nama Ujian yang dimasukkan admin

    // Input validation
    if (empty($mapel) || empty($access_code) || empty($guide) || empty($test_name)) {
        echo "<script>alert('Semua kolom harus diisi.'); window.location.href = 'create_access_code.php';</script>";
        exit();
    }

    // Check if the access code already exists
    $query = "SELECT * FROM access_codes WHERE access_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $access_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Kode akses sudah ada. Buat kode akses lain.'); window.location.href = 'create_access_code.php';</script>";
    } else {
        // Insert the new access code and guide into the database
        $query = "INSERT INTO access_codes (mapel, access_code, guide, test_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $mapel, $access_code, $guide, $test_name);

        if ($stmt->execute()) {
            echo "<script>
                alert('Kode akses berhasil dibuat!');
                window.location.href = 'create_test.php'; // Redirect to Create Test
            </script>";
        } else {
            echo "<script>alert('Gagal membuat kode akses.'); window.location.href = 'create_access_code.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kode Akses</title>

    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* General body styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Header styling */
        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-direction: column; /* Change flex direction to column */
            text-align: center; /* Center the content */
        }

        header img {
            margin-bottom: 10px; /* Add space below the logo */
        }

        header h1 {
            font-size: 24px;
        }

        /* Button styling for Kembali ke Dashboard link */
        header a {
            background-color: #007bff; /* Blue color */
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            margin-top: 15px; /* Add space between the h1 and the button */
        }

        header a:hover {
            background-color: #0056b3; /* Darker blue when hovered */
        }

        /* Main content styling */
        main {
            padding: 40px;
            display: flex;
            justify-content: center;
        }

        /* Form container styling */
        #create-access-code {
            background-color: white;
            padding: 30px;
            width: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        #create-access-code h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
        }

        form label {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
        }

        form input[type="text"],
        form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        form textarea {
            resize: vertical;
            height: 120px;
        }

        /* Button styling */
        form button {
            background-color: #007bff; /* Blue color */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        form button:hover {
            background-color: #0056b3; /* Darker blue when hovered */
        }

        /* Responsive design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            header h1 {
                margin-top: 10px;
            }

            #create-access-code {
                width: 90%;
            }
        }
    </style>
</head>

<body>

    <header>
        <img src="../soal/logo.png" alt="logo" width="100" height="100">
        <h1>Buat Kode Akses</h1>
        <a href="index.php">Kembali ke Dashboard</a> <!-- Move the button below the heading -->
    </header>

    <main>
        <section id="create-access-code">
            <h2>Form Buat Kode Akses</h2>
            <form method="POST" action="create_access_code.php">
                <label for="mapel">Mata Pelajaran:</label>
                <input type="text" name="mapel" id="mapel" required placeholder="Masukkan nama mata pelajaran">

                <label for="access_code">Kode Akses:</label>
                <input type="text" name="access_code" id="access_code" required placeholder="Masukkan kode akses">

                <label for="test_name">Nama Ujian:</label>
                <input type="text" name="test_name" id="test_name" required placeholder="Masukkan nama ujian">

                <label for="guide">Panduan:</label>
                <textarea name="guide" id="guide" required placeholder="Masukkan panduan untuk mata pelajaran ini"></textarea>

                <button type="submit">Buat Kode Akses</button>
            </form>
        </section>
    </main>

</body>

</html>
