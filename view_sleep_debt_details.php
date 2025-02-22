<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Database connection details
require_once '../dbconnection.php';

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch data from the database using the date range
$sql = "SELECT date, start_time, end_time, sleep_debt, overslept FROM sleeptracker WHERE date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($sql);

// Initialize data for chart
$dates = [];
$sleepDebts = [];
$oversleeps = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleep Debt Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="sleep_tracker.css">
    <style>
        #sleepDebtChart {
            background-color: white;
        }

        @media (max-width: 767px) {
            .table-responsive {
                overflow-x: auto;
            }
            
        }
    </style>
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
        <div class="row">
            <div class="col-12">
                <h1 class="mt-5">Sleep Debt Details</h1>
                <?php
                    // Check if any records are returned
                    if ($result->num_rows > 0) {
                        echo "<h2>Details from {$startDate} to {$endDate}</h2>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-bordered'>";
                        echo "<thead class='thead-dark'><tr>
                            <th>Day</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Total Sleep</th>
                            <th>Sleep Debt</th>
                            <th>Overslept</th>
                            </tr>
                        </thead>";
                        echo "<tbody>";
                    
                        // Loop through each record and display the data
                        while ($row = $result->fetch_assoc()) {
                            $date = $row['date'];
                            $startTime = new DateTime($row['start_time']);
                            $endTime = new DateTime($row['end_time']);
                            $sleepDebt = $row['sleep_debt'];
                            $overslept = $row['overslept'];
                    
                            // Calculate total sleep duration
                            $totalSleepDuration = $endTime->diff($startTime);
                            $totalSleepHours = $totalSleepDuration->h;
                            $totalSleepMinutes = $totalSleepDuration->i;
                    
                            // Store data for chart (if needed)
                            $dates[] = $startTime->format('H:i');
                            $sleepDebts[] = $sleepDebt;
                            $oversleeps[] = $overslept;
                    
                            // Convert sleep debt and overslept to hours and minutes
                            $sleepDebtHours = floor($sleepDebt);
                            $sleepDebtMinutes = ($sleepDebt - $sleepDebtHours) * 60;
                    
                            $oversleptHours = floor($overslept);
                            $oversleptMinutes = ($overslept - $oversleptHours) * 60;
                    
                            // Get the day of the week
                            $dayOfWeek = date('l', strtotime($date));
                    
                            // Format date and time for display
                            $formattedStartTime = $startTime->format('H:i');
                            $formattedEndTime = $endTime->format('H:i');
                    
                            // Display data in the table
                            echo "<tr>";
                            echo "<td>{$dayOfWeek}</td>";
                            echo "<td>{$formattedStartTime}</td>";
                            echo "<td>{$formattedEndTime}</td>";
                            echo "<td>{$totalSleepHours} hours " . $totalSleepMinutes . " minutes</td>";
                            echo "<td>{$sleepDebtHours} hours " . round($sleepDebtMinutes) . " minutes</td>";
                            echo "<td>{$oversleptHours} hours " . round($oversleptMinutes) . " minutes</td>";
                            echo "</tr>";
                        }
                    
                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                    } else {
                        echo "<p>No sleep data found for the selected date range.</p>";
                    }
                    
                    // Close the database connection
                    $conn->close();
                ?>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h3>Sleep Debt vs Overslept</h3>
                <canvas id="sleepDebtChart" width="100%" height="50"></canvas>
            </div>
        </div>
    </div>

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
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-facebook-square"></i></a>
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-twitter-square"></i></a>
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-instagram-square"></i></a>
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-linkedin"></i></a>
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-youtube-square"></i></a>
                    <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-pinterest-square"></i></a>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6 text-center text-md-left">
                    <a href="#" style="color: white;" target="_blank">Be a Member</a>
                </div>
                <div class="col-md-6 text-center text-md-right">
                    <a href="https://www.facebook.com/groups/119000601519017" style="color: white;" target="_blank">Join our Free Online Support Groups</a> |
                    <a href="#" style="color: white;" target="_blank">Join our Facebook Group</a> |
                    <a href="#" style="color: white;" target="_blank">Follow</a>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-center">
                    <a href="#" style="color: white; margin-right: 10px;" target="_blank">Facebook</a> |
                    <a href="#" style="color: white; margin-right: 10px;" target="_blank">Instagram</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('sleepDebtChart').getContext('2d');
        var sleepDebtData = {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Sleep Debt',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                data: <?php echo json_encode($sleepDebts); ?>,
                fill: true
            },
            {
                label: 'Overslept',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                data: <?php echo json_encode($oversleeps); ?>,
                fill: true
            }]
        };

        var myChart = new Chart(ctx, {
            type: 'bar',
            data: sleepDebtData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        document.getElementById("downloadBtn").onclick = function () {
            window.open('generate_pdf.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>');
        };
    </script>
</body>
</html>