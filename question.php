<?php
// Start the session to access session variables

// Memulai sesi dan koneksi database
require_once 'config.php'; // Koneksi database

// Memastikan parameter test_id dan mapel ada di URL
if (!isset($_GET['test_name']) || !isset($_GET['mapel'])) {
    die('Parameter test_id atau mapel tidak ditemukan! Harap pastikan URL lengkap dengan parameter test_id dan mapel.');
}

// Ambil test_id dan mapel dari URL
$test_id = intval($_GET['test_name']);  // Pastikan test_id adalah integer
$mapel = htmlspecialchars($_GET['mapel']); // Menghindari XSS

// Ambil informasi tes berdasarkan test_id
$sql = "SELECT * FROM tests WHERE test_name = ?";
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

// Ambil test_name dari query parameter atau variabel yang sesuai
$test_name = isset($_GET['mapel']) ? $_GET['test_name'] : '';

// Ambil panduan berdasarkan test_name dari tabel 'access_codes'
$sql_guideline = "SELECT guide FROM access_codes WHERE test_name = ?";
$stmt_guideline = $conn->prepare($sql_guideline);
if ($stmt_guideline === false) {
    die('Error preparing statement (access_codes): ' . $conn->error);
}
$stmt_guideline->bind_param('s', $test_name);  // Menggunakan test_name sebagai parameter
$stmt_guideline->execute();
$guideline_result = $stmt_guideline->get_result();


$guideline_text = '';
if ($guideline_result->num_rows > 0) {
    $guideline_data = $guideline_result->fetch_assoc();
    $guideline_text = nl2br(htmlspecialchars($guideline_data['guide'])); // Format teks agar baris baru tetap terlihat
} else {
    $guideline_text = 'Panduan umum tidak tersedia untuk tes ini.';
}





// Ambil semua soal berdasarkan test_id
$sql_questions = "SELECT * FROM questions WHERE test_name = ?";
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

if (!isset($_SESSION['username'])) {
    // Jika belum login, tampilkan alert menggunakan JavaScript dan arahkan ke halaman login
    echo "<script>
            alert('Anda belum login! Silakan login terlebih dahulu.');
            window.location.href = 'login.php'; // Ganti dengan halaman login kamu
          </script>";
    exit(); // Menghentikan eksekusi script PHP lebih lanjut
}


$username = $_SESSION['username']; // Mengambil username dari session

// Fungsi untuk menyimpan tracking ke database
function saveTracking($username, $test_id, $mapel, $step, $time_spent)
{
    global $conn;

    // Pastikan parameter lengkap
    if (empty($username) || empty($test_id) || empty($mapel) || empty($step)) {
        die('Error: Parameter tidak lengkap untuk menyimpan tracking!');
    }

    // Query untuk menyimpan data tracking
    $sql = "INSERT INTO user_tracking (username, test_id, mapel, step, time_spent) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Error preparing statement (tracking): ' . $conn->error);
    }

    // Mengikat parameter ke query
    $stmt->bind_param('sisss', $username, $test_id, $mapel, $step, $time_spent);

    // Eksekusi query
    if (!$stmt->execute()) {
        die("Error executing tracking query: " . $stmt->error);
    }

    return true;
}

// Menyimpan tracking saat melanjutkan ke bagian soal
if (isset($_POST['step'])) {
    $step = $_POST['step'];
    // Hitung waktu yang telah dihabiskan
    $time_spent = time() - $_SESSION['start_time'];
    saveTracking($username, $test_id, $mapel, $step, $time_spent);
}

// Ambil URL referer
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Parsing URL referer untuk mengambil query string
$parsed_url = parse_url($referer);
parse_str($parsed_url['query'], $query_params);

// Ambil nilai test_name dan mapel dari query string referer
$test_name_from_referer = isset($query_params['test_name']) ? $query_params['test_name'] : '';
$mapel_from_referer = isset($query_params['mapel']) ? $query_params['mapel'] : '';




// Memastikan parameter 'test_name' dan 'mapel' ada di URL
if (isset($_GET['test_name']) && isset($_GET['mapel'])) {
    // Ambil nilai dari parameter URL
    $test_name = $_GET['test_name'];
    $mapel = $_GET['mapel'];

    // Query untuk mendapatkan durasi total tes
    $query = "SELECT duration FROM tests WHERE test_name = '$test_name' AND mapel = '$mapel'";

    // Eksekusi query
    $result = mysqli_query($conn, $query);

    // Cek apakah ada data yang ditemukan
    if ($row = mysqli_fetch_assoc($result)) {
        $total_duration = $row['duration']; // Durasi tes dalam menit
    } else {
        // Default jika tidak ditemukan data
        $total_duration = 0;
    }
} else {
    // Jika test_name atau mapel tidak ada di URL
    $total_duration = 0;
    $mapel = '';
    $test_name = '';
}








?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes <?php echo htmlspecialchars($mapel); ?></title>
    <style>
        /* Global Style */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #444;
        }

        /* Styling for Video */
        #webcam {
            width: 100%;
            max-width: 640px;
            border-radius: 8px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
        }

        /* Timer Style */
        #timer {
            font-size: 20px;
            font-weight: bold;
            color: #d9534f;
            background-color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }

        /* Styling for Guidelines */
        .guidelines {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }

        .guidelines h2 {
            font-size: 18px;
            color: #5bc0de;
        }

        .guidelines p {
            line-height: 1.6;
        }

        /* Styling for Questions */
        .question {
            margin-bottom: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .question h3 {
            font-size: 16px;
        }

        .question .options {
            margin-top: 10px;
        }

        .question .options label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .question img.question-image {
            margin-top: 10px;
            width: 100%;
            max-width: 200px;
            display: block;
        }

        /* Section Transition */
        .section {
            display: none;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .section.active {
            display: block;
            opacity: 1;
        }

        /* Buttons */
        button {
            background-color: #5bc0de;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #31b0d5;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Button Style for Navigation */
        .next-btn {
            margin-top: 20px;
            font-size: 16px;
            padding: 12px;
            background-color: #5bc0de;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .next-btn:hover {
            background-color: #31b0d5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .question img {
                width: 100%;
                max-width: none;
            }

            #webcam {
                width: 100%;
            }

            #timer {
                font-size: 18px;
            }
        }

        /* Style untuk video */
        #webcam {
            width: 5%;
            max-width: 640px;
            height: auto;
            border: 1px solid black;
            margin-bottom: 20px;
        }

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

        /* Style untuk panduan umum */
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

        /* CSS Transitions untuk perubahan antar bagian soal */
        .section {
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .section.active {
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Tes Mapel: <?php echo htmlspecialchars($test['mapel']); ?></h1>


        <!-- Panduan Umum Mapel -->
        <div class="guidelines">
            <h2>Panduan Umum untuk Tes: <?php echo htmlspecialchars($test_name); ?></h2>
            <p><?php echo $guideline_text; ?></p> <!-- Tampilkan panduan -->
        </div>




        <p>Sisa Waktu: <span id="timer"></span></p>

        <!-- Webcam Container -->
        <div>
            <h2>Live Webcam</h2>
            <video id="webcam" autoplay></video>
        </div>

        <form id="testForm" action="balik.php" method="GET">
            <!-- Kirim hanya test_name dan mapel ke balik.php -->
            <input type="hidden" name="test_name" value="<?php echo htmlspecialchars($test_name_from_referer); ?>" />
            <input type="hidden" name="mapel" value="<?php echo htmlspecialchars($mapel_from_referer); ?>" />
            <!-- Display Multiple Choice Questions -->
            <div class="section" id="multiple_choice_section">
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
                <button type="button" class="next-btn" onclick="showNextSection('checkbox_section')">Lanjut ke Soal
                    Checkbox</button>
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

                    // Periksa apakah ada opsi checkbox
                    if (count($checkbox_options) > 0) {
                        echo "<div class='options'>";
                        foreach ($checkbox_options as $key => $option) {
                            // Tampilkan setiap opsi checkbox
                            echo "<label><input type='checkbox' name='answer[{$question['question_id']}][]' value='" . ($key + 1) . "'> " . htmlspecialchars($option) . "</label><br>";
                        }
                        echo "</div>";
                    } else {
                        echo "<p>Opsi tidak tersedia untuk soal ini.</p>";
                    }

                    // Jika ada gambar soal
                    if (!empty($question['question_image'])) {
                        echo "<img src='./uploads/" . htmlspecialchars($question['question_image']) . "' class='question-image' alt='Gambar Soal'>";
                    }

                    echo "</div>";
                    $number++;
                }
                ?>

                <button type="button" class="next-btn" onclick="showNextSection('essay_section')">Lanjut ke Soal
                    Esai</button>
            </div>

            <!-- Display Essay Questions -->
            <div class="section" id="essay_section">
                <h2>Soal Esai</h2>
                <?php
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
                ?>
                <button type="button" class="next-btn" onclick="submitTest()">Kirim Jawaban</button>
            </div>
        </form>

        <script>
            // Durasi dari PHP (dalam menit)
            var totalDuration = <?php echo $total_duration; ?>; // Durasi dalam menit
            var timeRemaining = totalDuration * 60; // Menghitung waktu dalam detik

            // Suara alarm
            let alarmSound = new Audio('timer.mp3'); // Ganti dengan file suara yang sesuai

            function updateTimer() {
                var minutes = Math.floor(timeRemaining / 60);  // Menghitung menit
                var seconds = timeRemaining % 60;  // Menghitung detik
                document.getElementById('timer').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                timeRemaining--;

                // Ketika waktu hampir habis
                if (timeRemaining === 60) {  // Jika waktu tersisa 60 detik (1 menit)
                    alarmSound.play();  // Memutar suara alarm
                }

                // Jika waktu habis, lakukan redirect ke balik.php
                if (timeRemaining <= 0) {
                    submitTest(); // Mengirimkan tes dan melakukan redirect
                }
            }

            // Fungsi untuk submit tes ketika waktu habis
            function submitTest() {

                window.location.href = "http://localhost/nm/balik.php?test_name=" + "<?php echo $test_name; ?>" + "&mapel=" + "<?php echo $mapel; ?>";  // Redirect ke balik.php
            }

            // Memulai timer
            setInterval(updateTimer, 1000);  // Update timer setiap detik

            // Fungsi untuk menampilkan bagian soal berikutnya
            function showNextSection(sectionId) {
                document.querySelectorAll('.section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(sectionId).style.display = 'block';
            }

            // Fungsi untuk menampilkan section berikutnya
            function showNextSection(sectionId) {
                // Sembunyikan semua section
                document.querySelectorAll('.section').forEach(function (section) {
                    section.classList.remove('active');
                });

                // Tampilkan section berikutnya
                document.getElementById(sectionId).classList.add('active');
                currentSection = sectionId; // Menandai section saat ini
            }

            // Tampilkan soal pertama
            document.getElementById('multiple_choice_section').classList.add('active');

            // Fungsi untuk memulai webcam dan capture gambar setiap 2 menit
            async function startWebcam() {
                const videoElement = document.getElementById('webcam');
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    videoElement.srcObject = stream;

                    // Setup canvas untuk capture gambar dari video
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Fungsi untuk capture gambar
                    function captureImage() {
                        // Set ukuran canvas sama dengan ukuran video
                        canvas.width = videoElement.videoWidth;
                        canvas.height = videoElement.videoHeight;

                        // Gambar frame video ke canvas
                        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

                        // Convert canvas menjadi data URL (gambar base64)
                        const imageData = canvas.toDataURL('image/png');

                        // Kirim gambar ke server menggunakan AJAX
                        sendImageToServer(imageData);
                    }

                    // Fungsi untuk mengirim gambar ke server menggunakan AJAX
                    function sendImageToServer(imageData) {
                        const formData = new FormData();
                        formData.append('image', imageData);
                        formData.append('test_id', <?php echo $test_id; ?>); // Menambahkan test_id jika perlu

                        // Menggunakan AJAX untuk mengirim gambar ke server
                        fetch('capture_upload.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log("Gambar berhasil di-upload!");
                                } else {
                                    console.error("Gambar gagal di-upload.");
                                }
                            })
                            .catch(error => {
                                console.error("Terjadi kesalahan saat mengirim gambar:", error);
                            });
                    }

                    // Set interval untuk capture gambar setiap 2 menit (120 detik)
                    setInterval(captureImage, 120 * 1000); // 120 detik
                } catch (error) {
                    console.error("Terjadi kesalahan saat mengakses webcam:", error);
                }
            }

            startWebcam();

            function submitAnswers() {
        const formData = new FormData(document.getElementById('testForm'));
        
        // Kirim data jawaban ke server menggunakan AJAX (GET)
        fetch('svanswer.php?' + new URLSearchParams(formData).toString(), {
            method: 'GET',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Jawaban berhasil disimpan!');
            } else {
                alert('Gagal menyimpan jawaban: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Terjadi kesalahan:', error);
        });
    }

        </script>
    </div>
</body>

</html>