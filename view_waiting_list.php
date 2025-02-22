<?php
session_start();
require_once '../dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch tasks with Waiting List status
$stmt = $conn->prepare("SELECT * FROM tasks WHERE UserID = ? AND Status = 'Waiting List' ORDER BY Deadline ASC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$waiting_list_tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $task_ids = $_POST['task_ids'] ?? []; // Get selected task IDs

    // Debugging: Log the values
    error_log("Updating Task IDs: " . implode(", ", $task_ids) . " to Status: $new_status");

    // Update task status in the tasks table for selected tasks
    if (!empty($task_ids)) {
        $ids = implode(',', array_map('intval', $task_ids)); // Sanitize task IDs for SQL
        $update_stmt = $conn->prepare("UPDATE tasks SET Status = ? WHERE TaskID IN ($ids)");

        // Check if the statement was prepared successfully
        if ($update_stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            $_SESSION['error'] = "Failed to prepare update statement.";
            header("Location: view_waiting_list.php");
            exit;
        }

        // Bind parameters: 's' means string for status
        $update_stmt->bind_param('s', $new_status);

        // Execute the statement and check for errors
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Task status updated successfully."; // Optional success message
        } else {
            $_SESSION['error'] = "Failed to update task status: " . $update_stmt->error;
            error_log("Error updating task IDs $ids: " . $update_stmt->error); // Log the error
        }

        $update_stmt->close();
    } else {
        $_SESSION['error'] = "No tasks selected for update.";
    }

    // Refresh the page to show updated status
    header("Location: view_waiting_list.php");
    exit;
}

// Get the logged-in user's profile picture
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
    <title>Waiting List Tasks</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
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

<div class="container mt-5 p-4" style="background-color: white; border-radius: 20px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);">
    <h1 class="text-center mb-4" style="font-weight: bold; color: #f0a844;">Waiting List Tasks</h1>

    <a href="taskmanager.php" class="btn btn-outline-secondary mb-3" style="border-radius: 20px; padding: 10px 20px; font-size: 16px;">
        <i class="fa fa-arrow-left"></i> Back to Task Manager
    </a>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success text-center" style="border-radius: 10px;">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-center" style="border-radius: 10px;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (count($waiting_list_tasks) > 0): ?>
        <form method="post" action="">
            <div class="task-container">
                <table class="table table-hover table-bordered" style="border-radius: 15px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>Select</th>
                            <th>Task Name</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($waiting_list_tasks as $task): ?>
                            <tr class="text-center" style="color: white; background: linear-gradient(135deg, #f0a844, #ffba6d);">
                                <td>
                                    <input type="checkbox" name="task_ids[]" value="<?php echo $task['TaskID']; ?>">
                                </td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($task['Name']); ?></td>
                                <td><?php echo htmlspecialchars(date('F j, Y g:i A', strtotime($task['Deadline']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-group">
                <label for="status">Update Status for Selected Tasks:</label>
                <select name="status" id="status" class="form-control" style="border-radius: 10px; padding: 8px 12px;">
                    <option value="Priority">Priority</option>
                    <option value="Waiting List">Waiting List</option>
                    <option value="Side Task">Side Task</option>
                    <option value="Done">Done</option>
                    <option value="Archive">Archive</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-light" style="border-radius: 20px; padding: 8px 16px;">
                <i class="fa fa-refresh"></i> Update Status
            </button>
        </form>
    <?php else: ?>
        <p class="text-center text-muted" style="font-size: 18px;">No waiting list tasks found.</p>
    <?php endif; ?>
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
