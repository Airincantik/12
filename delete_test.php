<?php
require_once '../config.php'; // Ensure the database connection is correct

// Check if 'id' is passed via the URL
if (isset($_GET['id'])) {
    $test_id = $_GET['id']; // Get the test ID from the URL parameter

    // Prepare the DELETE query
    $query = "DELETE FROM tests WHERE test_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    // Bind the parameter (test ID) and execute the query
    $stmt->bind_param('i', $test_id);
    $stmt->execute();

    // Check if a row was deleted
    if ($stmt->affected_rows > 0) {
        // Redirect to view_tests.php with a success message
        header("Location: view_tests.php?message=Test+successfully+deleted.");
        exit();
    } else {
        // If deletion failed, show an error message
        echo "Failed to delete test.";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "No test specified."; // Show this message if no 'id' parameter was provided
}

// Close the database connection
$conn->close();
?>