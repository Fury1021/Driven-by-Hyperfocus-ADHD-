<?php
include 'dbconnection.php';
session_start();

// Get the logged-in user ID
$user_id = $_SESSION['user_id'] ?? 1; // Replace 1 with actual user ID or handle missing session

// Get the start and end dates for the current week
$startDate = date('Y-m-d', strtotime('monday this week'));
$endDate = date('Y-m-d', strtotime('sunday this week'));

// Prepare and execute the SQL query for completed tasks
$sql_completed = "SELECT COUNT(*) AS completed_tasks FROM productivity WHERE status='done' AND created_at BETWEEN ? AND ? AND user_id = ?";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param('ssi', $startDate, $endDate, $user_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();

if ($result_completed->num_rows > 0) {
    $row_completed = $result_completed->fetch_assoc();
    $completedTasks = $row_completed['completed_tasks'];
} else {
    $completedTasks = 0;
}

// Prepare and execute the SQL query for undone tasks
$sql_undone = "SELECT COUNT(*) AS undone_tasks FROM productivity WHERE status='undone' AND created_at BETWEEN ? AND ? AND user_id = ?";
$stmt_undone = $conn->prepare($sql_undone);
$stmt_undone->bind_param('ssi', $startDate, $endDate, $user_id);
$stmt_undone->execute();
$result_undone = $stmt_undone->get_result();

if ($result_undone->num_rows > 0) {
    $row_undone = $result_undone->fetch_assoc();
    $undoneTasks = $row_undone['undone_tasks'];
} else {
    $undoneTasks = 0;
}

// Define target tasks
$targetTasks = 40;

// Set total tasks for calculation
$totalTasks = $completedTasks + $undoneTasks; // Total tasks are the sum of completed and undone tasks
$productivity = ($targetTasks > 0) ? ($completedTasks / $targetTasks) * 100 : 0;

// Reset the tasks on Monday
if (date('N') == 1) { // If today is Monday
    $sql_reset = "UPDATE productivity SET status='undone' WHERE user_id = ?";
    $stmt_reset = $conn->prepare($sql_reset);
    $stmt_reset->bind_param('i', $user_id);
    $stmt_reset->execute();
    $stmt_reset->close();
}

$stmt_completed->close();
$stmt_undone->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Productivity Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #pie-chart-container {
            margin-left: 50px; /* Adjust left margin to move right */
        }
        #productivity-chart {
            width: 200px; /* Set a smaller width */
            height: 200px; /* Set a smaller height */
        }
    </style>
</head>
<body class="header-blue">

<header>
    <style>
        header {
            position: fixed; /* Fix the header position */
            top: 0; /* Align to the top */
            width: 100%; /* Full width */
            z-index: 1000; /* Ensure it stays above other elements */
        }


        .navbar-nav .nav-item {
            display: inline-block; /* Keep items in one line */
        }

        .navbar-collapse {
            display: flex; /* Use flexbox for the navbar items */
            flex-wrap: nowrap; /* Prevent wrapping */
            align-items: center; /* Center items vertically */
        }

        .navbar img {
            max-height: 50px; /* Adjust logo size as needed */
            margin-right: 15px; /* Space between logo and nav items */
        }

        .navbar-text {
            margin-left: auto; /* Push log out link to the right */
        }

        /* Ensure the navbar is responsive */
        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: column; /* Stack items vertically */
                align-items: flex-start; /* Align to start */
            }

            .navbar-collapse {
                flex-wrap: wrap; /* Allow wrapping for mobile */
            }

            .navbar-toggler {
                margin-left: auto; /* Align toggler to the right */
            }

            /* Adjust the navbar for smaller screens */
            .navbar {
                padding: 5px; /* Reduce padding on smaller screens */
            }
        }

        /* Adjust body to prevent overlap with fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on the height of your navbar */
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
                    <li class="nav-item"><a class="nav-link active" href="behavior_survey.php">Behaviour Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sleep_tracker.php">Sleep Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="productivity_plan.php">Productivity Planner</a></li>
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
        <div class="col-lg-8">
            <h1 class="mt-5">Productivity Plan</h1>

            <div class="d-flex align-items-start">
            <div id="calendar" class="flex-grow-1">
                    <h2>Calendar</h2>
                    <?php
                    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    $dateComponents = getdate();
                    $month = $dateComponents['mon'];
                    $year = $dateComponents['year'];
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $monthName = $dateComponents['month'];
                    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
                    $dayOfWeek = date('w', $firstDayOfMonth);

                    echo "<table class='table table-bordered'>";
                    echo "<caption>$monthName $year</caption>";
                    echo "<tr>";
                    foreach ($daysOfWeek as $day) {
                        echo "<th class='text-center'>$day</th>";
                    }
                    echo "</tr><tr>";

                    if ($dayOfWeek > 0) {
                        for ($k = 0; $k < $dayOfWeek; $k++) {
                            echo "<td></td>";
                        }
                    }

                    $currentDay = 1;

                    while ($currentDay <= $daysInMonth) {
                        if ($dayOfWeek == 7) {
                            $dayOfWeek = 0;
                            echo "</tr><tr>";
                        }

                        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
                        $date = "$year-$month-$currentDayRel";

                        echo "<td class='text-center'><a href='calendar_view_tasks.php?date=$date'>$currentDay</a></td>";

                        $currentDay++;
                        $dayOfWeek++;
                    }

                    if ($dayOfWeek != 7) {
                        $remainingDays = 7 - $dayOfWeek;
                        for ($i = 0; $i < $remainingDays; $i++) {
                            echo "<td></td>";
                        }
                    }

                    echo "</tr>";
                    echo "</table>";
                    ?>
                </div>

                <div id="pie-chart-container" class="flex-grow-1">
                    <h2>Weekly Productivity Chart</h2>
                    <canvas id="productivity-chart"></canvas>
                </div>
            </div>

            <div id="analytics" class="mb-5">
                <h2>Productivity Analytics</h2>
                <?php
                if ($completedTasks >= 40) {
                    echo "<p>Well done, you're very productive this week, keep it up!!</p>";
                } elseif ($completedTasks >= 31) {
                    echo "<p>Great job! You're almost there!</p>";
                } elseif ($completedTasks >= 21) {
                    echo "<p>Good effort, keep pushing!</p>";
                } elseif ($completedTasks >= 11) {
                    echo "<p>Not bad, but there's room for improvement.</p>";
                } else {
                    echo "<p>You need to work harder!</p>";
                }

                echo "<p>Productivity: " . number_format($productivity, 2) . "%</p>";
                ?>
            </div>

            <div id="task-form">
                <h2>Add Task</h2>
                <form action="save_task.php" method="post">
                    <label for="task">Task:</label>
                    <input type="text" id="task" name="task" required>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </form>
            </div>

            <div id="view-tasks" class="mt-3">
                <a href="view_productivity.php" class="btn btn-secondary">View All Tasks</a>
                <a href="productivity_history.php" class="btn btn-secondary">View History</a>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const ctx = document.getElementById('productivity-chart').getContext('2d');

    const data = {
        labels: ['Completed Tasks', 'Remaining Tasks'],
        datasets: [{
            label: 'Productivity',
            data: [<?php echo $completedTasks; ?>, <?php echo $undoneTasks; ?>],
            backgroundColor: ['#36a2eb', '#ff6384'],
            hoverOffset: 4
        }]
    };

    const config = {
        type: 'pie',
        data: data,
    };

    new Chart(ctx, config);
});
</script>
</body>
</html>
