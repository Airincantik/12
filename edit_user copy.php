<?php
require_once '../config.php';

// Ambil username siswa yang ingin diedit
$username_to_edit = $_GET['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username_to_edit);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

// Proses pengeditan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $age = $_POST['age'];
    $birth_date = $_POST['birthdate'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $update_query = "UPDATE users SET full_name = ?, age = ?, birthdate = ?, address = ?, email = ?, phone_number = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('sisssss', $full_name, $age, $birth_date, $address, $email, $phone_number, $username_to_edit);

    if ($update_stmt->execute()) {
        echo "Profil berhasil diperbarui.";
    } else {
        echo "Terjadi kesalahan: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <img src="../soal/logo.png" align="left" alt="logo " width="100" height="100">
    <title>Edit Profil Siswa</title>
</head>

<body>
    <h2>Edit Profil Siswa: <?= htmlspecialchars($siswa['full_name']) ?></h2>
    <form method="POST">
        <label for="full_name">Nama Lengkap:</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($siswa['full_name']) ?>" required><br><br>

        <label for="age">Usia:</label>
        <input type="number" name="age" value="<?= htmlspecialchars($siswa['age']) ?>" required><br><br>

        <label for="birthdate">Tanggal Lahir:</label>
        <input type="date" name="birthdate" value="<?= htmlspecialchars($siswa['birthdate']) ?>" required><br><br>

        <label for="address">Alamat:</label>
        <textarea name="address" required><?= htmlspecialchars($siswa['address']) ?></textarea><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($siswa['email']) ?>" required><br><br>

        <label for="phone_number">Nomor HP:</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($siswa['phone_number']) ?>" required><br><br>

        <button type="submit">Perbarui Profil</button>
    </form>
</body>

</html>