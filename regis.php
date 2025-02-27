<?php

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    $full_name = $_POST['full_name'];
    $age = $_POST['age'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Enkripsi password

    // Simpan user ke database
    $sql = "INSERT INTO users (full_name, age, birthdate, gender, address, phone_number, email, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sisssssss', $full_name, $age, $birthdate, $gender, $address, $phone_number, $email, $username, $password);

    if ($stmt->execute()) {
        // Simpan username ke session dan redirect ke halaman capture
        $_SESSION['username'] = $username;
        header('Location: panduan.php');
        exit();
    } else {
        $error = "Gagal mendaftar! Username mungkin sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
</head>

<body>
    <h1>Registrasi Akun</h1>
    <form action="" method="POST">
        <label for="full_name">Nama Lengkap:</label>
        <input type="text" name="full_name" required><br>

        <label for="age">Umur:</label>
        <input type="number" name="age" required><br>

        <label for="birthdate">Tanggal Lahir:</label>
        <input type="date" name="birthdate" required><br>

        <label for="gender">Jenis Kelamin:</label>
        <select name="gender" required>
            <option value="Male">Laki-laki</option>
            <option value="Female">Perempuan</option>
        </select><br>

        <label for="address">Alamat:</label>
        <textarea name="address" required></textarea><br>

        <label for="phone_number">Nomor HP:</label>
        <input type="text" name="phone_number" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Daftar</button>
    </form>

    <?php if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    } ?>

    <!-- Tombol untuk mengarahkan ke halaman login -->
    <p>Sudah punya akun? <a href="login.php"><button type="button">Login</button></a></p>

    <style>
        body {
            background-color: #f4f7fc;
        }

        h1 {
            text-align: center;
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

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
        }

        a button {
            background-color: #28a745;
        }

        a button:hover {
            background-color: #218838;
        }
    </style>
    <script>// Fungsi untuk memperbarui progress saat berpindah halaman
        function updateProgress(currentPage, timeSpent) {
            const userId = <?php echo $user_id; ?>;  // Menggunakan session atau variabel yang sudah ada
            const testId = <?php echo $test_id; ?>;  // Menggunakan session atau variabel yang sudah ada

            // Mengirim permintaan ke server untuk memperbarui progress
            fetch(`update_progress.php?user_id=${userId}&test_id=${testId}&current_page=${currentPage}&time_spent=${timeSpent}`)
                .then(response => response.text())  // Ambil respons dari server
                .then(data => {
                    console.log(data);  // Tampilkan hasil jika berhasil update
                })
                .catch(error => {
                    console.error('Error updating progress:', error);  // Tampilkan error jika gagal
                });
        }

        // Contoh pemanggilan fungsi saat berpindah ke soal esai
        updateProgress('essay_section', 600);  // Misalnya, peserta berada di soal esai dan sudah menghabiskan 10 menit
    </script>
</body>

</html>