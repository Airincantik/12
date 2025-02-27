<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Memastikan ada data gambar yang dikirim
if (isset($_POST['image'])) {
    // Mendapatkan data gambar base64
    $imageData = $_POST['image'];

    // Menghapus prefix data URI (data:image/png;base64,)
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData); // Dekode base64 menjadi gambar

    // Mendapatkan informasi yang dibutuhkan untuk penamaan file
    // Misalnya: ambil username, test name, dan mapel dari session atau parameter lainnya
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';  // Ambil username dari session
    $testName = isset($_SESSION['test_name']) ? $_SESSION['test_name'] : 'test';      // Ambil test name dari session
    $mapel = isset($_SESSION['mapel']) ? $_SESSION['mapel'] : 'unknown';              // Ambil mapel dari session

    // Menggunakan timestamp untuk memastikan nama file unik
    $timestamp = time();

    // Format penamaan file: username_testname_mapel_timestamp.png
    $filePath = './uploads/' . $username . '_' . $testName . '_' . $mapel . '_' . $timestamp . '.png';

    // Menyimpan file gambar
    if (file_put_contents($filePath, $imageData)) {
        echo json_encode(['success' => true, 'message' => 'Gambar berhasil di-upload']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gambar gagal disimpan di server']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gambar tidak ditemukan']);
}
?>