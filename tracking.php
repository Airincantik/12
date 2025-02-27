<?php
// Koneksi ke database
require_once 'config.php';

// Pastikan user login
if (!isset($_SESSION['username'])) {  // Pastikan username ada di session
    die('User belum login!');
}

$username = $_SESSION['username']; // Mengambil username dari session
$test_id = isset($_POST['test_id']) ? $_POST['test_id'] : null;
$mapel = isset($_POST['mapel']) ? $_POST['mapel'] : null;
$access_code = isset($_POST['access_code']) ? $_POST['access_code'] : '';
$step = isset($_POST['step']) ? $_POST['step'] : '';
$time_spent = isset($_POST['time_spent']) ? $_POST['time_spent'] : 0;

// Ambil durasi tes
$sql = "SELECT * FROM tests WHERE test_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement (tests): ' . $conn->error);
}
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result();

// Ambil data tes
if ($result->num_rows === 0) {
    die('Tes tidak ditemukan!');
}

$test = $result->fetch_assoc();
$duration = $test['duration'];  // Durasi tes

// Fungsi untuk menyimpan tracking ke database
function saveTracking($username, $test_id, $mapel, $step, $access_code, $duration, $time_spent)
{
    global $conn;

    // Cek apakah variabel yang diperlukan tidak kosong
    if (empty($username) || empty($test_id) || empty($mapel) || empty($step) || empty($access_code)) {
        die('Parameter tidak lengkap untuk tracking!');
    }

    $sql = "INSERT INTO user_tracking (username, test_id, mapel, step, access_code, duration, time_spent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Periksa kesalahan saat mempersiapkan statement
    if ($stmt === false) {
        die('Error preparing statement (tracking): ' . $conn->error);
    }

    // Bind parameter untuk query
    $stmt->bind_param('sisssii', $username, $test_id, $mapel, $step, $access_code, $duration, $time_spent);

    // Eksekusi query dan periksa apakah berhasil
    if (!$stmt->execute()) {
        die('Error executing query (tracking): ' . $stmt->error);
    }

    // Opsional: Kembali status sukses
    echo "Tracking berhasil disimpan!";
}

// Menyimpan tracking
saveTracking($username, $test_id, $mapel, $step, $access_code, $duration, $time_spent);

?>