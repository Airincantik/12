<?php
require_once 'config.php'; // Koneksi ke database

// Pastikan semua data tersedia
if (!isset($_POST['user_id'], $_POST['test_id'], $_POST['mapel'])) {
    die('Data tidak lengkap!');
}

$user_id = intval($_POST['user_id']);
$test_id = intval($_POST['test_id']);
$mapel = htmlspecialchars($_POST['mapel']);

// Simpan log ke database
$sql = "INSERT INTO tab_switch_logs (user_id, test_id, mapel) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}
$stmt->bind_param('iis', $user_id, $test_id, $mapel);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Log berhasil disimpan";
} else {
    echo "Gagal menyimpan log";
}
?>