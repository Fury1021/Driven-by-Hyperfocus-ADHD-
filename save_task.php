<?php
session_start();
require_once '../dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve task from POST data
if (isset($_POST['task']) && !empty($_POST['task'])) {
    $task = trim($_POST['task']); // Sanitize input to prevent empty tasks
    
    // Insert task into database
    $sql = "INSERT INTO productivity (task, status, user_id) VALUES (?, 'undone', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $task, $user_id);

    if ($stmt->execute()) {
        header("Location: productivity_plan.php"); // Redirect to main page
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Error: Task is empty or invalid.";
}
?>
