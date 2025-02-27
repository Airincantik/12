<?php
// Koneksi ke database
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $participant_id = $_POST['participant_id'] ?? null;

    // Validasi data
    if ($latitude && $longitude && $participant_id) {
        // Masukkan data lokasi ke database
        $sql = "INSERT INTO participant_locations (participant_id, latitude, longitude, timestamp)
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idd", $participant_id, $latitude, $longitude);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Location saved successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save location."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid location data."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Tracking</title>
    <script src="geolocation.js"></script>
</head>

<body>
    <h1>Track Your Location</h1>
    <button onclick="sendLocation(1)">Send Location</button> <!-- 1 adalah ID peserta -->
</body>

</html>