<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

require_once '../dbconnection.php';

$date = isset($_GET['date']) ? $_GET['date'] : null;

if ($date) {
    $sql = "SELECT start_time, end_time, sleep_debt, overslept FROM sleeptracker WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "No date selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Sleep Data for <?php echo $date; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="task_manager.css">
    <style>
        body {
            padding-top: 80px;
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

        /* Responsive adjustments for table */
        table.table {
            width: 100%;
            margin-bottom: 1.5em;
        }

        .table th, .table td {
            word-wrap: break-word;
            white-space: normal;
            text-align: center;
        }

        .container {
            padding: 0 15px;
        }

        .chart-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 30px;
            margin: 20px;
            max-width: 100%;
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
            color: #007bff;
        }

        #weekly-summary p {
            font-family: Arial, sans-serif;
            color: #343a40;
            font-size: 1.2em;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar-collapse {
                flex-wrap: wrap;
            }

            .navbar {
                padding: 5px;
            }

            .navbar-text {
                text-align: center;
                margin: 10px 0;
            }

            table.table {
                font-size: 0.85em;
            }

            .footer {
                text-align: center;
            }

            .footer .col-md-6 {
                text-align: center;
            }

            .footer .social-link {
                margin: 5px;
            }

            /* Adjust table for smaller devices */
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
                    <li class="nav-item"><a class="nav-link active" href="screening_questionnaire.php">Behaviour Tracker</a></li>
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
                </ul>
                <span class="navbar-text">
                    <a href="user_logout.php" class="logout">Log Out</a>
                </span>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <h1 class="mt-5">Sleep Data for <?php echo $date; ?></h1>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Sleep Debt</th>
                    <th>Overslept</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['start_time'] . "</td>";
                        echo "<td>" . $row['end_time'] . "</td>";
                        echo "<td>" . $row['sleep_debt'] . "</td>";
                        echo "<td>" . $row['overslept'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No sleep data found for this date.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <a href="sleep_tracker.php" class="btn btn-secondary">Back to Calendar</a>
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
                <a class="social-link" href="#" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-linkedin-square"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>
