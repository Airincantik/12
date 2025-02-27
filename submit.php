<?php
require_once 'config.php';

// Ensure test_name and mapel are set using GET
if (!isset($_GET['test_name']) || !isset($_GET['mapel'])) {
    die('Parameter test_name or mapel not found!');
}

$test_name = htmlspecialchars($_GET['test_name']);
$mapel = htmlspecialchars($_GET['mapel']);

// Get the answers from the GET method
$answers = isset($_GET['answer']) ? $_GET['answer'] : [];

// Get test information based on test_name and mapel
$sql = "SELECT * FROM tests WHERE test_name = ? AND mapel = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $test_name, $mapel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Test not found!');
}

$test = $result->fetch_assoc();
$test_id = $test['test_id']; // Get test_id after fetching the test

// Get all questions for the test based on mapel and test_name
$sql_questions = "SELECT * FROM trial_questions WHERE mapel = ? AND test_name = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param('ss', $mapel, $test_name);
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
        // For essay, no correct answer comparison, just an explanation
        $feedback_essay[] = [
            'question' => $question['question_text'],
            'user_answer' => safe_htmlspecialchars($user_answer),
            'explanation' => 'This question will be manually graded.',
            'class' => 'essay'
        ];
        $correct_answers_essay++; // assuming an essay is pending for grading, not scored here
    }
}

// Calculate scores for each type of question
$score_multiple_choice = ($total_multiple_choice > 0) ? ($correct_answers_multiple_choice / $total_multiple_choice) * 100 : 0;
$score_checkbox = ($total_checkbox > 0) ? ($correct_answers_checkbox / $total_checkbox) * 100 : 0;
$score_essay = $correct_answers_essay; // Essay questions require manual grading, so no score calculation here

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tes <?php echo htmlspecialchars($mapel); ?></title>
    <style>
        /* Styling for feedback */
        .correct {
            color: green;
        }

        .incorrect {
            color: red;
        }

        .essay {
            color: orange;
        }

        .question-feedback {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .question-feedback.correct {
            background-color: #e7f7e7;
        }

        .question-feedback.incorrect {
            background-color: #f8d7da;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }

        .btn-info:hover {
            background-color: #138496;
        }
    </style>
</head>

<body>

    <h1>Hasil Tes Matapelajaran <?php echo htmlspecialchars($mapel); ?> (Test : <?php echo $test_name; ?>)</h1>

    <!-- Multiple Choice Results -->
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

    <!-- Checkbox Results -->
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

    <!-- Essay Results -->
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

    <div class="logout-container">
        <button
            onclick="window.location.href='question.php?test_name=<?php echo urlencode($test_name); ?>&mapel=<?php echo urlencode($mapel); ?>';"
            class="btn btn-info">Lanjut test</button>
    </div>
</body>

</html>
