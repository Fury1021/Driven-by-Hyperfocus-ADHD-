<?php
require_once '../dbconnection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Check if the user has submitted the survey this week
$current_date = new DateTime();
$start_of_week = clone $current_date; 
$start_of_week->modify('monday this week'); // Start of the current week

// Query to check if the user has submitted the survey in the current week
$check_query = "SELECT * FROM survey_results WHERE user_id = ? AND submission_date >= ?";
$stmt_check = $conn->prepare($check_query);
$stmt_check->bind_param("is", $user_id, $start_of_week->format('Y-m-d'));
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result->num_rows > 0) {
    // User has already submitted the survey this week
    echo "<script>alert('You have already completed the survey this week. Please try again next week.'); window.location.href = 'screening_questionnaire.php';</script>";
    exit(); // Stop further execution if the survey was submitted this week
}

// Initialize scores for Part A and Part B
$part_a_score = 0;
$part_b_score = 0;

// Calculate Part A Score (Questions 1 to 6)
for ($i = 1; $i <= 6; $i++) {
    if (isset($_POST["q$i"])) {
        $score = (int)$_POST["q$i"];
        // Scoring logic for Part A
        if ($i <= 3 || $i == 9 || $i == 12 || $i == 16 || $i == 18) {
            // Items 1-3, 9, 12, 16, 18 count 1 point for Sometimes, Often, Very Often
            $part_a_score += $score == 1 ? 1 : 0; // 0 for Never, Rarely; 1 for Sometimes, Often, Very Often
        } else {
            // Other items count 1 point for Often, Very Often
            $part_a_score += $score > 0 ? 1 : 0; // 0 for Never, Rarely, Sometimes; 1 for Often, Very Often
        }
    }
}

// Calculate Part B Score (Questions 7 to 18)
for ($i = 7; $i <= 18; $i++) {
    if (isset($_POST["q$i"])) {
        $score = (int)$_POST["q$i"];
        // Part B scoring logic is the same as above (items 7-18 follow the same rules)
        $part_b_score += $score > 0 ? 1 : 0; // 0 for Never, Rarely, Sometimes; 1 for Often, Very Often
    }
}

// Total score is the sum of Part A and Part B
$total_score = $part_a_score + $part_b_score;

// Prepare and execute statement to store results
$stmt = $conn->prepare("INSERT INTO survey_results (user_id, part_a_score, part_b_score, total_score, submission_date) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiii", $user_id, $part_a_score, $part_b_score, $total_score);
$stmt->execute();

// Calculate percentile (if needed, you might need to implement this based on total_score)
// Save additional logic for percentile calculation if necessary

$stmt->close();
$conn->close();

// Redirect or display a success message
header("Location: survey_success.php");
exit();
