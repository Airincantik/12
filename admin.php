<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gambar Peserta</title>
    <style>
        /* CSS untuk mengatur gambar responsif dan tata letak halaman */

        /* Mengatur gambar agar responsif */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Styling untuk header */
        header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
        }

        header h1 {
            font-size: 1.8rem;
        }

        /* Styling untuk tombol back */
        .back-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        /* Styling untuk gambar dalam gallery */
        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            width: 320px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .gallery-img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        /* Styling jika tidak ada gambar */
        .no-data {
            color: red;
            font-size: 18px;
            font-weight: bold;
        }

        /* Menambahkan ruang untuk gallery */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>

<body>
    <header>
        <img src="../soal/logo.png" align="left" alt="logo" width="100" height="100">
        <h1>Daftar Gambar Peserta</h1>
        <center>
        <a href="index.php" class="back-button">Back to Dashboard</a>
    </center>
    </header>

    <div class="container">
        <?php
        // Direktori tempat menyimpan gambar
        $upload_dir = '../uploads/';

        // Mengambil semua gambar dalam direktori tanpa folder (mengambil semua file gambar dalam direktori)
        $images = glob($upload_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);  // Mengambil gambar dengan ekstensi yang sesuai

        if (count($images) > 0):
        ?>
            <div class="gallery">
                <?php foreach ($images as $image_path): ?>
                    <div class="card">
                        <p><strong>Nama File:</strong> <?php echo htmlspecialchars(basename($image_path)); ?></p>
                        <p><strong>Waktu Upload:</strong> <?php echo date("Y-m-d H:i:s", filemtime($image_path)); ?></p>
                        <img src="<?php echo $image_path; ?>" alt="Gambar Peserta" class="gallery-img">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <center>
                <p class="no-data">Tidak ada gambar yang ditemukan di direktori uploads.</p>
            </center>
        <?php endif; ?>
    </div>

</body>

</html>
