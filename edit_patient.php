<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../dbconnection.php';

// Sanitize and validate input parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;  // Ensure it's an integer

// Fetch the user information securely using prepared statements
$query = "SELECT * FROM users WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);  // "i" for integer
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Store the profile picture path
$profile_pic = $user['ProfilePic'];

// Function to log admin actions securely
function logAdminAction($admin_id, $description, $conn) {
    $dateandtime = date("Y-m-d H:i:s");
    $query = "INSERT INTO admin_logs (admin_id, description, dateandtime) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $admin_id, $description, $dateandtime);
    $stmt->execute();
    $stmt->close();
}

// Handle form submission securely
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate form inputs
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_initial = htmlspecialchars(trim($_POST['middle_initial']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Get admin ID from session
    $admin_id = $_SESSION['admin_id'];

    // Update user information securely using prepared statements
    $query = "UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, Username = ?, Email = ?, PhoneNumber = ? WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $first_name, $middle_initial, $last_name, $username, $email, $phone, $id);

    if ($stmt->execute()) {
        // Log the action
        logAdminAction($admin_id, "updated the profile of user $username", $conn);
        header("Location: list_of_patients.php"); // Redirect to the user list
        exit();
    } else {
        echo "Error updating user: " . mysqli_error($conn);
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="patients.css"> <!-- Dashboard-specific CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .content {
            flex: 1; /* This makes the content area grow to fill the available space */
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .footer {
            background-color: black;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="header-blue">
        <nav class="navbar navbar-expand-md navbar-dark">
            <a class="navbar-brand" href="index.php"><img src="images/logo3.png" alt="Your Logo" height="40"></a>
            <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
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
                <a href="admin_logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container" style="margin-top: 50px; background-color: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <h1 class="text-center">Edit User</h1>
            
            <!-- Profile Picture Display -->
            <div class="card mx-auto" style="width: 18rem; margin-bottom: 20px;">
                <!-- Clickable Image -->
                <img src="<?php echo !empty($profile_pic) ? $profile_pic : 'images/default_profile.png'; ?>" 
                    class="card-img-top" 
                    alt="Profile Picture" 
                    style="height: 200px; object-fit: cover; cursor: pointer;" 
                    data-toggle="modal" 
                    data-target="#imageModal">
            </div>

            <!-- Modal for Profile Picture -->
            <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel">Profile Picture</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="<?php echo !empty($profile_pic) ? $profile_pic : 'images/default_profile.png'; ?>" 
                                alt="Profile Picture" 
                                style="width: 100%; height: auto;">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <form method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="middle_initial">Middle Initial</label>
                    <input type="text" class="form-control" name="middle_initial" id="middle_initial" value="<?php echo htmlspecialchars($user['middle_initial']); ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" name="username" id="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($user['PhoneNumber']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update User</button>
            </form>
        </div>
        <br>
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
</body>
</html>
