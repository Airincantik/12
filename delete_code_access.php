<?php
require_once '../config.php'; // Koneksi database

// Cek apakah ada ID yang diterima dari parameter URL
if (isset($_GET['id'])) {
    $code_id = $_GET['id'];

    // Query untuk menghapus kode akses
    $query = "DELETE FROM access_codes WHERE code_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param('i', $code_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Jika penghapusan berhasil, lakukan redirect ke view_tests.php
        header("Location: view_access_codes.php?message=acccess_codes successfully deleted.");
        exit();
    } else {
        // Jika penghapusan gagal, beri pesan kesalahan
        echo "Failed to delete access_codes ";
    }

    $stmt->close();
} else {
    echo "No access_codes  specified.";
}

$conn->close();
?>