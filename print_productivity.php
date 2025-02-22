<?php
// Start session and include database connection
session_start();
require_once '../dbconnection.php';
require_once('tcpdf/tcpdf.php'); // Include TCPDF

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get selected year and month from session or default to current year and month
$selected_year = isset($_SESSION['selected_year']) ? $_SESSION['selected_year'] : date('Y');
$selected_month = isset($_SESSION['selected_month']) ? $_SESSION['selected_month'] : date('m');

// Fetch tasks for the selected user, month, and year
$query = "SELECT * FROM tasks WHERE UserID = '$user_id' AND YEAR(Created_At) = '$selected_year' AND MONTH(Created_At) = '$selected_month'";
$result = $conn->query($query);

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

// Calculate "Other" tasks count
$other_count = $total_tasks - $done_count;

// Calculate total tasks
$total_tasks = $done_count + $other_count + $side_count; // Make sure this is accurate

// Fetch sleep records for the selected month and year
$sleep_query = "SELECT SUM(sleep_hours) AS total_sleep_hours, 
                        SUM(sleep_debt) AS total_sleep_debt, 
                        SUM(overslept) AS total_overslept 
                 FROM sleeptracker 
                 WHERE user_id = '$user_id' 
                 AND YEAR(date) = '$selected_year' 
                 AND MONTH(date) = '$selected_month'";

$sleep_result = $conn->query($sleep_query);
$sleep_data = $sleep_result->fetch_assoc();

// Assign variables for total sleep analytics
$total_sleep_hours = $sleep_data['total_sleep_hours'] ?? 0;
$total_sleep_debt = $sleep_data['total_sleep_debt'] ?? 0;
$total_overslept = $sleep_data['total_overslept'] ?? 0;

// Calculate net sleep debt
$net_sleep_debt = ($total_sleep_debt == 0) ? 0 : abs($total_sleep_debt - $total_overslept);

// Weekday vs Weekend Sleep Comparison
$weekday_sleep_query = "SELECT SUM(sleep_hours) AS weekday_sleep 
                        FROM sleeptracker 
                        WHERE user_id = '$user_id' 
                        AND YEAR(date) = '$selected_year' 
                        AND MONTH(date) = '$selected_month' 
                        AND DAYOFWEEK(date) BETWEEN 2 AND 6";

$weekday_sleep_result = $conn->query($weekday_sleep_query);
$weekday_sleep = $weekday_sleep_result->fetch_assoc()['weekday_sleep'] ?? 0;

$weekend_sleep_query = "SELECT SUM(sleep_hours) AS weekend_sleep 
                        FROM sleeptracker 
                        WHERE user_id = '$user_id' 
                        AND YEAR(date) = '$selected_year' 
                        AND MONTH(date) = '$selected_month' 
                        AND DAYOFWEEK(date) IN (1, 7)";

$weekend_sleep_result = $conn->query($weekend_sleep_query);
$weekend_sleep = $weekend_sleep_result->fetch_assoc()['weekend_sleep'] ?? 0;

// Fetch daily sleep, sleep debt, and overslept for trends
$daily_sleep_query = "SELECT DAY(date) AS day, 
                             SUM(sleep_hours) AS daily_sleep_hours, 
                             SUM(sleep_debt) AS daily_sleep_debt, 
                             SUM(overslept) AS daily_overslept 
                      FROM sleeptracker 
                      WHERE user_id = '$user_id' 
                      AND YEAR(date) = '$selected_year' 
                      AND MONTH(date) = '$selected_month' 
                      GROUP BY DAY(date)";
$daily_sleep_result = $conn->query($daily_sleep_query);

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

// Initialize activeness level
$activenessLevel = 100;

// SQL query to fetch task data for activeness level
$query = "SELECT 
              COUNT(*) AS total_tasks,
              SUM(CASE WHEN Status = 'Done' THEN 1 ELSE 0 END) AS completed_tasks,
              SUM(CASE WHEN Status != 'Done' AND Deadline < NOW() THEN 1 ELSE 0 END) AS overdue_tasks
          FROM tasks 
          WHERE UserID = '$user_id'";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $taskData = $result->fetch_assoc();
} else {
    $taskData = [
        'total_tasks' => 0,
        'completed_tasks' => 0,
        'overdue_tasks' => 0,
    ]; // Default values if no tasks are found
}

// Calculate activeness level based on tasks
if ($taskData) {
    $totalTasks = $taskData['total_tasks'];
    $completedTasks = $taskData['completed_tasks'];
    $overdueTasks = $taskData['overdue_tasks'];

    if ($totalTasks > 0) {
        $notFinishedTasks = $totalTasks - $completedTasks; 
        $reduction = ($notFinishedTasks * 10) + ($overdueTasks * 20);
        $activenessLevel = max(0, 100 - $reduction);
    }
}

// Get profile picture
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Productivity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <style>
        body {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Productivity Dashboard - Print View</h1>
        <h2>Task Distribution</h2>
        <canvas id="taskDistributionChart"></canvas>

        <h2>Sleep Analytics</h2>
        <canvas id="sleepAnalyticsChart"></canvas>

        <h2>Daily Sleep Trends</h2>
        <canvas id="dailySleepTrendsChart"></canvas>

        <button id="generatePDF" class="btn btn-primary mt-4">Generate PDF</button>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Task Distribution Chart
            const taskDistributionCtx = document.getElementById('taskDistributionChart').getContext('2d');
            const taskDistributionChart = new Chart(taskDistributionCtx, {
                type: 'pie',
                data: {
                    labels: ['Done', 'Other', 'Side Task', 'Waiting List', 'Priority'],
                    datasets: [{
                        label: 'Task Distribution',
                        data: [<?= $done_count ?>, <?= $other_count ?>, <?= $side_count ?>, <?= $waiting_count ?>, <?= $priority_count ?>],
                        backgroundColor: ['#4CAF50', '#FFC107', '#2196F3', '#FF5722', '#9C27B0'],
                    }]
                }
            });

            // Sleep Analytics Chart
            const sleepAnalyticsCtx = document.getElementById('sleepAnalyticsChart').getContext('2d');
            const sleepAnalyticsChart = new Chart(sleepAnalyticsCtx, {
                type: 'bar',
                data: {
                    labels: ['Total Sleep Hours', 'Total Sleep Debt', 'Total Overslept'],
                    datasets: [{
                        label: 'Sleep Analytics',
                        data: [<?= $total_sleep_hours ?>, <?= $total_sleep_debt ?>, <?= $total_overslept ?>],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                    }]
                }
            });

            // Daily Sleep Trends Chart
            const dailySleepTrendsCtx = document.getElementById('dailySleepTrendsChart').getContext('2d');
            const dailySleepTrendsChart = new Chart(dailySleepTrendsCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($days) ?>,
                    datasets: [{
                        label: 'Daily Sleep Hours',
                        data: <?= json_encode($daily_sleep_data) ?>,
                        borderColor: '#ff6384',
                        fill: false,
                    }, {
                        label: 'Daily Sleep Debt',
                        data: <?= json_encode($daily_sleep_debt_data) ?>,
                        borderColor: '#36a2eb',
                        fill: false,
                    }, {
                        label: 'Daily Overslept',
                        data: <?= json_encode($daily_overslept_data) ?>,
                        borderColor: '#cc65fe',
                        fill: false,
                    }]
                }
            });

            // Generate PDF using TCPDF
            document.getElementById('generatePDF').onclick = function () {
                const pdf = new TCPDF();
                pdf.setPrintHeader(false);
                pdf.setPrintFooter(false);
                pdf.AddPage();
                
                // Add content
                pdf.SetFont('helvetica', 'B', 20);
                pdf.Cell(0, 10, 'Productivity Dashboard - PDF View', 0, 1, 'C');
                pdf.Ln(10);
                
                // Add task distribution
                pdf.SetFont('helvetica', '', 12);
                pdf.Cell(0, 10, 'Task Distribution:', 0, 1);
                pdf.Image('path/to/your/task_distribution_chart.png', 10, 30, 180, 120, 'PNG');

                // Add sleep analytics
                pdf.Cell(0, 10, 'Sleep Analytics:', 0, 1);
                pdf.Image('path/to/your/sleep_analytics_chart.png', 10, 160, 180, 120, 'PNG');

                // Add daily sleep trends
                pdf.Cell(0, 10, 'Daily Sleep Trends:', 0, 1);
                pdf.Image('path/to/your/daily_sleep_trends_chart.png', 10, 290, 180, 120, 'PNG');
                
                // Output PDF to browser
                pdf.Output('D', 'productivity_dashboard.pdf');
            };
        </script>
    </div>
</body>
</html>
