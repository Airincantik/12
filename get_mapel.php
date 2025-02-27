<?php
include '../config.php';

// Query untuk mengambil semua mata pelajaran
$result = $conn->query("SELECT * FROM tests");

if ($result->num_rows > 0) {
    // Menampilkan opsi mata pelajaran
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . $row['mapel'] . "</option>";
    }
} else {
    echo "<option value=''>Tidak ada mata pelajaran tersedia</option>";
}
?>
