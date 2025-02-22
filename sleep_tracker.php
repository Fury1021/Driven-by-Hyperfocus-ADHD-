<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

include '../dbconnection.php';

$sql = "SELECT url, description FROM images";
$result = $conn->query($sql);

$user_id = $_SESSION['user_id'];
$sql = "SELECT ProfilePic FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Sleep Tracker</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
</head>
<body class="header-blue">
<?php include('loader.html'); ?>
<header>
    <style>
	 /* Custom media queries for mobile adjustments */
        @media (max-width: 768px) {
            .profile-section {
                text-align: center;
            }
            .profile-pic img {
                width: 50px;
                height: 50px;
            }
            .card {
                margin-bottom: 1rem;
            }
        }
    
        #calendar td a {
            color: inherit; /* Inherit the text color from the parent <td> */
            text-decoration: none; /* Remove underline from the link */
        }
    
        #calendar td a:hover {
            text-decoration: underline; /* Optional: Add underline on hover for better UX */
        }
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
    
        .navbar-nav .nav-item {
            display: inline-block;
        }
    
        .navbar-collapse {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
        }
    
        .navbar img {
            max-height: 50px;
            margin-right: 15px;
        }
    
        .navbar-text {
            margin-left: auto;
        }
    
        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: column;
                align-items: flex-start;
            }
    
            .navbar-collapse {
                flex-wrap: wrap;
            }
    
            .navbar-toggler {
                margin-left: auto;
            }
    
            .navbar {
                padding: 5px;
            }
        }
    
        h3 {
            color: black;
            text-align: center;
        }
    
        body {
            padding-top: 80px;
        }
    
        .chart-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 10px;
            margin: 10px; /* Reduced margin to bring cards closer */
            max-width: 600px;
            transition: transform 0.3s;
        }
    
        .chart-wrapper:hover {
            transform: translateY(-5px);
        }
    
        #weekly-summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
    
        #weekly-summary h2 {
            font-family: Arial, sans-serif;
            font-weight: bold;
            margin-bottom: 20px;
            color: #007bff;
        }
    
        #weekly-summary p {
            margin: 10px 0;
            font-family: Arial, sans-serif;
            color: #343a40;
            font-size: 1.2em;
        }
    
        #calendar {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
        }
    
        #calendar td {
            background-color: lightyellow;
            color: white;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
    
        @media (max-width: 768px) {
            #calendar td,
            #calendar th {
                padding: 5px;
                font-size: 0.9em;
            }
    
            #calendar {
                font-size: 0.8em;
            }
        }
    
        @media (max-width: 576px) {
            #calendar td,
            #calendar th {
                padding: 3px;
                font-size: 0.8em;
            }
    
            #calendar {
                font-size: 0.7em;
            }
    
            .chart-wrapper,
            #weekly-summary {
                padding: 15px;
            }
        }
    </style>

    
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <div class="container">
            <a href="user_dashboard.php"><img src="images/logo3.png" alt="Your Logo"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navcol-1" aria-controls="navcol-1" aria-expanded="false" aria-label="Toggle navigation">
                <span class="sr-only">Toggle navigation</span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
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
                            <a class="dropdown-item" href="adhd.php">What is ADHD?</a>
                            <a class="dropdown-item" href="brain.php">The ADHD Brain</a>
                            <a class="dropdown-item" href="symptoms.php">ADHD Symptoms</a>
                            <a class="dropdown-item" href="children.php">ADHD in Children</a>
                            <a class="dropdown-item" href="adult.php">ADHD in Adults</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <!-- Display the profile picture if it exists, otherwise display a default icon -->
                        <?php if (!empty($profile_pic)) : ?>
                            <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="rounded-circle" style="width: 50px; height: 50px; margin-left: 10px; cursor: pointer;" onclick="window.location.href='manage_profile.php';">
                        <?php else : ?>
                            <img src="images/default_profile.png" alt="Default Profile" class="rounded-circle" style="width: 50px; height: 50px; margin-left: 10px; cursor: pointer;" onclick="window.location.href='manage_profile.php';">
                        <?php endif; ?>
                    </li>
                    <li class="nav-item"><a class="nav-link active" href="user_logout.php">Log out</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<?php
// Assuming you already have user_id in the session
$user_id = $_SESSION['user_id'];

// Define the start and end dates for the current week
$startDate = date('Y-m-d', strtotime('monday this week'));
$endDate = date('Y-m-d', strtotime('sunday this week'));

// Weekday vs Weekend Sleep
$weekday_sleep_query = "SELECT AVG(sleep_hours) AS avg_weekday_sleep
                        FROM sleeptracker
                        WHERE user_id = '$user_id'
                        AND date BETWEEN '$startDate' AND '$endDate'
                        AND DAYOFWEEK(date) BETWEEN 2 AND 6"; // Monday to Friday

$weekday_sleep_result = $conn->query($weekday_sleep_query);
$weekday_sleep = $weekday_sleep_result->fetch_assoc()['avg_weekday_sleep'] ?? 0;

$weekend_sleep_query = "SELECT AVG(sleep_hours) AS avg_weekend_sleep
                        FROM sleeptracker
                        WHERE user_id = '$user_id'
                        AND date BETWEEN '$startDate' AND '$endDate'
                        AND DAYOFWEEK(date) IN (1, 7)"; // Sunday and Saturday

$weekend_sleep_result = $conn->query($weekend_sleep_query);
$weekend_sleep = $weekend_sleep_result->fetch_assoc()['avg_weekend_sleep'] ?? 0;


// Fetch daily sleep trends for the current week
$daily_sleep_query = "SELECT DAY(date) AS day, 
                            SUM(sleep_hours) AS daily_sleep_hours, 
                            SUM(sleep_debt) AS daily_sleep_debt, 
                            SUM(overslept) AS daily_overslept 
                    FROM sleeptracker 
                    WHERE user_id = '$user_id' 
                    AND date BETWEEN '$startDate' AND '$endDate' 
                    GROUP BY DAY(date)";
$daily_sleep_result = $conn->query($daily_sleep_query);

$daily_sleep_data = [];
$daily_sleep_debt_data = [];
$daily_overslept_data = [];
$days = [];

while ($row = $daily_sleep_result->fetch_assoc()) {
    $days[] = $row['day']; // Store the days with sleep data
    $daily_sleep_data[] = $row['daily_sleep_hours'];
    $daily_sleep_debt_data[] = $row['daily_sleep_debt'];
    $daily_overslept_data[] = $row['daily_overslept'];
}

// Fetch all days with sleep data for the current month
$monthStart = date('Y-m-01'); // First day of the current month
$monthEnd = date('Y-m-t'); // Last day of the current month

$sleep_data_query = "SELECT DAY(date) AS day FROM sleeptracker 
                     WHERE user_id = '$user_id' 
                     AND date BETWEEN '$monthStart' AND '$monthEnd'";
$sleep_data_result = $conn->query($sleep_data_query);

$sleep_days = [];
while ($row = $sleep_data_result->fetch_assoc()) {
    $sleep_days[] = $row['day']; // Store the days with sleep data
}

// Close the connection
$conn->close();
?>

<div class="container">
    <div class="row">
        <!-- Left side content -->
        <div class="col-lg-8">
            <h1 class="mt-5">Sleep Tracker</h1>
            <div id="calendar">
                <?php
                // Generate a simple calendar for the current month
                $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                $dateComponents = getdate();
                $month = $dateComponents['mon'];
                $year = $dateComponents['year'];

                // Create array containing number of days in each month
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                // Get the name of the month
                $monthName = $dateComponents['month'];

                // Get the index value 0-6 of the first day of the month
                $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
                $dayOfWeek = date('w', $firstDayOfMonth);

                // Display the name of the month above the calendar
                echo "<h2 class='text-center'>$monthName $year</h2>";

                // Create the table tag opener and first row of the calendar
                echo "<table class='table table-bordered'>";
                echo "<tr>";

                // Create the calendar headers
                foreach ($daysOfWeek as $day) {
                    echo "<th class='text-center'>$day</th>";
                }

                echo "</tr><tr>";

                // Fill the first week of the month with the appropriate number of blank days
                if ($dayOfWeek > 0) {
                    for ($k = 0; $k < $dayOfWeek; $k++) {
                        echo "<td style='background-color: white;'></td>"; // Blank days
                    }
                }

                // Continue with the rest of the days in the month
                $currentDay = 1;

                while ($currentDay <= $daysInMonth) {
                    // If the seventh column (Saturday) has been reached, start a new row
                    if ($dayOfWeek == 7) {
                        $dayOfWeek = 0;
                        echo "</tr><tr>";
                    }

                    $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
                    $date = "$year-$month-$currentDayRel";

                    // Determine the background color
                    if (in_array($currentDay, $sleep_days)) {
                        $bgColor = 'green'; // Green for days with sleep data
                    } else {
                        $bgColor = '#c1121f'; // Red for days without sleep data
                    }

                    echo "<td class='text-center' style='background-color: $bgColor;'><a href='calendar_sleep.php?date=$date'>$currentDay</a></td>";

                    // Increment counters
                    $currentDay++;
                    $dayOfWeek++;
                }

                // Complete the row of the last week in month, if necessary
                if ($dayOfWeek != 7) {
                    $remainingDays = 7 - $dayOfWeek;
                    for ($i = 0; $i < $remainingDays; $i++) {
                        echo "<td style='background-color: white;'></td>"; // Blank days
                    }
                }

                echo "</tr>";
                echo "</table>";
                ?>
            </div>
            <div id="sleep-input" class="mb-5">
                <h2>Record Sleep</h2>
                <form action="save_sleep.php" method="post">
                    <input type="hidden" name="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>">
                    
                    <!-- Sleep Start Time -->
                    <div class="form-group">
                        <label for="start_time">Sleep Start Time:</label>
                        <div class="d-flex">
                            <select name="start_hour" class="form-control mr-2" required>
                                <option value="" disabled selected>Hour</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="start_minute" class="form-control mr-2" required>
                                <option value="" disabled selected>Minute</option>
                                <?php for ($i = 0; $i < 60; $i += 5): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="start_am_pm" class="form-control" required>
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Wake Up Time -->
                    <div class="form-group">
                        <label for="end_time">Wake Up Time:</label>
                        <div class="d-flex">
                            <select name="end_hour" class="form-control mr-2" required>
                                <option value="" disabled selected>Hour</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="end_minute" class="form-control mr-2" required>
                                <option value="" disabled selected>Minute</option>
                                <?php for ($i = 0; $i < 60; $i += 5): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="end_am_pm" class="form-control" required>
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Sleep Data</button>
                </form>
            </div>
            <div id="weekly-summary" class="mb-5 p-4 border rounded shadow bg-light">
                <h2 class="text-center text-primary">Weekly Sleep Summary</h2>
                <?php
                include '../dbconnection.php';

                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));

                // Calculate the total sleep debt and overslept for the logged-in user
                $sql = "SELECT SUM(sleep_debt) AS total_sleep_debt, SUM(overslept) AS total_overslept
                        FROM sleeptracker 
                        WHERE user_id = '$user_id' 
                        AND date BETWEEN '$startDate' AND '$endDate'"; // Add user_id condition
                $result = $conn->query($sql);

                $totalSleepDebt = 0;
                $totalOverslept = 0;

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $totalSleepDebt = $row['total_sleep_debt'];
                    $totalOverslept = $row['total_overslept'];
                }

                // Calculate net sleep debt
                $netSleepDebt = $totalSleepDebt == 0 ? 0 : abs($totalSleepDebt - $totalOverslept);

                echo "<div class='text-center mt-4'>";
                echo "<p class='display-6 text-secondary'>Total Sleep Debt: <span class='text-danger'>" . number_format($totalSleepDebt, 2) . "</span> hours</p>";
                echo "<p class='display-6 text-secondary'>Total Overslept: <span class='text-success'>" . number_format($totalOverslept, 2) . "</span> hours</p>";
                echo "<p class='display-6 text-secondary'>Net Sleep Debt for the Week: <span class='text-warning'>" . number_format($netSleepDebt, 2) . "</span> hours</p>";
                echo "</div>";

                $conn->close();
                ?>
            </div>
            <div id="view-sleep-debt">
                <h2>View Sleep Debt History</h2>
                <a href="view_sleep_debt.php" class="btn btn-secondary">View Sleep Debt History</a>
            </div>
        </div>

        <!-- Right side charts section -->
        <div class="col-lg-4">
            <div class="chart-container mb-5 text-center">
                <div class="chart-wrapper">
                    <h3>Weekday vs Weekend Sleep</h3>
                    <canvas id="weekdayWeekendChart"></canvas>
                    <button class="btn btn-sm btn-info mt-2" onclick="showSweetAlert('Weekday vs Weekend Sleep', 'Chart Description')">See Description</button>
                </div>
            </div>

            <div class="chart-container mb-5 text-center">
                <div class="chart-wrapper">
                    <h3>Sleep Trends</h3>
                    <canvas id="sleepTrendsChart"></canvas>
                    <button class="btn btn-sm btn-info mt-2" onclick="showSweetAlert('Sleep Trends', 'Chart Description')">See Description</button>
                </div>
            </div>

            <div class="chart-container mb-5 text-center">
                <div class="chart-wrapper">
                    <h3>Total Sleep, Debt and Overslept Chart</h3>
                    <canvas id="donutChart"></canvas>
                    <button class="btn btn-sm btn-info mt-2" onclick="showSweetAlert('Total Sleep, Debt and Overslept', 'Chart Description')">See Description</button>
                </div>
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
                <address style="color: white; margin-top: 10px;">
                    3rd Floor Uniplan Overseas Agency Office, 302 JP Rizal Street corner Diego Silang Street, Project 4, Quezon City, Philippines, 1109<br>
                    <a href="mailto:adhdsociety@yahoo.com" style="color: white;" target="_blank">adhdsociety@yahoo.com</a><br>
                    09053906451
                </address>
            </div>
            <div class="col-md-6 text-center text-md-right">
                <a class="social-link" href="https://www.facebook.com/ADHDSOCPHILS/" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-facebook-square"></i></a>
                <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-twitter-square"></i></a>
                <a class="social-link" href="https://www.instagram.com/adhdsocph/" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-instagram-square"></i></a>
                <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-linkedin"></i></a>
                <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-youtube-square"></i></a>
                <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-pinterest-square"></i></a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6 text-center text-md-left">
                <a href="https://docs.google.com/forms/d/e/1FAIpQLSeaVc3RhWL1HnLDnMihI_0KyB5FM6YG9imNcMCxvsnycILQTQ/viewform" style="color: white;" target="_blank">Be a Member</a>
            </div>
            <div class="col-md-6 text-center text-md-right">
                <a href="https://docs.google.com/forms/d/e/1FAIpQLSd4Sf9BfC7kB4eBLRY-MRWYYhBkuRzKahNvQTNxebc12Gg0kQ/viewform" style="color: white;" target="_blank">Join our Free Online Support Groups</a> | 
                <a href="https://www.facebook.com/groups/119000601519017" style="color: white;" target="_blank">Join our Facebook Group</a> | 
                <a href="#" style="color: white;" target="_blank">Follow</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 text-center">
                <a href="https://www.facebook.com/ADHDSOCPHILS" style="color: white; margin-right: 10px;" target="_blank">Facebook</a> | 
                <a href="https://www.instagram.com/adhdsocph/" style="color: white; margin-right: 10px;" target="_blank">Instagram</a>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="sleep_tracker.js"></script>
<script>
const chartDescriptions = {
    'Weekday vs Weekend Sleep': 'This chart compares your average sleep hours on weekdays versus weekends during the current week.  It helps illustrate any differences in your sleep patterns.',
    'Sleep Trends': 'This line chart shows your daily sleep hours, sleep debt, and overslept hours for the current week.  It provides a visual representation of your sleep patterns over time.',
    'Total Sleep, Debt and Overslept': 'This bar chart summarizes your total sleep debt, total overslept hours, and the net sleep debt for the current week. It provides a quick overview of your overall sleep health.'
};

// Store scroll position before SweetAlert opens
let previousScrollPosition = { x: window.scrollX, y: window.scrollY };
function showSweetAlert(chartTitle) {
    previousScrollPosition = { x: window.scrollX, y: window.scrollY };
    const description = chartDescriptions[chartTitle] || 'No description available.'; // Handle missing descriptions
    Swal.fire({
        title: chartTitle,
        text: description,
        icon: 'info',
        confirmButtonText: 'Got it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.scrollTo(previousScrollPosition.x, previousScrollPosition.y);
        }
    });
}
    // Bar chart for Total Sleep Debt, Total Overslept, and Net Sleep Debt for the Week
    const totalSleepDebt = parseFloat("<?php echo $totalSleepDebt; ?>");
    const totalOverslept = parseFloat("<?php echo $totalOverslept; ?>");
    const netSleepDebt = parseFloat("<?php echo $netSleepDebt; ?>");
    
    const sleepData = {
        labels: ['Total Sleep Debt', 'Total Overslept', 'Net Sleep Debt'],
        datasets: [{
            label: 'Hours',
            data: [totalSleepDebt, totalOverslept, netSleepDebt],
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(75, 192, 192, 0.7)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    };
    
    const config = {
        type: 'bar',
        data: sleepData,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    };
    const ctx = document.getElementById('donutChart').getContext('2d');
    const sleepBarChart = new Chart(ctx, config);
    
    
    // Weekday vs Weekend Sleep Chart
    const weekdayWeekendCtx = document.getElementById('weekdayWeekendChart').getContext('2d');
    const weekdayWeekendChart = new Chart(weekdayWeekendCtx, {
        type: 'bar',
        data: {
            labels: ['Weekdays', 'Weekends'],
            datasets: [{
                label: 'Average Sleep Hours', // Changed label
                data: [<?php echo $weekday_sleep; ?>, <?php echo $weekend_sleep; ?>],
                backgroundColor: ['#36A2EB', '#FFCE56']
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    
    // Sleep Trends Line Chart
    const sleepTrendsCtx = document.getElementById('sleepTrendsChart').getContext('2d');
    const sleepTrendsChart = new Chart(sleepTrendsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($days) ?>,
            datasets: [
                {
                    label: 'Sleep Hours',
                    data: <?= json_encode($daily_sleep_data) ?>,
                    borderColor: '#4CAF50',
                    fill: false
                },
                {
                    label: 'Sleep Debt',
                    data: <?= json_encode($daily_sleep_debt_data) ?>,
                    borderColor: '#FF6384',
                    fill: false
                },
                {
                    label: 'Overslept',
                    data: <?= json_encode($daily_overslept_data) ?>,
                    borderColor: '#36A2EB',
                    fill: false
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
