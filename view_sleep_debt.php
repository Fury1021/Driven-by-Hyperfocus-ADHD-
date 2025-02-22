<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

require_once '../dbconnection.php';

// Function to convert hours to hours and minutes
function convertHoursToHoursAndMinutes($totalHours) {
    $totalMinutes = round($totalHours * 60); // Convert hours to minutes
    $hours = floor($totalMinutes / 60); // Get the whole hours
    $minutes = $totalMinutes % 60; // Get the remaining minutes

    // Create the output string
    if ($hours > 0) {
        return $hours . " hour" . ($hours > 1 ? "s" : "") . ($minutes > 0 ? " and " . $minutes . " minute" . ($minutes > 1 ? "s" : "") : "");
    } else {
        return $minutes . " minute" . ($minutes > 1 ? "s" : "");
    }
}

$sql = "SELECT url, description FROM images";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sleep Debt History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="sleep_tracker.css">
</head>
<body class="header-blue">

<header>
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <div class="container">
            <a href="user_dashboard.php"><img src="images/logo3.png" alt="Your Logo" class="navbar-brand"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navcol-1" aria-controls="navcol-1" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link active" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="taskmanager.php">Task Manager</a></li>
                    <li class="nav-item"><a class="nav-link active" href="screening_questionnaire.php">Self Report</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sleep_tracker.php">Sleep Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="productivity.php">Productivity</a></li>
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
                    <li class="nav-item"><a class="nav-link active" href="user_logout.php">Log out</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="main-content row">
        <div class="col-lg-12">
            <h1 class="mt-5">Sleep Debt History</h1>
            <div id="present" class="mb-5">
                <?php
                include 'dbconnection.php';

                // Get the start and end date for the current week
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));

                // Format dates to month and day
                $formattedStartDate = date('F j', strtotime($startDate));
                $formattedEndDate = date('F j', strtotime($endDate));

                echo "<h2>Current Week</h2>";
                echo "<p>{$formattedStartDate} - {$formattedEndDate}</p>";
                echo "<a href='view_sleep_debt_details.php?start_date={$startDate}&end_date={$endDate}'>
                        <button class='btn btn-primary'>View Details</button>
                      </a>";
                
                // Query to get the current week's total sleep debt and overslept
                $currentWeekSql = "SELECT SUM(sleep_debt) AS total_sleep_debt, SUM(overslept) AS total_overslept
                FROM sleeptracker
                WHERE date BETWEEN '$startDate' AND '$endDate'";
                $currentWeekResult = $conn->query($currentWeekSql);

                if ($currentWeekResult->num_rows > 0) {
                    $currentWeekRow = $currentWeekResult->fetch_assoc();
                    $totalSleepDebt = $currentWeekRow['total_sleep_debt'];
                    $totalOverslept = $currentWeekRow['total_overslept'];
                    $currentWeekSleepDebt = abs($totalSleepDebt - $totalOverslept);

                    $formattedSleepDebt = convertHoursToHoursAndMinutes($currentWeekSleepDebt);
                    echo "<p style='color: white;'><strong>Total Sleep Debt for Current Week:</strong> " . $formattedSleepDebt . "</p>";
                } else {
                    echo "<p style='color: white;'><strong>Total Sleep Debt for Current Week:</strong> No data available</p>";
                }

                echo "</div>";

                $previousWeeksSql = "SELECT MIN(date) AS start_date, MAX(date) AS end_date,
                                            SUM(sleep_debt) AS total_sleep_debt, SUM(overslept) AS total_overslept
                                     FROM sleeptracker 
                                     WHERE date < '$startDate'
                                     GROUP BY YEAR(date), WEEK(date, 1)
                                     ORDER BY YEAR(date) DESC, WEEK(date, 1) DESC";
                $previousWeeksResult = $conn->query($previousWeeksSql);

                if ($previousWeeksResult->num_rows > 0) {
                    echo "<h2>Previous Weeks' History</h2>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead class='thead-dark'><tr><th>Date Range</th><th>Total Sleep Debt of the Week</th></tr></thead>";
                    echo "<tbody>";

                    while ($row = $previousWeeksResult->fetch_assoc()) {
                        $startDate = $row['start_date'];
                        $endDate = $row['end_date'];
                        $totalSleepDebt = $row['total_sleep_debt'];
                        $totalOverslept = $row['total_overslept'];
                        $sleepDebt = $totalSleepDebt - $totalOverslept;

                        $formattedStartDatePrev = date('F j', strtotime($startDate));
                        $formattedEndDatePrev = date('F j', strtotime($endDate));
                        $formattedSleepDebtPrev = convertHoursToHoursAndMinutes(abs($sleepDebt));

                        echo "<tr>";
                        echo "<td>{$formattedStartDatePrev} - {$formattedEndDatePrev}</td>";
                        echo "<td><a href='view_sleep_debt_details.php?start_date={$startDate}&end_date={$endDate}' style='color: white;'>" . 
                             $formattedSleepDebtPrev . "</a></td>";

                        echo "</tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<p>No previous week data available.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer mt-auto py-3" style="background-color: black; color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-left">
                <h4 style="color: white;">ADHD Society of the Philippines</h4>
                <address style="color: white;">
                    123 Health St., Makati City, Philippines<br>
                    Phone: +63 123 456 7890<br>
                    Email: contact@adhdsocietyph.org
                </address>
            </div>
            <div class="col-md-6 text-center text-md-right">
                <h5>Follow Us</h5>
                <a href="#" class="text-white mx-2"><i class="fa fa-facebook"></i></a>
                <a href="#" class="text-white mx-2"><i class="fa fa-twitter"></i></a>
                <a href="#" class="text-white mx-2"><i class="fa fa-instagram"></i></a>
                <a href="#" class="text-white mx-2"><i class="fa fa-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
