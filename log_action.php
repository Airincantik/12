<?php
require_once '../config.php'; // Pastikan koneksi ke database sudah ada

// Pastikan aksi yang dikirim adalah valid
if (isset($_POST['proktor_id']) && isset($_POST['action_type']) && isset($_POST['action_details'])) {
    $proktor_id = $_POST['proktor_id'];
    $action_type = $_POST['action_type'];
    $action_details = $_POST['action_details'];

    // Query untuk menyimpan aksi ke dalam database
    $query = "INSERT INTO proktor_actions (proktor_id, action_type, action_details) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $proktor_id, $action_type, $action_details);
    $stmt->execute();

    // Menampilkan hasil (misalnya status sukses)
    echo "Aksi berhasil dicatat!";
}
?>
