<?php
require_once '../config.php';

// Pastikan 'question_id' ada di URL
if (!isset($_GET['question_id'])) {
    die('Parameter question_id tidak ditemukan!');
}

$question_id = intval($_GET['question_id']);  // Ambil question_id dari URL

// Ambil data soal berdasarkan question_id
$sql = "SELECT * FROM questions WHERE question_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $question_id);
$stmt->execute();
$result = $stmt->get_result();

// Jika soal tidak ditemukan
if ($result->num_rows === 0) {
    die('Soal tidak ditemukan!');
}

$question = $result->fetch_assoc();

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data soal dan update
    $question_text = htmlspecialchars($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $correct_option = isset($_POST['correct_option']) ? $_POST['correct_option'] : null;
    $checkbox_answers = isset($_POST['checkbox_answers']) ? implode(',', $_POST['checkbox_answers']) : null;

    // Proses upload gambar soal jika ada
    $question_image = null;
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] == 0) {
        $question_image = handleFileUpload('../soal', $_FILES['question_image']);
    }

    // Update soal dalam database
    if ($question_type == 'multiple_choice') {
        // Ambil data opsi jawaban untuk multiple choice
        $option_a = $_POST['option_a'];
        $option_b = $_POST['option_b'];
        $option_c = $_POST['option_c'];
        $option_d = $_POST['option_d'];
        // Update query untuk multiple choice
        $sql_update = "UPDATE questions SET question_text = ?, question_type = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?, question_image = ? WHERE question_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ssssssssi', $question_text, $question_type, $option_a, $option_b, $option_c, $option_d, $correct_option, $question_image, $question_id);
    } elseif ($question_type == 'checkbox') {
        // Update query untuk checkbox
        $sql_update = "UPDATE questions SET question_text = ?, question_type = ?, checkbox_answers = ?, correct_option = ?, question_image = ? WHERE question_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('sssssi', $question_text, $question_type, $checkbox_answers, $correct_option, $question_image, $question_id);
    } elseif ($question_type == 'essay') {
        // Update query untuk essay
        $sql_update = "UPDATE questions SET question_text = ?, question_type = ?, correct_option = ?, question_image = ? WHERE question_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('sssii', $question_text, $question_type, $correct_option, $question_image, $question_id);
    }

    $stmt_update->execute();

    // Redirect setelah berhasil update
    header("Location: view_questions.php");
    exit;
}

// Function to handle file upload
function handleFileUpload($uploadDir, $file) {
    // Validasi file gambar
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($file['tmp_name']);
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($fileType, $allowedTypes)) {
        die("Tipe file tidak valid: $fileType");
    }
    if ($file['size'] > $maxFileSize) {
        die("Ukuran file terlalu besar: " . $file['name']);
    }

    $filePath = $uploadDir . '/' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return basename($file['name']);
    } else {
        die("Gagal mengunggah file: " . $file['name']);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Soal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 20px;
        }
        .question-container {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            height: 100px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Soal - ID: <?php echo $question['question_id']; ?></h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="question-container">
            <label for="question_text">Soal:</label>
            <textarea name="question_text" id="question_text"><?php echo htmlspecialchars($question['question_text']); ?></textarea>
        </div>

        <div class="question-container">
            <label for="question_type">Tipe Soal:</label>
            <select name="question_type" id="question_type">
                <option value="multiple_choice" <?php echo ($question['question_type'] == 'multiple_choice') ? 'selected' : ''; ?>>Pilihan Ganda</option>
                <option value="checkbox" <?php echo ($question['question_type'] == 'checkbox') ? 'selected' : ''; ?>>Checkbox</option>
                <option value="essay" <?php echo ($question['question_type'] == 'essay') ? 'selected' : ''; ?>>Esai</option>
            </select>
        </div>

        <div class="question-container">
            <label for="question_image">Gambar Soal:</label>
            <input type="file" name="question_image" id="question_image">
            <?php if ($question['question_image']): ?>
                <div class="image-preview">
                    <img src="../soal/<?php echo $question['question_image']; ?>" alt="Gambar Soal">
                </div>
            <?php endif; ?>
        </div>

        <?php if ($question['question_type'] == 'multiple_choice'): ?>
            <div class="question-container">
                <label for="option_a">A:</label>
                <input type="text" name="option_a" id="option_a" value="<?php echo htmlspecialchars($question['option_a']); ?>">

                <label for="option_b">B:</label>
                <input type="text" name="option_b" id="option_b" value="<?php echo htmlspecialchars($question['option_b']); ?>">

                <label for="option_c">C:</label>
                <input type="text" name="option_c" id="option_c" value="<?php echo htmlspecialchars($question['option_c']); ?>">

                <label for="option_d">D:</label>
                <input type="text" name="option_d" id="option_d" value="<?php echo htmlspecialchars($question['option_d']); ?>">

                <label for="correct_option">Jawaban Benar:</label>
                <select name="correct_option" id="correct_option">
                    <option value="A" <?php echo ($question['correct_option'] == 'A') ? 'selected' : ''; ?>>A</option>
                    <option value="B" <?php echo ($question['correct_option'] == 'B') ? 'selected' : ''; ?>>B</option>
                    <option value="C" <?php echo ($question['correct_option'] == 'C') ? 'selected' : ''; ?>>C</option>
                    <option value="D" <?php echo ($question['correct_option'] == 'D') ? 'selected' : ''; ?>>D</option>
                </select>
            </div>
        <?php elseif ($question['question_type'] == 'checkbox'): ?>
            <div class="question-container">
                <label for="checkbox_answers">Jawaban Benar (Pilih beberapa):</label>
                <label><input type="checkbox" name="checkbox_answers[]" value="A" <?php echo in_array('A', explode(',', $question['checkbox_answers'])) ? 'checked' : ''; ?>> A</label>
                <label><input type="checkbox" name="checkbox_answers[]" value="B" <?php echo in_array('B', explode(',', $question['checkbox_answers'])) ? 'checked' : ''; ?>> B</label>
                <label><input type="checkbox" name="checkbox_answers[]" value="C" <?php echo in_array('C', explode(',', $question['checkbox_answers'])) ? 'checked' : ''; ?>> C</label>
                <label><input type="checkbox" name="checkbox_answers[]" value="D" <?php echo in_array('D', explode(',', $question['checkbox_answers'])) ? 'checked' : ''; ?>> D</label>
            </div>
        <?php elseif ($question['question_type'] == 'essay'): ?>
            <div class="question-container">
                <label for="correct_option">Jawaban Esai:</label>
                <textarea name="correct_option" id="correct_option"><?php echo htmlspecialchars($question['correct_option']); ?></textarea>
            </div>
        <?php endif; ?>

        <div class="question-container">
            <button type="submit">Simpan Perubahan</button>
        </div>
    </form>
</div>

</body>
</html>
