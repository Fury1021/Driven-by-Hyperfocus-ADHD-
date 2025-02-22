<?php
// Include database connection
require_once '../dbconnection.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_ids = $_POST['task_ids'];
    $statuses = $_POST['statuses'];

    // Check if task_ids and statuses are set and not empty
    if (!empty($task_ids) && !empty($statuses)) {
        // Loop through each selected task
        foreach ($task_ids as $task_id) {
            // Update each task's status in the database
            $status = $statuses[$task_id];
            $sql = "UPDATE productivity SET status='$status' WHERE id='$task_id'";

            if ($conn->query($sql) !== TRUE) {
                echo "Error updating task ID $task_id: " . $conn->error;
            }
        }
        echo "Selected task statuses updated successfully!";
    } else {
        echo "No tasks selected or no statuses provided.";
    }

    // Redirect back to the view tasks page
    header('Location: view_productivity.php');
    exit;
}

$conn->close();
?>
