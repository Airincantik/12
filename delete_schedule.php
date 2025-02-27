<?php
require_once '../config.php'; // Koneksi database

// Cek apakah ada ID yang diterima dari parameter URL
if (isset($_GET['id'])) {
    $code_id = $_GET['id'];

    // Query untuk menghapus kode akses
    $query = "DELETE FROM schedules WHERE schedule_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param('i', $code_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Jika penghapusan berhasil, lakukan redirect ke view_tests.php
        header("Location: view_schedule.php?message=schedulessuccessfully deleted.");
        exit();
    } else {
        // Jika penghapusan gagal, beri pesan kesalahan
        echo "Failed to delete schedules";
    }

    $stmt->close();
} else {
    echo "No schedules specified.";
}

$conn->close();
?>