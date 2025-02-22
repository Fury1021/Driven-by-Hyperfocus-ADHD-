<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}
require_once '../dbconnection.php';

$date = $_POST['date'];
$user_id = $_SESSION['user_id'];

// Construct start_time from dropdown inputs
$start_hour = $_POST['start_hour'];
$start_minute = $_POST['start_minute'];
$start_am_pm = $_POST['start_am_pm'];
$start_time = "$start_hour:$start_minute $start_am_pm";

// Construct end_time from dropdown inputs
$end_hour = $_POST['end_hour'];
$end_minute = $_POST['end_minute'];
$end_am_pm = $_POST['end_am_pm'];
$end_time = "$end_hour:$end_minute $end_am_pm";

// Convert start_time and end_time to 24-hour format
$start_time_24 = date("H:i", strtotime($start_time));
$end_time_24 = date("H:i", strtotime($end_time));

// Check if a record already exists for the given date
$checkSql = "SELECT COUNT(*) AS record_count FROM sleeptracker WHERE date = '$date' AND user_id = $user_id";
$checkResult = $conn->query($checkSql);
$checkRow = $checkResult->fetch_assoc();

if ($checkRow['record_count'] > 0) {
    // Record already exists
    echo "<script>alert('A record has already been added for today. See you tomorrow!'); window.location.href='sleep_tracker.php';</script>";
} else {
    // Calculate sleep_hours, sleep_debt, and overslept
    $start_time_obj = new DateTime("$date $start_time_24");
    $end_time_obj = new DateTime("$date $end_time_24");

    // Handle cases where the sleep period crosses midnight
    if ($end_time_obj < $start_time_obj) {
        $end_time_obj->modify('+1 day');
    }

    $interval = $start_time_obj->diff($end_time_obj);
    $sleep_hours = $interval->h + $interval->i / 60;

    // Validate minimum sleep hours
    if ($sleep_hours < 0.5) { // 30 minutes = 0.5 hours
        echo "<script>alert('Please input valid sleep details. Sleep duration must be at least 30 minutes.'); window.location.href='sleep_tracker.php';</script>";
        exit;
    }

    // Assuming 8 hours is the ideal sleep time
    $ideal_sleep_hours = 8;
    $sleep_debt = $ideal_sleep_hours - $sleep_hours;
    $overslept = $sleep_hours > $ideal_sleep_hours ? $sleep_hours - $ideal_sleep_hours : 0;
    $sleep_debt = $sleep_debt < 0 ? 0 : $sleep_debt;

    // Format DateTime objects as strings
    $start_time_formatted = $start_time_obj->format('Y-m-d H:i:s');
    $end_time_formatted = $end_time_obj->format('Y-m-d H:i:s');

    $insertSql = "INSERT INTO sleeptracker (user_id, date, start_time, end_time, sleep_hours, sleep_debt, overslept) VALUES ($user_id, '$date', '$start_time_formatted', '$end_time_formatted', $sleep_hours, $sleep_debt, $overslept)";
    if ($conn->query($insertSql) === TRUE) {
        echo "<script>alert('Sleep record saved successfully!'); window.location.href='sleep_tracker.php';</script>";
    } else {
        echo "<script>alert('Error saving record: " . $conn->error . "'); window.location.href='sleep_tracker.php';</script>";
    }
}
$conn->close();
?>
