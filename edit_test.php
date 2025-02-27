<?php

require_once '../config.php';

// Pastikan test_id tersedia di URL
if (!isset($_GET['test_id'])) {
    die('Parameter test_id tidak ditemukan!');
}

$test_id = intval($_GET['test_id']); // Ambil test_id dari URL

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

// Ambil soal berdasarkan test_id
$sql_questions = "SELECT * FROM questions WHERE test_name = ?";
$stmt_questions = $conn->prepare($sql_questions);
if ($stmt_questions === false) {
    die('Error preparing statement (questions): ' . $conn->error);
}
$stmt_questions->bind_param('i', $test_id);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// Simpan form input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_name = htmlspecialchars($_POST['test_name']);
    $duration = intval($_POST['duration']);

    // Update informasi tes (test_name, duration)
    $sql_update_test = "UPDATE tests SET test_name = ?, duration = ? WHERE test_id = ?";
    $stmt_update_test = $conn->prepare($sql_update_test);
    $stmt_update_test->bind_param('sii', $test_name, $duration, $test_id);
    $stmt_update_test->execute();

    // Update soal berdasarkan input
    if (isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $question_id => $question_data) {
            $question_text = htmlspecialchars($question_data['question_text']);
            $option_a = htmlspecialchars($question_data['option_a']);
            $option_b = htmlspecialchars($question_data['option_b']);
            $option_c = htmlspecialchars($question_data['option_c']);
            $option_d = htmlspecialchars($question_data['option_d']);
            $correct_answer = $question_data['correct_answer'];

            // Update soal
            $sql_update_question = "UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE question_id = ?";
            $stmt_update_question = $conn->prepare($sql_update_question);
            $stmt_update_question->bind_param('ssssssi', $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id);
            $stmt_update_question->execute();
        }
    }

    // Setelah update berhasil, redirect ke index.php
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <img src="../soal/logo.png" align="left" alt="logo " width="100" height="100">
    <title>Edit Tes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 20px;
        }

        .question {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .question-container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit Tes: <?php echo htmlspecialchars($test['test_name']); ?></h1>

        <!-- Form untuk mengedit test dan soal -->
        <form method="POST">
            <h2>Informasi Tes</h2>
            <label for="test_name">Nama Tes:</label>
            <input type="text" id="test_name" name="test_name"
                value="<?php echo htmlspecialchars($test['test_name']); ?>" required>

            <label for="duration">Durasi (Menit):</label>
            <input type="number" id="duration" name="duration" value="<?php echo $test['duration']; ?>" required>

            <h2>Soal-Soal</h2>
            <?php
            $number = 1;
            while ($question = $questions_result->fetch_assoc()) {
                ?>
                <div class="question-container">
                    <h3>Soal <?php echo $number++; ?></h3>

                    <label for="question_text_<?php echo $question['question_id']; ?>">Pertanyaan:</label>
                    <input type="text" id="question_text_<?php echo $question['question_id']; ?>"
                        name="questions[<?php echo $question['question_id']; ?>][question_text]"
                        value="<?php echo htmlspecialchars($question['question_text']); ?>" required>

                    <label for="option_a_<?php echo $question['question_id']; ?>">Pilihan A:</label>
                    <input type="text" id="option_a_<?php echo $question['question_id']; ?>"
                        name="questions[<?php echo $question['question_id']; ?>][option_a]"
                        value="<?php echo htmlspecialchars($question['option_a']); ?>" required>

                    <label for="option_b_<?php echo $question['question_id']; ?>">Pilihan B:</label>
                    <input type="text" id="option_b_<?php echo $question['question_id']; ?>"
                        name="questions[<?php echo $question['question_id']; ?>][option_b]"
                        value="<?php echo htmlspecialchars($question['option_b']); ?>" required>

                    <label for="option_c_<?php echo $question['question_id']; ?>">Pilihan C:</label>
                    <input type="text" id="option_c_<?php echo $question['question_id']; ?>"
                        name="questions[<?php echo $question['question_id']; ?>][option_c]"
                        value="<?php echo htmlspecialchars($question['option_c']); ?>" required>

                    <label for="option_d_<?php echo $question['question_id']; ?>">Pilihan D:</label>
                    <input type="text" id="option_d_<?php echo $question['question_id']; ?>"
                        name="questions[<?php echo $question['question_id']; ?>][option_d]"
                        value="<?php echo htmlspecialchars($question['option_d']); ?>" required>

                    <label for="correct_option_<?php echo $question['question_id']; ?>">Jawaban Benar:</label>
                    <select name="questions[<?php echo $question['question_id']; ?>][correct_answer]" required>
                        <option value="A" <?php echo $question['correct_option'] == 'A' ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo $question['correct_option'] == 'B' ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo $question['correct_option'] == 'C' ? 'selected' : ''; ?>>C</option>
                        <option value="D" <?php echo $question['correct_option'] == 'D' ? 'selected' : ''; ?>>D</option>
                    </select>
                </div>
                <?php
            }
            ?>
            <button type="submit">Perbarui Tes</button>
        </form>
    </div>
</body>

</html>