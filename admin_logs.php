<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../dbconnection.php';

// Fetch admin logs
$logQuery = "SELECT * FROM admin_logs ORDER BY dateandtime DESC"; // Modify the query as needed
$logResult = mysqli_query($conn, $logQuery);

// Check for errors in the query execution
if (!$logResult) {
    die("Error executing query: " . mysqli_error($conn));
}

// Fetch logs into an array
$logs = [];
while ($log = mysqli_fetch_assoc($logResult)) {
    $logs[] = $log; // Add each log entry to the logs array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Logs Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="patients.css"> <!-- Dashboard-specific CSS -->
    <style>
        body {
            background: linear-gradient(to bottom, #2596be, #475d62);
            color: white;
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        footer {
            background-color: #2c3e50;
            padding: 20px;
            color: white;
            text-align: center;
            width: 100%;
            position: relative;
            bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle; /* Align text vertically in table cells */
            color: white; /* Change font color to white */
        }

        .table {
            background-color: rgba(255, 255, 255, 0.1); /* Light background for the table */
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .table {
                width: 100%; /* Make table full width */
                font-size: 0.9em; /* Reduce font size for smaller screens */
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="header-blue">
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <a href="admin_dashboard.php"><img src="images/logo3.png" alt="Your Logo" style="height: 40px;"></a>
        <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navcol-1">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link " href="web_contents.php">Web Contents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="list_of_patients.php">Manage Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin_logs.php">Admin Logs</a>
                </li>
            </ul>
            <span class="navbar-text mr-2">
                <a href="admin_logout.php" class="login">Logout</a>
            </span>
        </div>
    </nav>
</div>

<!-- Main content container -->
<div class="container">
    <h1 class="mt-5 text-center">Admin Logs</h1>
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Admin ID</th>
                <th>Description</th>
                <th>Date and Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['admin_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                    <td><?php echo htmlspecialchars($log['dateandtime']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br><br>
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

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
