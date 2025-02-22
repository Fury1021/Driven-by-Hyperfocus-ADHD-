<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

// Database connection details
require_once '../dbconnection.php';

$sql = "SELECT url, description FROM images";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sleep Debt Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
</head>
<body class="header-blue">

<header>
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <div class="container">
            <a href="user_dashboard.php"><img src="images/logo3.png" alt="Your Logo"></a>
            <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link active" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="taskmanager.php">Task Manager</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Behaviour Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sleep_tracker.php">Sleep Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="productivity.php">Productivity Planner</a></li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_profile.php">My Profile <i class="fa fa-user-circle"></i></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More Info</a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="adhd_public.php">What is ADHD?</a>
                                    <a class="dropdown-item" href="brain_public.php">The ADHD Brain</a>
                                    <a class="dropdown-item" href="symptoms_public.php">ADHD Symptoms</a>
                                    <a class="dropdown-item" href="children_public.php">ADHD in Children</a>
                                    <a class="dropdown-item" href="adhd_public.php">ADHD in Adults</a>
                                </div>
                    </li>
                </ul>
                <span class="navbar-text">
                    <a href="user_logout.php" class="logout">Log Out</a>
                </span>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="main-content row">
        <div class="col-lg-12">
            <h1 class="mt-5">Sleep Debt Details</h1>

            <?php
            include 'dbconnection.php';

            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

            // Query to get details of sleep debt for the specific date range
            $sql = "SELECT start_time, end_time, sleep_debt, overslept
                    FROM sleeptracker
                    WHERE date BETWEEN '$startDate' AND '$endDate'";
            $result = $conn->query($sql);

            // Initialize totals
            $totalSleepDebt = 0;
            $totalOverslept = 0;
            $totalHours = 0;

            if ($result->num_rows > 0) {
                echo "<h2>Details from {$startDate} to {$endDate}</h2>";
                echo "<table class='table table-bordered'>";
                echo "<thead class='thead-dark'><tr><th>Start Time</th><th>End Time</th><th>Sleep Debt</th><th>Overslept</th></tr></thead>";
                echo "<tbody>";

                while ($row = $result->fetch_assoc()) {
                    $startTime = $row['start_time'];
                    $endTime = $row['end_time'];
                    $sleepDebt = $row['sleep_debt'];
                    $overslept = $row['overslept'];

                    // Accumulate totals
                    $totalSleepDebt += $sleepDebt;
                    $totalOverslept += $overslept;

                    // Display data
                    echo "<tr>";
                    echo "<td>{$startTime}</td>";
                    echo "<td>{$endTime}</td>";
                    echo "<td>" . number_format($sleepDebt, 2) . " hours</td>";
                    echo "<td>" . number_format($overslept, 2) . " hours</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";

                // Display totals
                $totalHours = $totalOverslept + $totalSleepDebt;
                echo "<p><strong>Total Sleep Debt: </strong>" . number_format($totalSleepDebt, 2) . " hours</p>";
                echo "<p><strong>Total Overslept: </strong>" . number_format($totalOverslept, 2) . " hours</p>";
                echo "<p><strong>Total Hours of Sleep: </strong>" . number_format($totalHours, 2) . " hours</p>";

            } else {
                echo "<p>No sleep data found for the selected date range.</p>";
            }

            $conn->close();
            ?>

            <script>
                // Redirect to download page and initiate download after 1 second
                setTimeout(function() {
                    window.location.href = 'download_sleep_tracker.php';
                }, 1000);
            </script>

        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer mt-auto py-3" style="background-color: black; color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-left">
                <h4 style="color: white;">ADHD Society of the Philippines</h4>
                <address style="color: white; margin-top: 10px;">
                    3rd Floor Uniplan Overseas Agency Office, 302 JP Rizal Street corner Diego Silang Street, Project 4, Quezon City, Philippines, 1109<br>
                    <a href="mailto:adhdsociety@yahoo.com" style="color: white;" target="_blank">adhdsociety@yahoo.com</a><br>
                    <a href="https://www.adhdsocph.org/" style="color: white;" target="_blank">www.adhdsocph.org</a>
                </address>
            </div>
            <div class="col-md-6 text-center text-md-right">
                <h4 style="color: white;">Follow Us</h4>
                <div class="social-icons" style="margin-top: 10px;">
                    <a href="#"><i class="fa fa-facebook fa-2x"></i></a>
                    <a href="#"><i class="fa fa-twitter fa-2x"></i></a>
                    <a href="#"><i class="fa fa-linkedin fa-2x"></i></a>
                    <a href="#"><i class="fa fa-instagram fa-2x"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
