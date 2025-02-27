<?php
require_once '../config.php';

// Fungsi untuk mendapatkan data progres pengguna (siswa)
function getStudentProgress($test_id)
{
    global $conn;
    $query = "
    SELECT u.id, u.username, q.question_id, q.question_text
    FROM users u
    LEFT JOIN exam_progress ep ON u.id = ep.user_id
    LEFT JOIN questions q ON q.question_id = ep.question_id
    WHERE ep.test_id = ?
    GROUP BY u.id
    ORDER BY ep.answered_at DESC
";


    // Persiapkan query
    $stmt = $conn->prepare($query);

    // Cek apakah query berhasil dipersiapkan
    if ($stmt === false) {
        die('Error preparing query: ' . $conn->error);
    }

    // Bind parameter dan eksekusi query
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ambil hasil dan struktur data progres
    $user_progress = [];
    while ($row = $result->fetch_assoc()) {
        $user_progress[$row['user_id']]['username'] = $row['username'];
        $user_progress[$row['user_id']]['questions'][] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
        ];
    }
    return $user_progress;
}

// Ambil ID test dari URL
$test_id = isset($_GET['test_id']) ? (int) $_GET['test_id'] : 0;

// Ambil progres pengguna berdasarkan test_id
$progress_data = [];
if ($test_id > 0) {
    $progress_data = getStudentProgress($test_id);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Progres Pengguna - Pengawas</title>
    <link rel="stylesheet" href="../cs/css.css">
</head>

<body>
    <header>
        <img src="../soal/logo.png" align="left" alt="logo " width="100" height="100">
        <h1>Monitor Progres Pengguna</h1>
        <a href="index.php">Kembali ke Dashboard</a>
    </header>

    <main>
        <section id="monitor-progress">
            <h2>Progres Ujian</h2>

            <form method="GET" action="monitor_progress.php">
                <label for="test_id">Pilih Tes:</label>
                <select name="test_id" id="test_id" required>
                    <option value="">Pilih Tes</option>
                    <?php
                    // Ambil data tes dari database
                    $query = "SELECT * FROM tests";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($row['test_id'] == $test_id) ? "selected" : "";
                        echo "<option value='{$row['test_id']}' $selected>{$row['test_name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit">Tampilkan Progres</button>
            </form>

            <?php if ($test_id > 0): ?>
                <h3>Daftar Pengguna</h3>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Soal yang Dikerjakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($progress_data)) {
                            foreach ($progress_data as $user_id => $data) {
                                echo "<tr>";
                                echo "<td>{$data['username']}</td>";
                                echo "<td>";
                                foreach ($data['questions'] as $question) {
                                    echo "<p>{$question['question_text']} (Soal ID: {$question['question_id']})</p>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>Tidak ada data progres untuk tes ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>