<?php
require_once '../config.php';

// Initialize $search_term as an empty string
$search_term = "";

// Menangani form untuk menambah panduan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_guideline'])) {
        $subject_name = $_POST['subject_name'];
        $guidelines = $_POST['guidelines'];

        // Insert into database
        $query = "INSERT INTO subject_guidelines (subject_name, guidelines) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $subject_name, $guidelines);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['edit_guideline'])) {
        $guideline_id = $_POST['guideline_id'];
        $guidelines = $_POST['guidelines'];

        // Update the guideline
        $query = "UPDATE subject_guidelines SET guidelines = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $guidelines, $guideline_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['search'])) {
        $search_term = $_POST['search_term'];  // Mengambil nilai pencarian dari form
    }
}

// Mengambil data panduan dari database dengan filter berdasarkan nama mata pelajaran
$query = "SELECT * FROM subject_guidelines WHERE subject_name LIKE ?";
$stmt = $conn->prepare($query);
$search_param = "%" . $search_term . "%";
$stmt->bind_param('s', $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah query berhasil
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Panduan Mata Pelajaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        /* Styling Form */
        .add-form {
            margin: 20px auto;
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
        }

        .add-form input,
        .add-form textarea {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .add-form button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-form button:hover {
            background-color: #0056b3;
        }

        /* Styling Tabel */
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
        }

        td {
            color: black;
            font-size: 14px;
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
                padding: 8px 15px;
            }

            .search-form input[type="text"] {
                width: 200px;
            }

            .search-form button {
                padding: 8px 12px;
            }

            .add-form {
                width: 80%;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Kelola Panduan Mata Pelajaran</h1>
    <a href="index.php"><button class="back-button">Back to Dashboard</button></a>
</header>

<!-- Form untuk menambah panduan -->
<div class="add-form">
    <h3>Tambah Panduan Mata Pelajaran</h3>
    <form method="POST" action="">
        <label for="subject_name">Nama Mata Pelajaran</label><br>
        <input type="text" name="subject_name" required><br><br>

        <label for="guidelines">Panduan</label><br>
        <textarea name="guidelines" rows="5" required></textarea><br><br>

        <button type="submit" name="add_guideline">Tambah Panduan</button>
    </form>
</div>

<!-- Form Pencarian -->
<div class="search-form">
    <form method="POST" action="">
        <input type="text" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by Subject Name" required>
        <button type="submit" name="search"><i class="fas fa-search"></i> Search</button>
    </form>
</div>

<!-- Tabel Panduan -->
<h2 style="text-align:center;">Daftar Panduan Mata Pelajaran</h2>
<table>
    <thead>
        <tr>
            <th>Nama Mata Pelajaran</th>
            <th>Panduan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['guidelines'])) ?></td>
                <td>
                    <a href="edit_guideline.php?id=<?= $row['id'] ?>">Edit</a> |
                    <a href="delete_guideline.php?id=<?= $row['id'] ?>"
                       onclick="return confirm('Are you sure you want to delete this guideline?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>

</html>
