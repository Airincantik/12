<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $test_name = $_POST['test_name'];
    $mapel = $_POST['mapel'];
    $duration = $_POST['duration'];

    // Prevent SQL injection by using prepared statements
    $stmt = $conn->prepare("INSERT INTO tests (test_name, mapel, duration) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $test_name, $mapel, $duration);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to create_questions.php after successful insertion
        header("Location: create_questions.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Test</title>
    <style>
        /* Resetting some basic styles */
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

        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        /* Tombol Kembali */
        .back-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        /* Form Pencarian */
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

        /* Form Styling */
        main {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 100px); /* Adjust height to take full viewport minus header */
        }

        form {
            background-color: white;
            padding: 20px;
            width: 40%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            background-color: #f9f9f9;
        }

        form input[type="text"]:focus,
        form input[type="number"]:focus {
            border-color: #28a745;
            outline: none;
        }

        form button {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #218838;
        }

        /* Desain Responsif */
        @media screen and (max-width: 768px) {
            header h1 {
                font-size: 1.4rem;
            }

            form {
                width: 80%;
            }

            table, th, td {
                font-size: 0.9rem;
            }

            .back-button {
                font-size: 14px;
                padding: 8px 15px;
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
    <header>
        <h1>Buat Test</h1>
        <a href="index.php" class="back-button">Kembali</a>
    </header>

    <main>
        <form action="create_test.php" method="POST">
            <input type="text" name="test_name" placeholder="Nama Test" required>
            <input type="text" name="mapel" placeholder="Mata Pelajaran" required>
            <input type="number" name="duration" placeholder="Durasi (menit)" required>
            <button type="submit">Buat Test</button>
        </form>
    </main>

    <!-- Back Button -->
    
</body>

</html>
