<?php
require_once '../config.php'; // Koneksi database

// Cek apakah ada ID yang diterima dari parameter URL
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];

    // Pastikan ID adalah angka
    if (!is_numeric($question_id)) {
        die("Invalid question ID.");
    }

    // Query untuk menghapus soal berdasarkan question_id
    $query = "DELETE FROM questions WHERE question_id = ?"; // Menggunakan 'id' untuk menghapus berdasarkan ID soal
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    // Bind parameter dan eksekusi query
    $stmt->bind_param('i', $question_id);
    $stmt->execute();

    // Cek apakah penghapusan berhasil
    if ($stmt->affected_rows > 0) {
        // Jika penghapusan berhasil, lakukan redirect ke view_questions.php
        header("Location: view_questions.php?message=Question successfully deleted.");
        exit();
    } else {
        // Jika penghapusan gagal, beri pesan kesalahan
        echo "Failed to delete question.";
    }

    // Menutup prepared statement
    $stmt->close();
} else {
    // Menampilkan pesan jika ID tidak ditemukan
    echo "No question specified.";
}

// Menutup koneksi database
$conn->close();
?>