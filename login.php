<?php
require_once '../config.php'; // Koneksi ke database

// Pastikan koneksi berhasil
if ($conn === false) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Cek apakah form login dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mencari admin berdasarkan username
    $admin_query = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($admin_query);

    // Periksa apakah query berhasil dipersiapkan
    if ($stmt === false) {
        die("Terjadi kesalahan dalam menyiapkan query: " . mysqli_error($conn));
    }

    // Bind parameter dan eksekusi query
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin_result = $stmt->get_result();

    // Jika admin ditemukan
    if ($admin_result->num_rows === 1) {
        $admin = $admin_result->fetch_assoc();

        // Cocokkan password (tanpa hash)
        if ($password === $admin['password']) {
            // Login berhasil
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_last_activity'] = time(); // Untuk session timeout

            // Mendapatkan alamat IP publik dengan menggunakan API eksternal
            $ip_address = file_get_contents('https://ipecho.net/plain'); // Atau bisa menggunakan checkip.amazonaws.com
            $device_info = $_SERVER['HTTP_USER_AGENT']; // Mendapatkan informasi perangkat (browser, OS, dsb)

            // Simpan log login ke database
            $log_query = "INSERT INTO admin_activity_log (admin_id, login_time, ip_address, device_info, login_method) 
                         VALUES (?, NOW(), ?, ?, 'manual')";
            $log_stmt = $conn->prepare($log_query);

            if ($log_stmt === false) {
                die("Terjadi kesalahan dalam menyiapkan query log: " . mysqli_error($conn));
            }

            // Bind parameter dan eksekusi query log
            $log_stmt->bind_param('iss', $admin['id'], $ip_address, $device_info);
            $log_stmt->execute();

            // Redirect ke halaman admin (misalnya dashboard admin)
            header('Location: index.php');
            exit();
        } else {
            $error_message = "Password salah untuk admin.";
        }
    } else {
        $error_message = "Admin tidak ditemukan.";
    }
}
?>

<!-- Form Login -->
<div class="login-container">
    <form method="POST" class="login-form">
        <h2>Login Admin</h2>

        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($error_message)): ?>
            <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

<!-- CSS Styling -->
<style>
    /* Global Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    /* Form container */
    .login-container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        box-sizing: border-box;
    }

    .login-container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    /* Input fields */
    .login-form input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    /* Submit Button */
    .login-form button {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    .login-form button:hover {
        background-color: #0056b3;
    }

    /* Error messages - Alert Box */
    .alert {
        color: white;
        background-color: #f44336;  /* Red background */
        padding: 15px;
        margin: 10px 0;
        border-radius: 5px; /* Rounded corners */
        font-size: 16px;
        text-align: center;
        animation: fadeIn 1s, fadeOut 1s 4s;  /* Animasi masuk dan keluar */
        width: 100%;
        box-sizing: border-box;
    }

    /* Fade-in Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Fade-out Animation */
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .login-container {
            width: 90%;
        }

        .login-container h2 {
            font-size: 1.4rem;
        }

        .login-form input, .login-form button {
            font-size: 0.9rem;
        }

        .alert {
            font-size: 14px;
        }
    }
</style>

<!-- JavaScript for auto-hide alert after animation -->
<script>
    // Periksa apakah ada alert di halaman
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.display = 'none';  // Sembunyikan alert setelah animasi selesai
        }, 5000);  // Sembunyikan setelah 5 detik
    }
</script>
