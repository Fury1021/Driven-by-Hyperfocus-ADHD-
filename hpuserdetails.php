<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: hplogin.php");
    exit();
}

// Database connection
require_once '../dbconnection.php';

// Fetch user details based on UserID
if (isset($_GET['UserID'])) {
    $userID = intval($_GET['UserID']); // Use $userID instead of $UserID
    $stmt = $conn->prepare("SELECT username, name, email FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userDetails = $userResult->fetch_assoc();
    $user_username = $userDetails['username'];
    $user_name = $userDetails['name'];
    $user_email = $userDetails['email'];
} else {
    echo "No UserID specified.";
    exit();
}

// Get selected year and month from form, or default to current year and month
$selected_year = isset($_POST['year']) ? $_POST['year'] : date('Y');
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('m');

// Check if the $conn variable is properly set
if (!$conn) {
    die("Database connection failed. Please check your connection.");
}

// Fetch tasks for the selected user, month, and year
$query = "SELECT * FROM tasks WHERE UserID = ? AND YEAR(Created_At) = ? AND MONTH(Created_At) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("isi", $userID, $selected_year, $selected_month); // Adjust the types as needed
$stmt->execute();
$result = $stmt->get_result();

// Initialize task counts
$priority_count = $waiting_count = $done_count = $side_count = 0;
$total_tasks = 0;

// Calculate task distribution
while ($task = $result->fetch_assoc()) {
    $total_tasks++;
    switch ($task['Status']) {
        case 'Priority':
            $priority_count++;
            break;
        case 'Waiting List':
            $waiting_count++;
            break;
        case 'Done':
            $done_count++;
            break;
        case 'Side Task':
            $side_count++;
            break;
    }
}

// Calculate total tasks (including procrastinate)
$total_tasks = $done_count + $priority_count + $waiting_count + $side_count;

// Calculate percentages for done, other, and side tasks
$done_percentage = $total_tasks > 0 ? ($done_count / $total_tasks) * 100 : 0;
$other_count = $priority_count + $waiting_count; // Reflect both priority and waiting tasks
$other_percentage = $total_tasks > 0 ? ($other_count / $total_tasks) * 100 : 0;
$side_percentage = $total_tasks > 0 ? ($side_count / $total_tasks) * 100 : 0;


// Fetch sleep records for the selected month and year
$sleep_query = "SELECT SUM(sleep_hours) AS total_sleep_hours, 
                        SUM(sleep_debt) AS total_sleep_debt, 
                        SUM(overslept) AS total_overslept 
                 FROM sleeptracker 
                 WHERE user_id = ? 
                 AND YEAR(date) = ? 
                 AND MONTH(date) = ?";

$sleep_stmt = $conn->prepare($sleep_query);
$sleep_stmt->bind_param("isi", $userID, $selected_year, $selected_month);
$sleep_stmt->execute();
$sleep_result = $sleep_stmt->get_result();
$sleep_data = $sleep_result->fetch_assoc();

// Assign variables for total sleep analytics
$total_sleep_hours = $sleep_data['total_sleep_hours'] ?? 0;
$total_sleep_debt = $sleep_data['total_sleep_debt'] ?? 0;
$total_overslept = $sleep_data['total_overslept'] ?? 0;

// Weekday vs Weekend Sleep Comparison
$weekday_sleep_query = "SELECT AVG(sleep_hours) AS weekday_sleep 
                        FROM sleeptracker 
                        WHERE user_id = ? 
                        AND YEAR(date) = ? 
                        AND MONTH(date) = ? 
                        AND DAYOFWEEK(date) BETWEEN 2 AND 6";

$weekday_sleep_stmt = $conn->prepare($weekday_sleep_query);
$weekday_sleep_stmt->bind_param("isi", $userID, $selected_year, $selected_month);
$weekday_sleep_stmt->execute();
$weekday_sleep_result = $weekday_sleep_stmt->get_result();
$weekday_sleep = $weekday_sleep_result->fetch_assoc()['weekday_sleep'] ?? 0;

$weekend_sleep_query = "SELECT AVG(sleep_hours) AS weekend_sleep 
                        FROM sleeptracker 
                        WHERE user_id = ? 
                        AND YEAR(date) = ? 
                        AND MONTH(date) = ? 
                        AND DAYOFWEEK(date) IN (1, 7)";

$weekend_sleep_stmt = $conn->prepare($weekend_sleep_query);
$weekend_sleep_stmt->bind_param("isi", $userID, $selected_year, $selected_month);
$weekend_sleep_stmt->execute();
$weekend_sleep_result = $weekend_sleep_stmt->get_result();
$weekend_sleep = $weekend_sleep_result->fetch_assoc()['weekend_sleep'] ?? 0;

// Fetch daily sleep, sleep debt, and overslept for trends
$daily_sleep_query = "SELECT DAY(date) AS day, 
                             SUM(sleep_hours) AS daily_sleep_hours, 
                             SUM(sleep_debt) AS daily_sleep_debt, 
                             SUM(overslept) AS daily_overslept 
                      FROM sleeptracker 
                      WHERE user_id = ? 
                      AND YEAR(date) = ? 
                      AND MONTH(date) = ? 
                      GROUP BY DAY(date)";

$daily_sleep_stmt = $conn->prepare($daily_sleep_query);
$daily_sleep_stmt->bind_param("isi", $userID, $selected_year, $selected_month);
$daily_sleep_stmt->execute();
$daily_sleep_result = $daily_sleep_stmt->get_result();

// Prepare data for sleep trends chart
$daily_sleep_data = [];
$daily_sleep_debt_data = [];
$daily_overslept_data = [];
$days = [];

while ($row = $daily_sleep_result->fetch_assoc()) {
    $days[] = $row['day'];
    $daily_sleep_data[] = $row['daily_sleep_hours'];
    $daily_sleep_debt_data[] = $row['daily_sleep_debt'];
    $daily_overslept_data[] = $row['daily_overslept'];
}

// Fetch task data for activeness level calculation
$sql = "SELECT COUNT(*) AS total_tasks,
               SUM(CASE WHEN Status = 'Done' THEN 1 ELSE 0 END) AS completed_tasks,
               SUM(CASE WHEN Status != 'Done' AND Status != 'Archive' AND Deadline < NOW() THEN 1 ELSE 0 END) AS overdue_tasks
        FROM tasks
        WHERE UserID = ?
        AND YEAR(Created_At) = ?
        AND MONTH(Created_At) = ?
        AND Status != 'Archive'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $userID, $selected_year, $selected_month);
$stmt->execute();
$result = $stmt->get_result();
$taskData = $result->fetch_assoc();

// Initialize activeness level
$activenessLevel = 100;

// Calculate activeness level based on selected month's tasks
if ($taskData['total_tasks'] > 0) {
    $notFinishedTasks = $taskData['total_tasks'] - $taskData['completed_tasks'];
    $overdueTasks = $taskData['overdue_tasks'];

    // Calculate reduction based on not finished and overdue tasks
    $reduction = ($notFinishedTasks * 10) + ($overdueTasks * 20);

    // Ensure activeness level does not go below 0
    $activenessLevel = max(0, 100 - $reduction);
}

// After fetching tasks and sleep data
$total_tasks = $done_count + $priority_count + $waiting_count + $side_count;
$total_sleep_hours = $sleep_data['total_sleep_hours'] ?? 0;

// Check for absence of tasks and sleep data
$no_tasks = $total_tasks == 0;
$no_sleep_data = $total_sleep_hours == 0;

// Message to display if there's no data
$noDataMessage = '';
if ($no_tasks && $no_sleep_data) {
    $noDataMessage = "Please add sleep data and tasks to properly show your productivity results.";
} elseif ($no_tasks) {
    $noDataMessage = "Please add tasks to properly show your productivity results.";
} elseif ($no_sleep_data) {
    $noDataMessage = "Please add sleep data to properly show your productivity results.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Dashboard</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="productivity.css">
    <!-- Bootstrap CSS for Styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js for Pie and Line Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('loader.html'); ?>
<header>
    <style>
    body{
    background-color: #FDFBEC;
    }
    .chart-container {
    border: 1px solid #ccc;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: -1px;
}
</style>
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <div class="container">
            <a href="hphomepage.php"><img src="images/logo3.png" alt="Your Logo"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navcol-1" aria-controls="navcol-1" aria-expanded="false" aria-label="Toggle navigation">
                <span class="sr-only">Toggle navigation</span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="hphomepage.php">Manage Users</a>
                    </li>
                </ul>
                <a href="admin_logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
</header>
<div class="container">
    <h1 class="mt-5">Productivity Dashboard</h1>

    <?php if (!empty($noDataMessage)): ?>
        <div class="alert alert-warning" role="alert">
            <?= $noDataMessage ?>
        </div>
    <?php endif; ?>
    

<!-- Date Picker for Year and Month -->
<form method="POST" class="mb-4">
    <div class="row">
        <div class="col-md-6">
            <label for="year">Select Year:</label>
            <select name="year" class="form-select">
                <?php for($i = 2020; $i <= date('Y'); $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $selected_year ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select><br>
        </div>
        <div class="col-md-6">
            <label for="month">Select Month:</label>
            <select name="month" class="form-select">
                <?php for($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= sprintf('%02d', $i) ?>" <?= $i == $selected_month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
                <?php endfor; ?>
            </select>
            <div class="d-flex justify-content-center mt-3">
                <button type="submit" class="btn btn-primary">Show Productivity</button>
            </div>
        </div>
    </div>
</form>

    <!-- Categories Section with Progress Bars -->
    <div class="row mt-5">
        <div class="col-md-6">
            <h3>Categories</h3>
            <div class="mb-3">
                <label>Priority</label>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= ($priority_count / $total_tasks) * 100 ?>%; background-color: #e14b4b;" aria-valuenow="<?= $priority_count ?>" aria-valuemin="0" aria-valuemax="100"><?= $priority_count ?> Tasks (<?= number_format(($priority_count / $total_tasks) * 100, 2) ?>%)</div>
                </div>
            </div>
            <div class="mb-3">
                <label>Waiting List</label>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= ($waiting_count / $total_tasks) * 100 ?>%; background-color: #f0a844;" aria-valuenow="<?= $waiting_count ?>" aria-valuemin="0" aria-valuemax="100"><?= $waiting_count ?> Tasks (<?= number_format(($waiting_count / $total_tasks) * 100, 2) ?>%)</div>
                </div>
            </div>
            <div class="mb-3">
                <label>Done</label>
                <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?= ($done_count / $total_tasks) * 100 ?>%; background-color: #488c44;" aria-valuenow="<?= $done_count ?>" aria-valuemin="0" aria-valuemax="100"><?= $done_count ?> Tasks (<?= number_format(($done_count / $total_tasks) * 100, 2) ?>%)</div>
                </div>
            </div>
            <div class="mb-3">
                <label>Side Task</label>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= ($side_count / $total_tasks) * 100 ?>%; background-color: #2064cc;" aria-valuenow="<?= $side_count ?>" aria-valuemin="0" aria-valuemax="100"><?= $side_count ?> Tasks (<?= number_format(($side_count / $total_tasks) * 100, 2) ?>%)</div>
                </div>
            </div>
        </div>

        <!-- Productivity Score Section with Pie Chart -->
        <div class="col-md-6">
            <h3>Productivity Score</h3>
            <div class="chart-container">
                <canvas id="productivityChart" width="350" height="350"></canvas>
            </div>
            <div id="productivitySummary" class="mt-3 text-center bold-text"></div>
        </div>
    </div>

        <div class="col-md-12 text-center">
            <h3>Activeness Level</h3>
            <div class="chart-container">
                <canvas id="activenessChart" width="400" height="400"></canvas>
            </div>
            <div class="info">
                Activeness Level: <?php echo $activenessLevel; ?>% &nbsp;
                Total Tasks: <?php echo $taskData['total_tasks']; ?> &nbsp;
                Overdue Tasks: <?php echo $taskData['overdue_tasks']; ?> &nbsp;
                Unfinished Tasks: <?php echo $taskData['total_tasks'] - $taskData['completed_tasks']; ?>
            </div>
        </div>

    <!-- Sleep Tracker Analytics -->
    <div class="row mt-5">
        <h2>Sleep Tracker Analytics</h2>

        <!-- Sleep Progress Bar with Sleep Debt (red) and Overslept (blue) -->
        <div class="col-md-12">
            <h3>Sleep Debt vs Overslept</h3>
            <div class="progress">
                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($total_sleep_debt / ($total_sleep_debt + $total_overslept)) * 100 ?>%;" aria-valuenow="<?= $total_sleep_debt ?>" aria-valuemin="0" aria-valuemax="100">
                    Sleep Debt: <?= $total_sleep_debt ?> hours
                </div>
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= ($total_overslept / ($total_sleep_debt + $total_overslept)) * 100 ?>%;" aria-valuenow="<?= $total_overslept ?>" aria-valuemin="0" aria-valuemax="100">
                    Overslept: <?= $total_overslept ?> hours
                </div>
            </div>
        </div>

        <!-- Weekday vs Weekend Sleep Chart -->
        <div class="col-md-6 mt-5">
            <h3>Weekday vs Weekend Sleep</h3>
            <div class="chart-container">
                <canvas id="weekdayWeekendChart"></canvas>
            </div>
        </div>

        <!-- Sleep Trends Chart -->
        <div class="col-md-6 mt-5">
            <h3>Sleep Trends</h3>
            <div class="chart-container">
                <canvas id="sleepTrendsChart"></canvas>
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

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script>
    // Assuming you have the following PHP variables defined
    var doneCount = <?= $done_count ?>; // Count of done tasks
    var otherCount = <?= $other_count ?>; // Count of other tasks
    var sideCount = <?= $side_count ?>; // Count of side tasks
    var totalCount = doneCount + otherCount + sideCount; // Update total tasks
    
    // Calculate percentages for summary display
    var donePercentage = <?= $done_percentage ?>; // Use PHP calculated percentage
    var otherPercentage = <?= $other_percentage ?>; // Use PHP calculated percentage
    var sidePercentage = <?= $side_percentage ?>; // Use PHP calculated percentage
    
    
    // Productivity Score Pie Chart
    var productivityCtx = document.getElementById('productivityChart').getContext('2d');
    var productivityChart = new Chart(productivityCtx, {
        type: 'pie',
        data: {
            labels: ['Done', 'Other (Priority and Waiting List)', 'Procrastinate (Side Tasks)'],
            datasets: [{
                data: [<?= $done_percentage ?>, <?= $other_percentage ?>, <?= $side_percentage ?>], // Use PHP calculated percentages
                backgroundColor: ['#28a745', '#6c757d', '#007bff'], // Add blue color for side tasks
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            let value = tooltipItem.raw.toFixed(2); // Show percentage with 2 decimals
                            return tooltipItem.label + ": " + value + "%"; // Show percentage from chart data
                        }
                    }
                }
            }
        }
    });
    
    // Display summary below the chart
    document.getElementById('productivitySummary').innerHTML = 
    `Done: ${donePercentage.toFixed(2)}% | Other: ${otherPercentage.toFixed(2)}% | Procrastinate: ${sidePercentage.toFixed(2)}%`;


    // Weekday vs Weekend Sleep Chart
    var weekdayWeekendCtx = document.getElementById('weekdayWeekendChart').getContext('2d');
    var weekdayWeekendChart = new Chart(weekdayWeekendCtx, {
        type: 'bar',
        data: {
            labels: ['Weekdays', 'Weekends'],
            datasets: [{
                label: 'Sleep Hours',
                data: [<?= $weekday_sleep ?>, <?= $weekend_sleep ?>],
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
    var sleepTrendsCtx = document.getElementById('sleepTrendsChart').getContext('2d');
    var sleepTrendsChart = new Chart(sleepTrendsCtx, {
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
                x: {
                    title: {
                        display: true,
                        text: 'X axis: Days of Month     Y axis: Number of Hours', // X-axis label
                    }
                },
                y: {
                    title: {
                        display: false,
                        text: 'Number of Hours', // Y-axis label
                    },
                    beginAtZero: true
                }
            }
        }
    });


    // Activeness Level Chart
    var activenessCtx = document.getElementById('activenessChart').getContext('2d');
    var activenessChart = new Chart(activenessCtx, {
        type: 'bar',
        data: {
            labels: ['Activeness Level'],
            datasets: [{
                label: 'Percentage',
                data: [<?= $activenessLevel ?>],
                backgroundColor: 'rgba(0, 128, 0, 0.2)', // Light green
                borderColor: 'rgba(0, 128, 0, 1)', // Dark green
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    
   // SweetAlert function
    const showChartDescription = (chartId, title, description) => {
        Swal.fire({
            title: title,
            text: description,
            confirmButtonText: 'Got it!',
            allowOutsideClick: true,
            allowEscapeKey: false,
            showCloseButton: true,
        });
    };
    // Chart descriptions
    const chartDescriptions = {
        productivityChart: {
            title: 'Productivity Chart',
            description: 'This chart shows your task completion percentage for the month.'
        },
        activenessChart: {
            title: 'Activeness Chart',
            description: 'This chart displays your overall activeness level based on completed and overdue tasks.'
        },
        weekdayWeekendChart: {
            title: 'Weekday Weekend Chart',
            description: 'This chart compares your average sleep hours on weekdays versus weekends per month.'
        },
        sleepTrendsChart: {
            title: 'Sleep Trends Chart',
            description: 'This chart shows the trend of your daily sleep hours, sleep debt, and overslept hours.'
        }
    };
    // Add event listeners to the buttons
    const buttons = ['productivityChartDesc', 'activenessChartDesc', 'weekdayWeekendChartDesc', 'sleepTrendsChartDesc'];
    buttons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        const chartId = buttonId.replace('Desc', '');
        button.addEventListener('click', () => {
            showChartDescription(chartId, chartDescriptions[chartId].title, chartDescriptions[chartId].description);
        });
    });
</script>

</body>
</html>
