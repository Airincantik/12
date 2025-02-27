<?php
  // Mulai session
include 'config.php';  // Pastikan sudah ada koneksi PDO dan session aktif

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User tidak terdaftar.']);
    exit;
}

$userId = $_SESSION['user_id'];  // Ambil user_id dari session

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ambil data jawaban dari query string
    if (isset($_GET['answers']) && !empty($_GET['answers'])) {
        $answers = $_GET['answers'];  // Data jawaban diterima dalam array 'answers'
        $attemptNumber = isset($_SESSION['attempt_number']) ? $_SESSION['attempt_number'] + 1 : 1;

        foreach ($answers as $questionId => $answer) {
            // Cek apakah jawaban sudah ada di database
            $sql = "SELECT * FROM user_answers WHERE user_id = :user_id AND question_id = :question_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':question_id', $questionId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Jika sudah ada, update jawaban
                $sql = "UPDATE user_answers SET answer = :answer, attempt_number = :attempt_number WHERE user_id = :user_id AND question_id = :question_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':answer', $answer);
                $stmt->bindParam(':attempt_number', $attemptNumber);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':question_id', $questionId);
                $stmt->execute();
            } else {
                // Jika belum ada, simpan jawaban baru
                $sql = "INSERT INTO user_answers (user_id, question_id, answer, attempt_number) VALUES (:user_id, :question_id, :answer, :attempt_number)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':question_id', $questionId);
                $stmt->bindParam(':answer', $answer);
                $stmt->bindParam(':attempt_number', $attemptNumber);
                $stmt->execute();
            }
        }

        // Kirim response JSON ke client
        echo json_encode(['success' => true, 'message' => 'Jawaban berhasil disimpan!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Jawaban tidak ditemukan.']);
    }
}
?>
