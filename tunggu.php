<?php
session_start();

// Pastikan waktu mulai ada di sesi
if (!isset($_SESSION['start_time'])) {
    die("Schedule not found.");
}

$start_time = $_SESSION['start_time'];
$current_time = time();
$remaining_time = $start_time - $current_time;

if ($remaining_time <= 0) {
    // Waktu ujian sudah dimulai, redirect ke halaman registrasi
    header("Location: regis.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countdown</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7fc;
            text-align: center;
        }

        h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        .countdown {
            font-size: 2em;
            margin: 20px;
            padding: 10px;
            background-color: #ffcc00;
            display: inline-block;
        }
    </style>
</head>

<body>

    <h2>Test is about to begin!</h2>
    <div id="countdown" class="countdown">
        <!-- Countdown timer will be shown here -->
    </div>

    <script>
        // Set the date we're counting down to
        var countdownDate = new Date(<?php echo $start_time * 1000; ?>).getTime();

        // Update the countdown every 1 second
        var x = setInterval(function () {

            // Get current time
            var now = new Date().getTime();

            // Find the time remaining
            var distance = countdownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="countdown"
            document.getElementById("countdown").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";

            // If the countdown is finished, redirect to the registration page
            if (distance < 0) {
                clearInterval(x);
                window.location.href = "registrasi.php";  // Redirect to the registration page
            }
        }, 1000);
    </script>

</body>

</html>
