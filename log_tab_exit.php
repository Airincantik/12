<?php
require_once 'config.php'; // Koneksi database

// Pastikan data POST tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data yang dikirimkan melalui POST
    $test_id = isset($_POST['test_id']) ? intval($_POST['test_id']) : null;
    $mapel = isset($_POST['mapel']) ? htmlspecialchars($_POST['mapel']) : null;
    $event = isset($_POST['event']) ? htmlspecialchars($_POST['event']) : 'unknown_event';

    // Validasi data
    if ($test_id !== null && $mapel !== null) {
        // Ambil user_id dari sesi (misalnya pengguna yang sedang login)
        session_start();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        if ($user_id === null) {
            die('User tidak ditemukan. Pastikan login terlebih dahulu.');
        }

        // Query untuk menyimpan log ke database
        $sql = "INSERT INTO tab_switch_logs (user_id, test_id, mapel, event) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Bind parameter dan eksekusi query
        $stmt->bind_param('iiss', $user_id, $test_id, $mapel, $event);

        if ($stmt->execute()) {
            echo "Log berhasil disimpan!";
        } else {
            echo "Gagal menyimpan log: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Data tidak lengkap.";
    }
} else {
    echo "Metode request tidak valid.";
}
?>