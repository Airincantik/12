<?php
// Koneksi ke database
require_once 'config.php';

// Ambil data JSON dari request
$data = json_decode(file_get_contents('php://input'), true);

// Ambil data dari request
$user_id = $data['id'];
$test_id = $data['test_id'];
$current_page = $data['current_page'];  // Soal yang sedang dikerjakan

// Cek apakah ada progres sebelumnya
$sql_check = "SELECT * FROM progress WHERE user_id = ? AND test_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $user_id, $test_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // Jika ada, update progres
    $sql_update = "UPDATE progress SET current_page = ? WHERE user_id = ? AND test_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('iii', $current_page, $user_id, $test_id);
    $stmt_update->execute();
    echo json_encode(['status' => 'updated']);
} else {
    // Jika belum ada, insert baru
    $sql_insert = "INSERT INTO progress (user_id, test_id, current_page) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('iii', $user_id, $test_id, $current_page);
    $stmt_insert->execute();
    echo json_encode(['status' => 'inserted']);
}
?>