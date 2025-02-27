<?php
require_once '../config.php';

// Cek apakah ID tersedia di URL
if (!isset($_GET['id'])) {
    die("ID tidak valid.");
}

// Ambil data berdasarkan ID
$id = $_GET['id'];
$query = "SELECT * FROM access_codes WHERE id = ?";  // Ganti code_id menjadi id
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Kode Akses tidak ditemukan.");
}

$row = $result->fetch_assoc();

// Tangkap data yang sudah ada di form saat POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mapel = $_POST['mapel'];
    $access_code = $_POST['access_code'];
    $guide = $_POST['guide']; // Tambahkan panduan

    // Update query
    $update_query = "UPDATE access_codes SET mapel = ?, access_code = ?, guide = ? WHERE id = ?";  // Ganti code_id menjadi id
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $mapel, $access_code, $guide, $id); // Bind parameter dengan panduan

    if ($stmt->execute()) {
        header("Location: view_access_codes.php"); // Redirect setelah update
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
    <title>Edit Kode Akses</title>
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
        form textarea {
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
        <h1>Edit Kode Akses</h1>
        <a href="index.php">Kembali ke Dashboard</a>
    </header>

    <main>
        <form method="POST">
            <label for="mapel">Mata Pelajaran:</label>
            <input type="text" id="mapel" name="mapel" value="<?= htmlspecialchars($row['mapel']) ?>" required><br><br>

            <label for="access_code">Kode Akses:</label>
            <input type="text" id="access_code" name="access_code" value="<?= htmlspecialchars($row['access_code']) ?>" required><br><br>

            <label for="guide">Panduan:</label>
            <textarea id="guide" name="guide" required><?= htmlspecialchars($row['guide']) ?></textarea><br><br>

            <button type="submit">Perbarui</button>
        </form>
    </main>
</body>

</html>
