<?php
session_start();

// Database Connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'system';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}


// Admin session timeout (e.g., 30 minutes)
function checkAdminSessionTimeout()
{
    if (isset($_SESSION['admin_last_activity'])) {
        $inactive_time = 1800; // 30 minutes in seconds
        if (time() - $_SESSION['admin_last_activity'] > $inactive_time) {
            session_unset();
            session_destroy();
            header("Location: admin/login.php?timeout=true");
            exit();
        }
    }
    $_SESSION['admin_last_activity'] = time(); // Reset the timer
}
?>