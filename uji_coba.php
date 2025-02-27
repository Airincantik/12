<?php
// Memulai sesi dan koneksi database
require_once 'config.php'; // Koneksi database

// Memastikan parameter test_id dan mapel ada di URL
if (!isset($_GET['test_id']) || !isset($_GET['mapel'])) {
    die('Parameter test_id atau mapel tidak ditemukan! Harap pastikan URL lengkap dengan parameter test_id dan mapel.');
}

// Ambil test_id dan mapel dari URL
$test_id = intval($_GET['test_id']);  // Pastikan test_id adalah integer
$mapel = htmlspecialchars($_GET['mapel']); // Menghindari XSS

// Ambil informasi tes berdasarkan test_id
$sql = "SELECT * FROM tests WHERE test_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement (tests): ' . $conn->error);
}
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result();

// Jika tes tidak ditemukan, tampilkan error
if ($result->num_rows === 0) {
    die('Tes tidak ditemukan!');
}

$test = $result->fetch_assoc();
$duration = $test['duration']; // Durasi tes dalam menit dari tabel 'tests'

// Ambil panduan berdasarkan mapel dari tabel 'access_codes'
$sql_guideline = "SELECT guide FROM access_codes WHERE mapel = ?";
$stmt_guideline = $conn->prepare($sql_guideline);
if ($stmt_guideline === false) {
    die('Error preparing statement (access_codes): ' . $conn->error);
}
$stmt_guideline->bind_param('s', $mapel);
$stmt_guideline->execute();
$guideline_result = $stmt_guideline->get_result();

// Ambil panduan jika ada
$guideline_text = '';
if ($guideline_result->num_rows > 0) {
    $guideline_data = $guideline_result->fetch_assoc();
    $guideline_text = nl2br(htmlspecialchars($guideline_data['guide'])); // Format teks agar baris baru tetap terlihat
} else {
    $guideline_text = 'Panduan umum tidak tersedia untuk mapel ini.';
}

// Ambil semua soal berdasarkan test_id
$sql_questions = "SELECT * FROM trial_questions WHERE test_id = ?";
$stmt_questions = $conn->prepare($sql_questions);
if ($stmt_questions === false) {
    die('Error preparing statement (questions): ' . $conn->error);
}
$stmt_questions->bind_param('i', $test_id);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// Jika soal tidak ditemukan, tampilkan error
if ($questions_result->num_rows === 0) {
    die('Soal tidak ditemukan!');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes <?php echo htmlspecialchars($mapel); ?></title>
    <style>
        .question {
            margin-bottom: 20px;
        }

        .question h3 {
            font-size: 16px;
        }

        .question .options label {
            display: block;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .next-btn {
            margin-top: 20px;
        }

        .guidelines {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #ccc;
        }

        .guidelines h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Tes: <?php echo htmlspecialchars($mapel); ?></h1>

        <!-- Panduan Umum Mapel -->
        <div class="guidelines">
            <h2>Panduan Umum untuk Mapel: <?php echo htmlspecialchars($mapel); ?></h2>
            <p><?php echo $guideline_text; ?></p>
        </div>

        <form id="testForm" action="submit.php?test_id=<?php echo $test_id; ?>&mapel=<?php echo urlencode($mapel); ?>"
            method="POST">
            <input type="hidden" name="test_id" value="<?php echo $test_id; ?>" />
            <input type="hidden" name="mapel" value="<?php echo $mapel; ?>" />

            <?php
            // Pisahkan soal berdasarkan jenisnya
            $multiple_choice_questions = [];
            $checkbox_questions = [];
            $essay_questions = [];

            while ($question = $questions_result->fetch_assoc()) {
                if ($question['question_type'] == 'multiple_choice') {
                    $multiple_choice_questions[] = $question;
                } elseif ($question['question_type'] == 'checkbox') {
                    $checkbox_questions[] = $question;
                } elseif ($question['question_type'] == 'essay') {
                    $essay_questions[] = $question;
                }
            }

            // Tampilkan soal Pilihan Ganda jika ada
            if (!empty($multiple_choice_questions)) {
                echo "<div class='section' id='multiple_choice_section'>";
                echo "<h2>Soal Pilihan Ganda</h2>";
                $number = 1;
                foreach ($multiple_choice_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";

                    // Menyiapkan opsi jawaban sebagai huruf ('a', 'b', 'c', 'd')
                    $options = [
                        'a' => $question['option_a'],
                        'b' => $question['option_b'],
                        'c' => $question['option_c'],
                        'd' => $question['option_d']
                    ];

                    echo "<div class='options'>";
                    // Loop untuk menampilkan pilihan jawaban dengan huruf
                    foreach ($options as $key => $option) {
                        echo "<label><input type='radio' name='answer[{$question['question_id']}]' value='$key'> $option</label>";
                    }
                    echo "</div>";

                    if (!empty($question['question_image'])) {
                        echo "<img src='./uploads/" . htmlspecialchars($question['question_image']) . "' class='question-image' alt='Gambar Soal'>";
                    }

                    echo "</div>";
                    $number++;
                }
                echo "<button type='button' class='next-btn' onclick='showNextSection(\"checkbox_section\")'>Lanjut ke Soal Checkbox</button>";
                echo "</div>";
            }

            // Tampilkan soal Checkbox jika ada
            if (!empty($checkbox_questions)) {
                echo "<div class='section' id='checkbox_section'>";
                echo "<h2>Soal Checkbox</h2>";
                $number = 1;
                foreach ($checkbox_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";

                    $checkbox_options = explode(',', $question['checkbox_options']);
                    echo "<div class='options'>";
                    foreach ($checkbox_options as $key => $option) {
                        echo "<label><input type='checkbox' name='answer[{$question['question_id']}][]' value='" . ($key + 1) . "'> $option</label>";
                    }
                    echo "</div>";

                    if (!empty($question['question_image'])) {
                        echo "<img src='./uploads/" . htmlspecialchars($question['question_image']) . "' class='question-image' alt='Gambar Soal'>";
                    }

                    echo "</div>";
                    $number++;
                }
                echo "<button type='button' class='next-btn' onclick='showNextSection(\"essay_section\")'>Lanjut ke Soal Esai</button>";
                echo "</div>";
            }

            // Tampilkan soal Esai jika ada
            if (!empty($essay_questions)) {
                echo "<div class='section' id='essay_section'>";
                echo "<h2>Soal Esai</h2>";
                $number = 1;
                foreach ($essay_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";

                    echo "<textarea name='answer[{$question['question_id']}]' rows='4' cols='50'></textarea>";

                    if (!empty($question['question_image'])) {
                        echo "<img src='./uploads/" . htmlspecialchars($question['question_image']) . "' class='question-image' alt='Gambar Soal'>";
                    }

                    echo "</div>";
                    $number++;
                }
                echo "<button type='submit' class='next-btn'>Kirim Jawaban</button>";
                echo "</div>";
            }
            ?>
        </form>

        <script>
            // Fungsi untuk menampilkan section berikutnya
            function showNextSection(sectionId) {
                const nextSection = document.getElementById(sectionId);
                if (nextSection && nextSection.innerHTML.trim() !== '') {
                    document.querySelectorAll('.section').forEach(function (section) {
                        section.classList.remove('active');
                    });
                    nextSection.classList.add('active');
                } else {
                    // Jika tidak ada soal di bagian ini, langsung ke bagian berikutnya
                    if (sectionId === 'multiple_choice_section') {
                        showNextSection('checkbox_section');
                    } else if (sectionId === 'checkbox_section') {
                        showNextSection('essay_section');
                    } else {
                        // Semua soal telah selesai, kirim form
                        document.getElementById('testForm').submit();
                    }
                }
            }

            // Tampilkan soal pertama jika ada
            window.onload = function () {
                if (document.getElementById('multiple_choice_section').innerHTML.trim() !== '') {
                    document.getElementById('multiple_choice_section').classList.add('active');
                } else if (document.getElementById('checkbox_section').innerHTML.trim() !== '') {
                    document.getElementById('checkbox_section').classList.add('active');
                } else if (document.getElementById('essay_section').innerHTML.trim() !== '') {
                    document.getElementById('essay_section').classList.add('active');
                }
            }
        </script>
    </div>
</body>

</html>