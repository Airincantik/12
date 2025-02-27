<?php
include '../config.php';

// Ambil parameter mapel dari URL
$mapel = $_GET['mapel'];

// Query untuk mengambil jenis tes berdasarkan mata pelajaran
$stmt = $conn->prepare("SELECT * FROM tests WHERE mapel = ?");
$stmt->bind_param("i", $mapel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Menampilkan opsi jenis tes
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . $row['test_name'] . "</option>";
    }
} else {
    echo "<option value=''>Tidak ada jenis tes tersedia</option>";
}
?>