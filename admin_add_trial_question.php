<?php
include '../config.php'; // Include your database connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mapel = $_POST['mapel'];
    $test_name = $_POST['test_name'];

    // Process Multiple Choice Questions if they exist
    if (!empty($_POST['multiple_choice_question_text'])) {
        foreach ($_POST['multiple_choice_question_text'] as $key => $question_text) {
            $option_a = $_POST['option_a'][$key] ?? '';
            $option_b = $_POST['option_b'][$key] ?? '';
            $option_c = $_POST['option_c'][$key] ?? '';
            $option_d = $_POST['option_d'][$key] ?? '';
            $correct_option = $_POST['correct_option'][$key] ?? '';
            $question_image = $_FILES['multiple_choice_image']['name'][$key] ?? NULL;

            // Handle image upload for multiple choice questions
            if (isset($_FILES['multiple_choice_image']['name'][$key])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["multiple_choice_image"]["name"][$key]);

                if (move_uploaded_file($_FILES["multiple_choice_image"]["tmp_name"][$key], $target_file)) {
                    $question_image = basename($_FILES["multiple_choice_image"]["name"][$key]);
                } else {
                    $question_image = NULL;
                }
            }

            $sql = "INSERT INTO trial_questions 
                (mapel, test_name, question_text, question_type, option_a, option_b, option_c, option_d, correct_option, question_image) 
                VALUES ('$mapel', '$test_name', '$question_text', 'multiple_choice', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option', '$question_image')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $conn->error;
            }
        }
    }

    // Process Checkbox Questions if they exist
    if (!empty($_POST['checkbox_question_text'])) {
        foreach ($_POST['checkbox_question_text'] as $key => $question_text) {
            $option_1 = $_POST['checkbox_option_1'][$key] ?? '';
            $option_2 = $_POST['checkbox_option_2'][$key] ?? '';
            $option_3 = $_POST['checkbox_option_3'][$key] ?? '';
            $option_4 = $_POST['checkbox_option_4'][$key] ?? '';

            $checkbox_answers = [];
            if (!empty($_POST['checkbox_answers'][$key])) {
                foreach ($_POST['checkbox_answers'][$key] as $answer) {
                    if ($answer == 'checkbox_option_1') $checkbox_answers[] = $option_1;
                    elseif ($answer == 'checkbox_option_2') $checkbox_answers[] = $option_2;
                    elseif ($answer == 'checkbox_option_3') $checkbox_answers[] = $option_3;
                    elseif ($answer == 'checkbox_option_4') $checkbox_answers[] = $option_4;
                }
            }
            $checkbox_answers = implode(',', $checkbox_answers);

            $question_image = $_FILES['checkbox_image']['name'][$key] ?? NULL;

            if (isset($_FILES['checkbox_image']['name'][$key])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["checkbox_image"]["name"][$key]);

                if (move_uploaded_file($_FILES["checkbox_image"]["tmp_name"][$key], $target_file)) {
                    $question_image = basename($_FILES["checkbox_image"]["name"][$key]);
                } else {
                    $question_image = NULL;
                }
            }

            $sql = "INSERT INTO trial_questions 
                (mapel, test_name, question_text, question_type, checkbox_option_1, checkbox_option_2, checkbox_option_3, checkbox_option_4, checkbox_answers, question_image) 
                VALUES ('$mapel', '$test_name', '$question_text', 'checkbox', '$option_1', '$option_2', '$option_3', '$option_4', '$checkbox_answers', '$question_image')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $conn->error;
            }
        }
    }

    // Process Essay Questions if they exist
    if (!empty($_POST['essay_question_text'])) {
        foreach ($_POST['essay_question_text'] as $key => $question_text) {
            $question_image = $_FILES['essay_image']['name'][$key] ?? NULL;

            if (isset($_FILES['essay_image']['name'][$key])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["essay_image"]["name"][$key]);

                if (move_uploaded_file($_FILES["essay_image"]["tmp_name"][$key], $target_file)) {
                    $question_image = basename($_FILES["essay_image"]["name"][$key]);
                } else {
                    $question_image = NULL;
                }
            }

            $sql = "INSERT INTO trial_questions 
                (mapel, test_name, question_text, question_type, question_image) 
                VALUES ('$mapel', '$test_name', '$question_text', 'essay', '$question_image')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $conn->error;
            }
        }
    }

    // Success message
    echo "<script>alert('Soal berhasil ditambahkan!'); window.location.href='create_schedule.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal Ujian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .question-section {
            margin-bottom: 20px;
        }

        .question-section h3 {
            margin-top: 20px;
            color: #555;
        }

        .question-section button {
            background-color: #007bff;
            margin-top: 10px;
        }

        .question-section button:hover {
            background-color: #0056b3;
        }

        .question-container {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .question-container input[type="file"] {
            padding: 3px;
        }

        .question-container .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }

        .question-container .delete-btn:hover {
            background-color: #c82333;
        }

        .container-buttons {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Form Tambah Soal Ujian</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="question-section">
            <label for="mapel">Mata Pelajaran:</label>
            <input type="text" name="mapel" id="mapel" required><br><br>

            <label for="test_name">Nama Ujian:</label>
            <input type="text" name="test_name" id="test_name" required><br><br>
        </div>

        <!-- Multiple Choice Questions Section -->
        <div class="question-section" id="multiple_choice_section">
            <h3>Soal Pilihan Ganda</h3>
            <button type="button" onclick="addMultipleChoiceQuestion()">Tambah Soal Pilihan Ganda</button>
            <div id="multiple_choice_questions"></div>
        </div>

        <!-- Checkbox Questions Section -->
        <div class="question-section" id="checkbox_section">
            <h3>Soal Checkbox</h3>
            <button type="button" onclick="addCheckboxQuestion()">Tambah Soal Checkbox</button>
            <div id="checkbox_questions"></div>
        </div>

        <!-- Essay Questions Section -->
        <div class="question-section" id="essay_section">
            <h3>Soal Essay</h3>
            <button type="button" onclick="addEssayQuestion()">Tambah Soal Essay</button>
            <div id="essay_questions"></div>
        </div>

        <div class="container-buttons">
            <button type="submit">Simpan Soal</button>
        </div>
    </form>

    <script>
        function addMultipleChoiceQuestion() {
            var container = document.getElementById('multiple_choice_questions');
            var index = container.children.length;
            var questionHTML = `
                <div class="question-container">
                    <label>Soal Pilihan Ganda ` + (index + 1) + `:</label><br>
                    <textarea name="multiple_choice_question_text[]" required></textarea><br><br>
                    <label>Opsi A:</label><input type="text" name="option_a[]" required><br>
                    <label>Opsi B:</label><input type="text" name="option_b[]" required><br>
                    <label>Opsi C:</label><input type="text" name="option_c[]" required><br>
                    <label>Opsi D:</label><input type="text" name="option_d[]" required><br>
                    <label>Opsi yang benar:</label><input type="text" name="correct_option[]" required><br>
                    <label>Gambar (optional):</label><input type="file" name="multiple_choice_image[]"><br><br>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHTML);
        }

        function addCheckboxQuestion() {
            var container = document.getElementById('checkbox_questions');
            var index = container.children.length;
            var questionHTML = `
                <div class="question-container">
                    <label>Soal Checkbox ` + (index + 1) + `:</label><br>
                    <textarea name="checkbox_question_text[]"></textarea><br><br>
                    <label>Opsi 1:</label><input type="text" name="checkbox_option_1[]"><br>
                    <label>Opsi 2:</label><input type="text" name="checkbox_option_2[]"><br>
                    <label>Opsi 3:</label><input type="text" name="checkbox_option_3[]"><br>
                    <label>Opsi 4:</label><input type="text" name="checkbox_option_4[]"><br>
                    <label>Jawaban yang benar:</label><br>
                    <input type="checkbox" name="checkbox_answers[` + index + `][]" value="checkbox_option_1"> Opsi 1<br>
                    <input type="checkbox" name="checkbox_answers[` + index + `][]" value="checkbox_option_2"> Opsi 2<br>
                    <input type="checkbox" name="checkbox_answers[` + index + `][]" value="checkbox_option_3"> Opsi 3<br>
                    <input type="checkbox" name="checkbox_answers[` + index + `][]" value="checkbox_option_4"> Opsi 4<br><br>
                    <label>Gambar (optional):</label><input type="file" name="checkbox_image[]"><br><br>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHTML);
        }

        function addEssayQuestion() {
            var container = document.getElementById('essay_questions');
            var index = container.children.length;
            var questionHTML = `
                <div class="question-container">
                    <label>Soal Essay ` + (index + 1) + `:</label><br>
                    <textarea name="essay_question_text[]"></textarea><br><br>
                    <label>Gambar (optional):</label><input type="file" name="essay_image[]"><br><br>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHTML);
        }
    </script>
</body>
</html>
