<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture Gambar Peserta</title>
</head>

<body>

    <video id="webcam" width="640" height="480" autoplay></video>
    <canvas id="canvas" style="display:none;"></canvas>

    <script>
        // Mengakses webcam menggunakan getUserMedia
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                // Menampilkan stream webcam ke elemen video
                document.getElementById('webcam').srcObject = stream;
            })
            .catch(function (error) {
                console.error("Error accessing webcam: ", error);
            });

        // Fungsi untuk menangkap gambar dari webcam
        function captureImage() {
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Set ukuran canvas sesuai dengan video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Menggambar frame video ke dalam canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Menyimpan gambar dalam format Base64
            const imageData = canvas.toDataURL('image/jpeg');

            // Kirim gambar ke server untuk disimpan
            uploadImage(imageData);
        }

        // Fungsi untuk mengirim gambar ke server menggunakan AJAX
        function uploadImage(imageData) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'capture_upload.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Kirim data gambar ke server
            xhr.send('image_data=' + encodeURIComponent(imageData));

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log("Gambar berhasil diupload");
                } else {
                    console.log("Terjadi kesalahan dalam mengupload gambar");
                }
            };
        }

        // Menangkap gambar setiap 2 menit (120000 ms)
        setInterval(captureImage, 120000); // 120000 ms = 2 menit
    </script>

</body>

</html>