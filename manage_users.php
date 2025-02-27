<?php
require_once '../config.php';


// Query untuk mengambil semua user (admin dan siswa)
$query = "SELECT * FROM users"; // Menampilkan semua siswa
$result = $conn->query($query);

// Cek jika ada error pada query
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <header>
        <img src="../soal/logo.png" align="left" alt="logo " width="100" height="100">
        <title>Manage Users</title>
    </header>

<body>
    <h1>Manage Users</h1>
    <a href="index.php">kembali ke beranda</a>

    <h2>Daftar Siswa</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Nomor HP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td>
                        <!-- Link untuk mengedit user -->
                        <a href="edit_user.php?username=<?= htmlspecialchars($row['username']) ?>">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        </head>
    </table>
</body>

</html>