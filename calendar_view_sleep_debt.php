<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

require_once '../dbconnection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sleep Tracker - Date Details</title>
    <!-- Include CSS stylesheets and scripts as needed -->
</head>
<body>
    <h1>Sleep Tracker - Details for <?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?></h1>
    
    <?php
    include 'dbconnection.php';

    // Retrieve sleep data for the specific date
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $sql = "SELECT * FROM sleeptracker WHERE date = '$date'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $start_time = $row['start_time'];
        $end_time = $row['end_time'];
        $sleep_debt = $row['sleep_debt'];

        echo "<p>Date: $date</p>";
        echo "<p>Sleep Start Time: $start_time</p>";
        echo "<p>Wake Up Time: $end_time</p>";
        echo "<p>Sleep Debt: " . number_format($sleep_debt, 2) . " hours</p>";
    } else {
        echo "<p>No sleep data found for this date.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
