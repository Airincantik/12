<?php

require_once '../config.php';

// Proses penambahan proktor baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM proktors WHERE username = '$username'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error_message = "Username sudah digunakan, coba lagi dengan username yang berbeda.";
    } else {
        // Query untuk menyimpan data proktor baru
        $query = "INSERT INTO proktors (username, password) VALUES ('$username', '$password')";
        if ($conn->query($query)) {
            $success_message = "Proktor baru berhasil ditambahkan.";
        } else {
            $error_message = "Terjadi kesalahan saat menambahkan proktor.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Proktor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            width: 100%;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding-top: 80px;
            padding-bottom: 40px;
            text-align: center;
        }

        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #4caf50;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.3s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border: 1px solid #4caf50;
        }

        button {
            background-color: #4caf50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Alert notification center */
        .notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #f44336;
            color: white;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            width: 80%;
            max-width: 400px;
            display: none;
        }

        .notification.success {
            background-color: #4caf50;
        }

        .notification.error {
            background-color: #f44336;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0288d1;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #0277bd;
        }

    </style>
</head>

<body>
    <header>
        <h1>Tambah Proktor Baru</h1>
        <a href="index.php" class="btn-back">Kembali ke Dashboard</a>
    </header>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="notification success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Tambah Proktor</button>
        </form>

        <br>
       
    </div>

    <!-- Notification Alert for Error -->
    <?php if (isset($error_message)): ?>
        <script>
            const errorAlert = document.createElement('div');
            errorAlert.classList.add('notification', 'error');
            errorAlert.innerText = '<?php echo $error_message; ?>';
            document.body.appendChild(errorAlert);
            errorAlert.style.display = 'block';

            // Hide the error message after 5 seconds
            setTimeout(function () {
                errorAlert.style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

</body>

</html>
