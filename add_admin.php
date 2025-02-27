<?php
require_once '../config.php';

$notification = ''; // Variable to display notifications

// Process new admin addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_admin_username = $_POST['username'];
    $new_admin_password = $_POST['password']; // Saving password as plain text

    // Check if username already exists
    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $new_admin_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $notification = "Admin with this username already exists.";
    } else {
        // Insert username and password into admins table
        $insert_query = "INSERT INTO admins (username, password) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('ss', $new_admin_username, $new_admin_password);

        if ($insert_stmt->execute()) {
            $notification = "New admin added successfully.";
        } else {
            $notification = "Error adding new admin.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Admin</title>
    <style>
        /* Reset some basic styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* General body styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* Header Styling */
        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            width: 100%;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
        }

        /* Main content container */
        .container {
            width: 100%;
            max-width: 400px;
            padding-top: 80px;
            padding-bottom: 40px;
            text-align: center;
        }

        /* Form container */
        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Label styling */
        label {
            font-size: 14px;
            font-weight: bold;
            color: #4caf50;
            margin-bottom: 8px;
            display: block;
        }

        /* Input field styling */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.3s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border: 1px solid #4caf50;
        }

        /* Button styling */
        button {
            background-color: #4caf50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Notification styling */
        .notification {
            margin-top: 20px;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            transition: opacity 0.5s ease-in-out;
        }

        .success {
            background-color: #4caf50;
            color: white;
        }

        .error {
            background-color: #f44336;
            color: white;
        }

        /* Back button styling */
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0288d1;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #0277bd;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            header h1 {
                font-size: 1.5rem;
            }

            .container {
                padding: 20px;
            }

            form {
                padding: 20px;
            }

            button {
                padding: 10px;
                font-size: 14px;
            }

            .btn-back {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <header>
        <h1>Add New Admin</h1>
    </header>

    <!-- Main Content Container -->
    <div class="container">

        <!-- Logo -->
        <img src="../soal/logo.png" alt="logo" width="100" height="100">

        <!-- Form -->
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Add Admin</button>
        </form>

        <!-- Notification Message -->
        <?php if (!empty($notification)): ?>
            <div class="notification <?php echo strpos($notification, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $notification; ?>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="index.php" class="btn-back">Back to Dashboard</a>

    </div>

</body>

</html>
