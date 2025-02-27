<?php
// Memulai sesi dan koneksi database
require_once 'config.php'; // Koneksi database

// Memastikan session telah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Memastikan parameter mapel dan test_name ada di URL
if (!isset($_GET['mapel']) || !isset($_GET['test_name'])) {
    echo "<script>
            alert('Parameter mapel atau test_name tidak ditemukan! Harap pastikan URL lengkap dengan parameter mapel dan test_name.');
            setTimeout(function() {
                window.location.href = 'akses.php';
            }, 3000); // 3 detik sebelum pengalihan
          </script>";
    exit; // Keluar dari skrip setelah pengalihan
}

// Ambil mapel dan test_name dari URL
$mapel = htmlspecialchars($_GET['mapel']); // Menghindari XSS
$test_name = htmlspecialchars($_GET['test_name']); // Menghindari XSS

// Memastikan user yang mengakses (mengambil username dari session)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null; // Pastikan session username ada

// Jika username tidak ada, tampilkan error atau redirect ke login
if (!$username) {
    echo "<script>
            alert('Username tidak ditemukan dalam session. Harap login terlebih dahulu.');
            setTimeout(function() {
                window.location.href = 'akses.php';
            }, 3000); // 3 detik sebelum pengalihan
          </script>";
    exit; // Keluar dari skrip setelah pengalihan
}

// Fungsi untuk menyimpan tracking ke database
function saveTracking($username, $mapel, $test_name, $step, $time_spent)
{
    global $conn;

    // Pastikan parameter lengkap
    if (empty($username) || empty($mapel) || empty($test_name) || empty($step)) {
        echo "<script>
                alert('Error: Parameter tidak lengkap untuk menyimpan tracking!');
                setTimeout(function() {
                    window.location.href = 'akses.php';
                }, 3000); // 3 detik sebelum pengalihan
              </script>";
        exit; // Keluar dari skrip setelah pengalihan
    }

    // Query untuk menyimpan data tracking
    $sql = "INSERT INTO user_tracking (username, mapel, test_name, step, time_spent) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "<script>
                alert('Error preparing statement (tracking): " . $conn->error . "');
                setTimeout(function() {
                    window.location.href = 'akses.php';
                }, 3000);
              </script>";
        exit;
    }

    // Mengikat parameter ke query
    $stmt->bind_param('sssss', $username, $mapel, $test_name, $step, $time_spent);

    // Eksekusi query
    if (!$stmt->execute()) {
        echo "<script>
                alert('Error executing tracking query: " . $stmt->error . "');
                setTimeout(function() {
                    window.location.href = 'akses.php';
                }, 3000);
              </script>";
        exit;
    }

    return true;
}

// Menyimpan tracking saat melanjutkan ke bagian soal
if (isset($_POST['step'])) {
    $step = $_POST['step'];
    // Hitung waktu yang telah dihabiskan
    $time_spent = time() - $_SESSION['start_time'];
    saveTracking($username, $mapel, $test_name, $step, $time_spent);
}

// Ambil informasi tes berdasarkan mapel dan test_name
$sql = "SELECT * FROM tests WHERE test_name = ? AND mapel = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $test_name, $mapel);
$stmt->execute();
$result = $stmt->get_result();

// Jika tes tidak ditemukan, tampilkan error
if ($result->num_rows === 0) {
    echo "<script>
            alert('Tes tidak ditemukan!');
            setTimeout(function() {
                window.location.href = 'akses.php';
            }, 3000); // 3 detik sebelum pengalihan
          </script>";
    exit; // Keluar dari skrip setelah pengalihan
}

$test = $result->fetch_assoc();
$duration = $test['duration']; // Durasi tes dalam menit dari tabel 'tests'

// Ambil panduan berdasarkan test_name dari tabel 'access_codes'
$sql_guideline = "SELECT guide FROM access_codes WHERE test_name = ?";
$stmt_guideline = $conn->prepare($sql_guideline);
$stmt_guideline->bind_param('s', $test_name);
$stmt_guideline->execute();
$guideline_result = $stmt_guideline->get_result();

// Ambil panduan jika ada
$guideline_text = '';
if ($guideline_result->num_rows > 0) {
    // Jika ada panduan, ambil dan format teks
    $guideline_data = $guideline_result->fetch_assoc();
    $guideline_text = nl2br(htmlspecialchars($guideline_data['guide'])); // Format teks agar baris baru tetap terlihat
} else {
    // Jika tidak ada panduan, tampilkan pesan default
    $guideline_text = 'Panduan umum tidak tersedia untuk tes ini.';
}

// Ambil semua soal berdasarkan mapel dan test_name
$sql_questions = "SELECT * FROM trial_questions WHERE test_name = ? AND mapel = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param('ss', $test_name, $mapel);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// Jika soal tidak ditemukan, tampilkan error
if ($questions_result->num_rows === 0) {
    echo "<script>
            alert('Soal tidak ditemukan!');
            setTimeout(function() {
                window.location.href = 'akses.php';
            }, 3000);
          </script>";
    exit; // Keluar dari skrip setelah pengalihan
}

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

// Hitung durasi per kategori soal berdasarkan jumlah soal
$total_multiple_choice = count($multiple_choice_questions);
$total_checkbox = count($checkbox_questions);
$total_essay = count($essay_questions);
$total_questions = $total_multiple_choice + $total_checkbox + $total_essay;

// Pembagian waktu berdasarkan jumlah soal
$multiple_choice_duration = $total_multiple_choice > 0 ? ($duration * $total_multiple_choice) / $total_questions : 0;
$checkbox_duration = $total_checkbox > 0 ? ($duration * $total_checkbox) / $total_questions : 0;
$essay_duration = $total_essay > 0 ? ($duration * $total_essay) / $total_questions : 0;

$_SESSION['start_time'] = time(); // Memulai waktu tes jika belum dimulai
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes <?php echo htmlspecialchars($mapel); ?> - <?php echo htmlspecialchars($test_name); ?></title>
    <style>
        /* Reset and basic setup */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #007BFF;
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #555;
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
            color: #333;
        }

        .question {
            background-color: #fafafa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .question p {
            font-size: 16px;
            color: #444;
        }

        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .options label {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 45%;
            box-sizing: border-box;
            font-size: 14px;
        }

        .options label:hover {
            background-color: #e3e3e3;
        }

        .question-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .next-btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .next-btn:hover {
            background-color: #0056b3;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .next-btn:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
        }

        .question-container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Tes <?php echo htmlspecialchars($mapel); ?> - <?php echo htmlspecialchars($test_name); ?></h1>

        <!-- Panduan Tes -->
        <div class="guidelines">
            <h2>Panduan Tes</h2>
            <p><?php echo $guideline_text; ?></p>
        </div>

        <!-- Soal -->
        <form id="testForm" action="submit.php?test_name=<?php echo $test_name; ?>&mapel=<?php echo urlencode($mapel); ?>"
            method="GET">
            <!-- Menggunakan test_name dalam input hidden -->
            <input type="hidden" name="test_name" value="<?php echo $test_name; ?>" />
            <input type="hidden" name="mapel" value="<?php echo $mapel; ?>" />

            <!-- Display Multiple Choice Questions -->
            <div class="section active" id="multiple_choice_section">
                <h2>Soal Pilihan Ganda</h2>
                <?php
                $number = 1;
                foreach ($multiple_choice_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";
                    $options = [
                        'a' => $question['option_a'],
                        'b' => $question['option_b'],
                        'c' => $question['option_c'],
                        'd' => $question['option_d']
                    ];
                    echo "<div class='options'>";
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
                ?>
                <button type="button" class="next-btn" onclick="showNextSection('checkbox_section')">Lanjut ke Soal Checkbox</button>
            </div>

            <!-- Display Checkbox Questions -->
            <div class="section" id="checkbox_section">
                <h2>Soal Checkbox</h2>
                <?php
                $number = 1;
                foreach ($checkbox_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";

                    // Ambil opsi checkbox dari kolom terpisah
                    $checkbox_options = [];
                    if (!empty($question['checkbox_option_1'])) {
                        $checkbox_options[] = $question['checkbox_option_1'];
                    }
                    if (!empty($question['checkbox_option_2'])) {
                        $checkbox_options[] = $question['checkbox_option_2'];
                    }
                    if (!empty($question['checkbox_option_3'])) {
                        $checkbox_options[] = $question['checkbox_option_3'];
                    }
                    if (!empty($question['checkbox_option_4'])) {
                        $checkbox_options[] = $question['checkbox_option_4'];
                    }

                    echo "<div class='options'>";
                    foreach ($checkbox_options as $key => $option) {
                        echo "<label><input type='checkbox' name='answer[{$question['question_id']}][]' value='$key'> $option</label>";
                    }
                    echo "</div>";
                    if (!empty($question['question_image'])) {
                        echo "<img src='./uploads/" . htmlspecialchars($question['question_image']) . "' class='question-image' alt='Gambar Soal'>";
                    }
                    echo "</div>";
                    $number++;
                }
                ?>
                <button type="button" class="next-btn" onclick="showNextSection('essay_section')">Lanjut ke Soal Essay</button>
            </div>

            <!-- Display Essay Questions -->
            <div class="section" id="essay_section">
                <h2>Soal Essay</h2>
                <?php
                $number = 1;
                foreach ($essay_questions as $question) {
                    echo "<div class='question'>";
                    echo "<p>$number. " . htmlspecialchars($question['question_text']) . "</p>";
                    echo "<textarea name='answer[{$question['question_id']}]' rows='5'></textarea>";
                    echo "</div>";
                    $number++;
                }
                ?>
                <button type="submit" class="next-btn">Kirim Jawaban</button>
            </div>
        </form>
    </div>

    <script>
        // Fungsi untuk menampilkan bagian berikutnya dari soal
        function showNextSection(nextSectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            const nextSection = document.getElementById(nextSectionId);
            nextSection.classList.add('active');
        }
    </script>
</body>

</html>
