<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch records grouped by year, month, and week
$sql = "SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, WEEK(created_at, 1) AS week, created_at, task, status 
        FROM productivity 
        WHERE user_id = ? 
        ORDER BY year DESC, month DESC, week DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$records_by_year = [];

// Organize records by year, month, and week
while ($row = $result->fetch_assoc()) {
    $year = $row['year'];
    $month = $row['month'];
    $week = $row['week'];
    $date = $row['created_at'];
    $task = $row['task'];
    $status = $row['status'];

    // Group records by year and month
    if (!isset($records_by_year[$year])) {
        $records_by_year[$year] = [];
    }
    if (!isset($records_by_year[$year][$month])) {
        $records_by_year[$year][$month] = [];
    }
    if (!isset($records_by_year[$year][$month][$week])) {
        $records_by_year[$year][$month][$week] = [];
    }
    // Store task details
    $records_by_year[$year][$month][$week][] = [
        'task' => $task,
        'status' => $status,
        'date' => $date
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Productivity History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light grey background */
        }

        .card-body {
            background-color: #ffffff; /* White background for card body */
        }

        .list-group-item {
            background-color: #ffffff; /* White background for the list item */
            color: #333333; /* Dark text color */
        }

        .list-group-item:hover {
            background-color: #e9ecef; /* Slightly darker background on hover */
        }

        .card-header {
            color: #343a40; /* Darker color for card headers */
        }

        .back-button {
            margin-top: 20px;
        }

        footer {
            background-color: black;
            color: white;
        }

        footer a {
            color: white; /* Ensure footer links are white */
        }
    </style>
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

<div class="container mt-5">
    <h1>Productivity History</h1>
    <div class="accordion" id="productivityAccordion">
        <?php foreach ($records_by_year as $year => $months): ?>
            <div class="card">
                <div class="card-header" id="heading_<?= $year ?>">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_<?= $year ?>" aria-expanded="true" aria-controls="collapse_<?= $year ?>">
                            <?= htmlspecialchars($year) ?>
                        </button>
                    </h5>
                </div>

                <div id="collapse_<?= $year ?>" class="collapse" aria-labelledby="heading_<?= $year ?>" data-parent="#productivityAccordion">
                    <div class="card-body">
                        <div class="accordion" id="accordion_months_<?= $year ?>">
                            <?php foreach ($months as $month => $weeks): ?>
                                <div class="card">
                                    <div class="card-header" id="heading_month_<?= $year . $month ?>">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_month_<?= $year . $month ?>" aria-expanded="true" aria-controls="collapse_month_<?= $year . $month ?>">
                                                <?= htmlspecialchars(date("F", mktime(0, 0, 0, $month, 1, $year))) ?>
                                            </button>
                                        </h5>
                                    </div>

                                    <div id="collapse_month_<?= $year . $month ?>" class="collapse" aria-labelledby="heading_month_<?= $year . $month ?>" data-parent="#accordion_months_<?= $year ?>">
                                        <div class="card-body">
                                            <div class="accordion" id="accordion_weeks_<?= $year . $month ?>">
                                                <?php foreach ($weeks as $week => $tasks): ?>
                                                    <div class="card">
                                                        <div class="card-header" id="heading_week_<?= $year . $month . $week ?>">
                                                            <h5 class="mb-0">
                                                                <?php
                                                                // Calculate the start and end dates for the week
                                                                $start_date = new DateTime();
                                                                $start_date->setISODate($year, $week);
                                                                $end_date = clone $start_date;
                                                                $end_date->modify('+6 days');
                                                                ?>
                                                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_week_<?= $year . $month . $week ?>" aria-expanded="true" aria-controls="collapse_week_<?= $year . $month . $week ?>">
                                                                    <?= $start_date->format('F j') . ' - ' . $end_date->format('F j') ?>
                                                                </button>
                                                            </h5>
                                                        </div>

                                                        <div id="collapse_week_<?= $year . $month . $week ?>" class="collapse" aria-labelledby="heading_week_<?= $year . $month . $week ?>" data-parent="#accordion_weeks_<?= $year . $month ?>">
                                                            <div class="card-body">
                                                                <ul class="list-group">
                                                                    <?php foreach ($tasks as $task_detail): ?>
                                                                        <li class="list-group-item">
                                                                            <?= htmlspecialchars($task_detail['task']) ?> - <?= htmlspecialchars($task_detail['status']) ?> - <?= htmlspecialchars($task_detail['date']) ?>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="taskmanager.php" class="btn btn-primary back-button">Back to Task Manager</a>
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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
