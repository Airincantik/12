<?php
require_once '../config.php';

// Query untuk mengambil peserta yang aktif dalam 5 menit terakhir
$query_active_participants = "SELECT username, device_name, device_os, public_ip, timestamp
                              FROM participan_device
                              WHERE access_time > NOW() - INTERVAL 5 MINUTE
                              ORDER BY access_time DESC";
$result_active_participants = $conn->query($query_active_participants);

// Cek apakah query berhasil
if (!$result_active_participants) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peserta Aktif</title>

    <style>
        /* Styling untuk tabel */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Styling untuk header */
        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
        }

        /* Styling untuk logo */
        img {
            max-width: 100px;
        }
    </style>
</head>

<body>
    <header>
        <img src="../soal/logo.png" alt="Logo">
        <h1>Peserta Aktif</h1>
        <a href="index.php">Back to Dashboard</a>
    </header>

    <main>
        <h2>Daftar Peserta Aktif dalam 5 Menit Terakhir</h2>

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Browser</th>
                    <th>OS</th>
                    <th>IP Address</th>
                    <th>Waktu Akses</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_active_participants->num_rows > 0): ?>
                    <?php while ($row = $result_active_participants->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['device_name']) ?></td>
                            <td><?= htmlspecialchars($row['device_os']) ?></td>
                            <td><?= htmlspecialchars($row['public_ip']) ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Tidak ada peserta yang aktif dalam 5 menit terakhir.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>