<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Umum</title>
</head>

<body>
    <h1>Panduan Umum untuk Tes</h1>
    <p>Berikut adalah panduan umum sebelum Anda memulai tes.</p>

    <!-- Tombol untuk melanjutkan ke countdown -->
    <a href="index.php">Lanjutkan ke Countdown</a>
    <script>// Fungsi untuk memperbarui progress saat berpindah halaman
        function updateProgress(currentPage, timeSpent) {
            const userId = <?php echo $user_id; ?>;  // Menggunakan session atau variabel yang sudah ada
            const testId = <?php echo $test_id; ?>;  // Menggunakan session atau variabel yang sudah ada

            // Mengirim permintaan ke server untuk memperbarui progress
            fetch(`update_progress.php?user_id=${userId}&test_id=${testId}&current_page=${currentPage}&time_spent=${timeSpent}`)
                .then(response => response.text())  // Ambil respons dari server
                .then(data => {
                    console.log(data);  // Tampilkan hasil jika berhasil update
                })
                .catch(error => {
                    console.error('Error updating progress:', error);  // Tampilkan error jika gagal
                });
        }

        // Contoh pemanggilan fungsi saat berpindah ke soal esai
        updateProgress('essay_section', 600);  // Misalnya, peserta berada di soal esai dan sudah menghabiskan 10 menit
    </script>
</body>

</html>