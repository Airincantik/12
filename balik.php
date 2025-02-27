<?php
require_once 'config.php';

// Ensure test_name and mapel are set in the GET request
if (!isset($_GET['test_name']) || !isset($_GET['mapel'])) {
    die('Parameter test_name or mapel not found!');
}

$test_name = htmlspecialchars($_GET['test_name']);
$mapel = htmlspecialchars($_GET['mapel']);

// Check if 'answer' is set in GET, if not initialize as an empty array
$answers = isset($_GET['answer']) ? $_GET['answer'] : [];

// Debugging: Tampilkan jawaban yang diterima
echo "<pre>Jawaban yang diterima: ";
print_r($answers);
echo "</pre>";

// Get test information based on test_name
$sql = "SELECT * FROM tests WHERE test_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $test_name);  // Use 's' because test_name is a string
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Test not found!');
}

$test = $result->fetch_assoc();

// Get all questions for the test
$sql_questions = "SELECT * FROM questions WHERE test_name = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param('s', $test_name);  // Use 's' for string test_name
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// Initialize variables for score and feedback
$correct_answers_multiple_choice = 0;
$correct_answers_checkbox = 0;
$total_multiple_choice = 0;
$total_checkbox = 0;
$feedback_multiple_choice = [];
$feedback_checkbox = [];
$feedback_essay = [];
$correct_answers_essay = 0;

// Initialize variables for DISC results
$isDiscTest = ($test_name == 'disc'); // Cek apakah tes ini adalah DISC
$discResults = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0]; // Skor DISC
$discMapping = ['A' => 'D', 'B' => 'I', 'C' => 'S', 'D' => 'C']; // Mapping jawaban ke DISC

// Helper function to safely apply htmlspecialchars to answers
function safe_htmlspecialchars($data)
{
    if (is_array($data)) {
        return htmlspecialchars(implode(', ', $data));
    }
    return htmlspecialchars($data);
}

// Process each question and calculate feedback
while ($question = $questions_result->fetch_assoc()) {
    $question_id = $question['question_id'];
    $question_type = $question['question_type'];

    // Get user's answer for the question
    $user_answer = isset($answers[$question_id]) ? $answers[$question_id] : null;

    // Debugging: Tampilkan pertanyaan dan jawaban
    echo "<pre>Pertanyaan ID: $question_id, Jawaban: $user_answer</pre>";

    // Handle DISC questions (only multiple choice)
    if ($isDiscTest && $question_type == 'multiple_choice') {
        if ($user_answer && isset($discMapping[$user_answer])) {
            $discType = $discMapping[$user_answer];
            $discResults[$discType]++;
        }
    }

    // Handle non-DISC questions
    elseif (!$isDiscTest) {
        // Handle multiple choice question
        if ($question_type == 'multiple_choice') {
            $correct_option = strtolower($question['correct_option']);
            $correct_answers_array = explode(',', $correct_option);

            if ($user_answer && $user_answer == $correct_answers_array[0]) {
                $correct_answers_multiple_choice++;
                $feedback_multiple_choice[] = [
                    'question' => $question['question_text'],
                    'user_answer' => safe_htmlspecialchars($user_answer),
                    'correct_option' => safe_htmlspecialchars($correct_answers_array),
                    'explanation' => 'Correct answer.',
                    'class' => 'correct'
                ];
            } else {
                $feedback_multiple_choice[] = [
                    'question' => $question['question_text'],
                    'user_answer' => safe_htmlspecialchars($user_answer),
                    'correct_option' => safe_htmlspecialchars($correct_answers_array),
                    'explanation' => 'Incorrect answer. The correct answer is ' . safe_htmlspecialchars($correct_answers_array[0]),
                    'class' => 'incorrect'
                ];
            }
            $total_multiple_choice++;
        }

        // Handle checkbox question
        elseif ($question_type == 'checkbox') {
            $correct_checkbox_answers = $question['checkbox_answers'];
            $correct_checkbox_answers_array = explode(',', $correct_checkbox_answers);

            if (is_array($user_answer)) {
                sort($user_answer);
            } else {
                $user_answer = explode(',', $user_answer);
            }
            sort($user_answer);

            if ($user_answer == $correct_checkbox_answers_array) {
                $correct_answers_checkbox++;
                $feedback_checkbox[] = [
                    'question' => $question['question_text'],
                    'user_answer' => safe_htmlspecialchars($user_answer),
                    'correct_option' => safe_htmlspecialchars($correct_checkbox_answers_array),
                    'explanation' => 'Correct answers.',
                    'class' => 'correct'
                ];
            } else {
                $feedback_checkbox[] = [
                    'question' => $question['question_text'],
                    'user_answer' => safe_htmlspecialchars($user_answer),
                    'correct_option' => safe_htmlspecialchars($correct_checkbox_answers_array),
                    'explanation' => 'Incorrect answer. The correct answers are ' . safe_htmlspecialchars($correct_checkbox_answers_array),
                    'class' => 'incorrect'
                ];
            }
            $total_checkbox++;
        }

        // Handle essay question
        elseif ($question_type == 'essay') {
            $feedback_essay[] = [
                'question' => $question['question_text'],
                'user_answer' => safe_htmlspecialchars($user_answer),
                'explanation' => 'This question will be manually graded.',
                'class' => 'essay'
            ];
            $correct_answers_essay++; // assuming an essay is pending for grading, not scored here
        }
    }
}

// Debugging: Tampilkan hasil DISC
echo "<pre>Hasil DISC: ";
print_r($discResults);
echo "</pre>";

// Calculate scores for each type of question
$score_multiple_choice = ($total_multiple_choice > 0) ? ($correct_answers_multiple_choice / $total_multiple_choice) * 100 : 0;
$score_checkbox = ($total_checkbox > 0) ? ($correct_answers_checkbox / $total_checkbox) * 100 : 0;
$score_essay = $correct_answers_essay; // Essay questions require manual grading, so no score calculation here

// Determine DISC personality type
if ($isDiscTest) {
    $personalityType = array_keys($discResults, max($discResults))[0];
    $personalityDetail = [
        'D' => 'Dominance: Tegas, berorientasi pada hasil, dan suka tantangan.',
        'I' => 'Influence: Ramah, bersemangat, dan suka bersosialisasi.',
        'S' => 'Steadiness: Sabar, harmonis, dan dapat diandalkan.',
        'C' => 'Conscientiousness: Teliti, sistematis, dan berorientasi pada detail.'
    ][$personalityType];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tes <?php echo htmlspecialchars($mapel); ?></title>
    <style>
        /* Styling for feedback */
        .correct { color: green; }
        .incorrect { color: red; }
        .essay { color: orange; }
        .question-feedback { margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; }
        .question-feedback.correct { background-color: #e7f7e7; }
        .question-feedback.incorrect { background-color: #f8d7da; }
        .btn-logout { display: inline-block; background-color: #f44336; color: white; padding: 10px 20px; text-align: center; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn-logout:hover { background-color: #e53935; }
    </style>
</head>
<body>

    <h1>Hasil Tes Mata Pelajaran <?php echo htmlspecialchars($mapel); ?> (Test: <?php echo $test_name; ?>)</h1>

    <!-- Display DISC results if test is DISC -->
    <?php if ($isDiscTest): ?>
        <div class="result">
            <h2>Hasil Tes DISC:</h2>
            <p><strong>Tipe Kepribadian Anda:</strong> <?php echo $personalityType; ?></p>
            <p><strong>Deskripsi:</strong> <?php echo $personalityDetail; ?></p>
            <p><strong>Skor Detail:</strong></p>
            <ul>
                <li>Dominance (D): <?php echo $discResults['D']; ?></li>
                <li>Influence (I): <?php echo $discResults['I']; ?></li>
                <li>Steadiness (S): <?php echo $discResults['S']; ?></li>
                <li>Conscientiousness (C): <?php echo $discResults['C']; ?></li>
            </ul>
        </div>
    <?php else: ?>
        <!-- Display non-DISC results -->
        <div class="result">
            <h2>Hasil Soal Pilihan Ganda:</h2>
            <p>Skor Anda: <?php echo number_format($score_multiple_choice, 2); ?>%</p>
            <p>Total Soal: <?php echo $total_multiple_choice; ?></p>
            <p>Jawaban Benar: <?php echo $correct_answers_multiple_choice; ?></p>

            <?php foreach ($feedback_multiple_choice as $item): ?>
                <div class="question-feedback <?php echo $item['class']; ?>">
                    <p><strong>Soal:</strong> <?php echo htmlspecialchars($item['question']); ?></p>
                    <p><strong>Jawaban Anda:</strong> <?php echo htmlspecialchars($item['user_answer']); ?></p>
                    <p><strong>Jawaban Benar:</strong> <?php echo htmlspecialchars($item['correct_option']); ?></p>
                    <p><strong>Penjelasan:</strong> <?php echo htmlspecialchars($item['explanation']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="result">
            <h2>Hasil Soal Checkbox:</h2>
            <p>Skor Anda: <?php echo number_format($score_checkbox, 2); ?>%</p>
            <p>Total Soal: <?php echo $total_checkbox; ?></p>
            <p>Jawaban Benar: <?php echo $correct_answers_checkbox; ?></p>

            <?php foreach ($feedback_checkbox as $item): ?>
                <div class="question-feedback <?php echo $item['class']; ?>">
                    <p><strong>Soal:</strong> <?php echo htmlspecialchars($item['question']); ?></p>
                    <p><strong>Jawaban Anda:</strong> <?php echo htmlspecialchars($item['user_answer']); ?></p>
                    <p><strong>Jawaban Benar:</strong> <?php echo htmlspecialchars($item['correct_option']); ?></p>
                    <p><strong>Penjelasan:</strong> <?php echo htmlspecialchars($item['explanation']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="result">
            <h2>Hasil Soal Esai:</h2>
            <p>Soal-soal esai akan segera dikoreksi.</p>

            <?php foreach ($feedback_essay as $item): ?>
                <div class="question-feedback <?php echo $item['class']; ?>">
                    <p><strong>Soal:</strong> <?php echo htmlspecialchars($item['question']); ?></p>
                    <p><strong>Jawaban Anda:</strong> <?php echo htmlspecialchars($item['user_answer']); ?></p>
                    <p><strong>Penjelasan:</strong> <?php echo htmlspecialchars($item['explanation']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Logout Button -->
    <div class="logout-container">
        <a href="akses.php" class="btn-logout">Logout</a>
    </div>
</body>
</html>
