<?php
require_once 'config.php';
session_start();

// Pastikan peserta sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil soal dan jawaban dari database
$sql = "SELECT * FROM questions WHERE test_id = ?";  // Pastikan `test_id` dikirimkan ke halaman ini
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_GET['test_id']);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Atur waktu countdown (misalnya 3 menit)
$countdown_time = 180;  // dalam detik

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countdown Tes</title>
    <script>
        // Waktu countdown dalam detik
        var countdownTime = <?php echo $countdown_time; ?>;

        function startCountdown() {
            var countdownDisplay = document.getElementById('countdown');
            var interval = setInterval(function () {
                var minutes = Math.floor(countdownTime / 60);
                var seconds = countdownTime % 60;
                countdownDisplay.textContent = minutes + "m " + seconds + "s";

                if (countdownTime <= 0) {
                    clearInterval(interval);
                    window.location.href = "test.php?test_id=<?php echo $_GET['test_id']; ?>"; // Arahkan ke halaman test
                } else {
                    countdownTime--;
                }
            }, 1000);
        }

        window.onload = startCountdown;
    </script>
</head>

<body>
    <h1>Countdown Sebelum Tes Dimulai</h1>
    <p>Countdown sebelum tes dimulai:</p>
    <div id="countdown"></div>

    <h2>Soal dan Jawaban Benar</h2>
    <?php if (count($questions) > 0): ?>
        <div>
            <?php foreach ($questions as $question): ?>
                <div>
                    <p><strong>Soal: </strong><?php echo $question['question_text']; ?></p>
                    <p><strong>Jawaban yang benar: </strong><?php echo $question['correct_answer']; ?></p>
                    <p><strong>Alasan: </strong><?php echo $question['explanation']; ?></p>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Tidak ada soal yang ditemukan.</p>
    <?php endif; ?>

</body>

</html>