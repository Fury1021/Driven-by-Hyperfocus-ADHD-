<?php
session_start();
require_once '../dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the current month start (1st day) and end (last day)
$currentMonth = new DateTime();
$first_day_of_month = $currentMonth->modify('first day of this month')->format('Y-m-d');
$last_day_of_month = $currentMonth->modify('last day of this month')->format('Y-m-d');

// Update tasks with deadlines within the next 3 days to Priority, except for tasks with status '' or 'Waiting List' or 'Archive'
$current_date = new DateTime();
$three_days_from_now = (clone $current_date)->modify('+3 days');
$deadline_date = $three_days_from_now->format('Y-m-d');

$update_stmt = $conn->prepare("UPDATE tasks SET Status = 'Priority' WHERE UserID = ? AND Deadline <= ? AND Status NOT IN ('Done', '', 'Waiting List', 'Side Task', 'Archive')");
$update_stmt->bind_param('is', $user_id, $deadline_date);
$update_stmt->execute();
$update_stmt->close();

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $deadline = $_POST['deadline'];

    // Insert the new task into the appropriate table
    if ($status == '') {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task, status, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iss', $user_id, $name, $status);
    } else {
        $created_at = date('Y-m-d H:i:s'); // Get the current date and time
        $stmt = $conn->prepare("INSERT INTO tasks (UserID, Name, Status, Deadline, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $user_id, $name, $status, $deadline, $created_at); // Bind the created_at variable
    }
    $stmt->execute();
    $stmt->close();

    header("Location: taskmanager.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];

    // Update task status in the tasks table
    $update_stmt = $conn->prepare("UPDATE tasks SET Status = ? WHERE TaskID = ?");
    $update_stmt->bind_param('si', $new_status, $task_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Refresh the page to show updated status
    header("Location: taskmanager.php");
    exit;
}

// Fetch tasks for the current month
$categories = ['Priority', 'Waiting List', 'Done', 'Side Task'];
$tasks = [];
$countTasks = [];

// Initialize productivity done count
$countTasks[''] = 0;

foreach ($categories as $category) {
    // Fetch all tasks for the category for the current month
    $stmt = $conn->prepare("SELECT Name, Deadline FROM tasks WHERE UserID = ? AND Status = ? AND DATE(Deadline) BETWEEN ? AND ?");
    $stmt->bind_param('isss', $user_id, $category, $first_day_of_month, $last_day_of_month);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $tasks[$category] = $result->fetch_all(MYSQLI_ASSOC);
    $countTasks[$category] = $result->num_rows;
    $stmt->close();
}

// Helper function to get the color for each category
function getCategoryColor($category) {
    $colors = [
        'Priority' => '#e14b4b',
        'Side Task' => '#2064cc',
        'Waiting List' => '#f0a844',
        'Done' => '#488c44'
    ];
    return $colors[$category] ?? 'white';
}

// Calculate the total and max number of tasks
$totalTasks = array_sum($countTasks); // Total number of tasks across all categories
$maxTasks = max(array_values($countTasks)); // Maximum number of tasks in any category for full progress (100%)

// Function to calculate progress for each category
function calculateProgress($count, $maxTasks) {
    return $maxTasks > 0 ? ($count / $maxTasks) * 100 : 0;
}

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
    <title>Task Manager</title>
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
.taskboard a {
    text-decoration: none; /* Remove underline */
    color: inherit; /* Use the inherited text color */
}

.taskboard a:hover {
    color: inherit; /* Keep the color same as the normal state on hover */
    background-color: rgba(0, 0, 0, 0.1); /* Optional: Add a slight background color on hover for better visibility */
}
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

<div class="container">
    <div class="main-content row">
        <div class="col-lg-8">
            <h1 class="mt-5">Task Manager</h1>
            <a href="user_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
            
            <!-- Task Manager Form -->
            <form action="taskmanager.php" method="post" class="mb-5">
                <div class="form-group">
                    <label for="name">Name of Task:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="status">Set Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Priority">Priority</option>
                        <option value="Waiting List">Waiting List</option>
                        <option value="Done">Done</option>
                        <option value="Side Task">Side Task</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deadline">Deadline:</label>
                    <input type="datetime-local" name="deadline" id="deadline" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Create New Task</button> 
            </form>
            <a href="archive.php" style="margin-left:150px; margin-top:-145px" class="btn btn-secondary mb-3">Archive</a> <!-- Archive button -->
            

            <!-- Task Quadrants -->
            <div class="task-container">
                <div class="taskboard">
                    <?php foreach ($categories as $category): ?>
                        <?php
                        // Count the tasks for this category
                        $count = $countTasks[$category];
                        $categorySlug = strtolower(str_replace(' ', '_', $category));
                        $progress = calculateProgress($count, $maxTasks); // Calculate the progress percentage
                        ?>
                        <a href="view_<?php echo $categorySlug; ?>.php" class="quadrant" style="background-color: <?php echo getCategoryColor($category); ?>;">
                            <div class="quadrant-content">
                                <h2><?php echo htmlspecialchars($category); ?></h2>
                                <p>Number of Tasks: <?php echo $count; ?></p>
                                
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $count; ?>" aria-valuemin="0" aria-valuemax="<?php echo $maxTasks; ?>">
                                        <?php echo $count; ?> Tasks
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-container">
                <div class="chart-wrapper">
                    <canvas id="donutChart"></canvas>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const data = {
        labels: ['Priority', 'Side Task', 'Waiting List', 'Done'],
        datasets: [{
            label: 'Number of Tasks',
            data: [<?php echo $countTasks['Priority']; ?>, <?php echo $countTasks['Side Task']; ?>, <?php echo $countTasks['Waiting List']; ?>, <?php echo $countTasks['Done']; ?>],
            backgroundColor: ['#e14b4b', '#2064cc', '#f0a844', '#488c44'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    };

    const config = {
        type: 'doughnut',
        data: data
    };

    const donutChart = new Chart(document.getElementById('donutChart'), config);
</script>
</body>
</html>
