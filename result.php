<?php

require_once 'config.php';

// Pastikan test_id tersedia di URL
if (!isset($_GET['test_id'])) {
    die('Parameter test_id tidak ditemukan!');
}

// Ambil test_id dari URL
$test_id = intval($_GET['test_id']);

// Ambil informasi tes berdasarkan test_id
$sql = "SELECT * FROM tests WHERE test_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement (tests): ' . $conn->error);
}
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Tes tidak ditemukan!');
}

$test = $result->fetch_assoc();
$mapel = $test['mapel']; // Nama mata pelajaran dari tes

// Ambil semua soal berdasarkan test_id
$sql_questions = "SELECT * FROM questions WHERE test_id = ?";
$stmt_questions = $conn->prepare($sql_questions);
if ($stmt_questions === false) {
    die('Error preparing statement (questions): ' . $conn->error);
}
$stmt_questions->bind_param('i', $test_id);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// Ambil jawaban dari form
$total_questions = $questions_result->num_rows;
$correct_answers = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses jawaban yang dikirimkan
    foreach ($_POST['answer'] as $question_id => $answer) {
        // Cek apakah jawaban benar dengan mengambil data soal berdasarkan question_id
        $sql_answer = "SELECT correct_option FROM questions WHERE test_id = ? AND question_id = ?";
        $stmt_answer = $conn->prepare($sql_answer);
        if ($stmt_answer === false) {
            die('Error preparing statement (correct_option): ' . $conn->error);
        }
        $stmt_answer->bind_param('ii', $test_id, $question_id);
        $stmt_answer->execute();
        $stmt_answer->store_result();
        $stmt_answer->bind_result($correct_option);

        if ($stmt_answer->fetch()) {
            // Cek apakah jawaban pengguna benar
            if ($answer === $correct_option) {
                $correct_answers++;
            }
        }
    }

    // Hitung skor
    $score = ($correct_answers / $total_questions) * 100; // Skor dalam persen
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tes <?php echo htmlspecialchars($mapel); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 20px;
        }

        .result {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Hasil Tes: <?php echo htmlspecialchars($mapel); ?></h1>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="result">
                <h2>Skor Anda: <?php echo round($score, 2); ?>%</h2>
                <p>Total Soal: <?php echo $total_questions; ?></p>
                <p>Jawaban Benar: <?php echo $correct_answers; ?></p>
            </div>
        <?php else: ?>
            <p>Terjadi kesalahan. Formulir belum dikirimkan atau waktu ujian sudah habis.</p>
        <?php endif; ?>

        <a href="index.php">Kembali ke halaman utama</a>
    </div>

</body>

</html>