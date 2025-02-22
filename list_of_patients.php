<?php
session_start();
require_once '../dbconnection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Function to log admin actions securely
function logAdminAction($admin_id, $description, $conn) {
    $dateandtime = date("Y-m-d H:i:s");
    $query = "INSERT INTO admin_logs (admin_id, description, dateandtime) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $admin_id, $description, $dateandtime);
    $stmt->execute();
    $stmt->close();
}

// Fetch all users from the `users` table securely
$query = "SELECT * FROM users";
$result = $conn->query($query);

// Handle the removal of selected users securely
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove']) && isset($_POST['user_ids'])) {
    // Get admin ID from session
    $admin_id = $_SESSION['admin_id'];

    // Validate user IDs
    foreach ($_POST['user_ids'] as $userId) {
        $userId = intval($userId); // Convert user ID to an integer to prevent injection

        // Fetch user's name for logging
        $userQuery = "SELECT first_name, last_name FROM users WHERE UserID = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $user = $userResult->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Delete the user securely
            $deleteQuery = "DELETE FROM users WHERE UserID = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Log the action
            $description = "{$user['first_name']} {$user['last_name']} account removed";
            logAdminAction($admin_id, $description, $conn);
        }
    }

    $_SESSION['notification'] = "Selected accounts have been successfully removed.";
    header("Location: list_of_patients.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="patients.css"> <!-- Dashboard-specific CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1;
        }
        .footer {
            background-color: black;
            color: white;
        }
        @media (max-width: 768px) {
            .table thead {
                display: none;
            }
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                overflow: hidden;
            }
            .table td {
                text-align: right;
                padding: 10px;
                position: relative;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                text-align: left;
                font-weight: bold;
                color: #555;
            }
            .table td:last-child {
                text-align: center;
            }
        }
    </style>
</head>
<body>
<body>
    <?php
    // Check if there's a notification message to display
    if (isset($_SESSION['notification'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
            . $_SESSION['notification'] .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
            . '<span aria-hidden="true">&times;</span>'
            . '</button></div>';
        // Clear the notification after displaying
        unset($_SESSION['notification']);
    }
    ?>

    <!-- Navbar -->
    <div class="header-blue">
        <nav class="navbar navbar-expand-md navbar-dark">
            <a class="navbar-brand" href="admin_dashboard.php"><img src="images/logo3.png" alt="Your Logo" height="40"></a>
            <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="web_contents.php">Web Contents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="list_of_patients.php">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="admin_logs.php">Admin Logs</a>
                    </li>
                </ul>
                <a href="admin_logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </nav>
    </div>
    <!-- Your page content goes here -->
<div class="content">
    <!-- Container for Manage Users section -->
    <div class="container mt-5" style="background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <h1 class="text-center">Manage Users</h1>
        
        <!-- Search Form -->
        <form method="GET" class="form-inline mb-3">
            <input type="text" name="search" class="form-control mr-2" style="width: 40%;" placeholder="Search by name, email, or username" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <!-- Scrollable Table -->
        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
            <form method="POST">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Search functionality
                        $searchQuery = '';
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $search = mysqli_real_escape_string($conn, $_GET['search']);
                            $searchQuery = "WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR Username LIKE '%$search%' OR Email LIKE '%$search%'";
                        }
                        
                        $query = "SELECT * FROM users $searchQuery";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): ?>    
                        <tr>
                            <td><input type="checkbox" name="user_ids[]" value="<?php echo $row['UserID']; ?>"></td>
                            <td data-label="Full Name">
                                <a href="edit_patient.php?id=<?php echo $row['UserID']; ?>">
                                    <?php echo $row['first_name'] . ' ' . $row['middle_initial'] . '. ' . $row['last_name']; ?>
                                </a>
                            </td>
                            <td data-label="Username"><?php echo $row['Username']; ?></td>
                            <td data-label="Email"><?php echo $row['Email']; ?></td>
                            <td data-label="Phone Number"><?php echo $row['PhoneNumber']; ?></td>
                            <td data-label="Actions">
                                <a href="edit_patient.php?id=<?php echo $row['UserID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>

        <!-- Buttons for actions -->
        <div class="d-flex justify-content-between">
            <button type="submit" name="remove" class="btn btn-danger">Remove Selected Users</button>
            <a href="add_patient.php" class="btn btn-primary">Add User</a>
        </div>
            </form>
    </div>
</div>


<br>
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

</body>
</html>
