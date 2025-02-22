<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

// Database connection details
require_once '../dbconnection.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, middle_initial, last_name, Email, PhoneNumber, Password, ProfilePic FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_initial, $last_name, $email, $phone, $hashed_password, $profile_pic);
$stmt->fetch();
$stmt->close();

// Initialize message for feedback
$message = "";

// Update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $new_middle_initial = htmlspecialchars($_POST['middle_initial'], ENT_QUOTES, 'UTF-8');
    $new_last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Sanitize email
    $new_phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $old_password = $_POST['old_password']; // Get old password
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : ''; // Avoid undefined index warning
    
    // Validate inputs (basic server-side validation)
    if (!empty($new_first_name) && !empty($new_last_name) && !empty($new_email) && !empty($new_phone)) {
        // Update the user details in the database
        $sql = "UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, Email = ?, PhoneNumber = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing the SQL statement: " . $conn->error);
        }
        $stmt->bind_param("sssssi", $new_first_name, $new_middle_initial, $new_last_name, $new_email, $new_phone, $user_id);
        if ($stmt->execute() === false) {
            die("Error executing the SQL statement: " . $stmt->error);
        }
        $stmt->close();

        // Update password if provided
        if (!empty($new_password)) {
            // Check if the old password is correct
            if (password_verify($old_password, $hashed_password)) {
                // Hash the new password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET Password = ? WHERE UserID = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die("Error preparing the SQL statement: " . $conn->error);
                }
                $stmt->bind_param("si", $hashed_new_password, $user_id);
                if ($stmt->execute() === false) {
                    die("Error executing the SQL statement: " . $stmt->error);
                }
                $stmt->close();
                $message = "Profile updated successfully, and password changed!";
            } else {
                $message = "Old password is incorrect. Please try again.";
            }
        } else {
            // Feedback message after update
            $message = "Profile updated successfully!";
        }

        // Update Profile Picture
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            // Process profile picture upload
            $target_dir = "ProfilePic/"; // Directory where profile pictures are stored
            $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validate image file type and prevent PHP file upload
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            // Check file extension and MIME type (to prevent fake files)
            if (in_array($imageFileType, $allowed_types)) {
                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
                finfo_close($finfo);

                if (($mime_type == 'image/jpeg' || $mime_type == 'image/png' || $mime_type == 'image/gif') &&
                    !preg_match('/\.php$/i', $_FILES['profile_pic']['name']) && 
                    !preg_match('/\.phtml$/i', $_FILES['profile_pic']['name'])) {

                    // Check if the file is a valid image by verifying its content (not just file extension)
                    $check = getimagesize($_FILES['profile_pic']['tmp_name']);
                    if ($check !== false) {
                        // Check if a profile picture exists and delete it
                        if (!empty($profile_pic) && file_exists($profile_pic)) {
                            unlink($profile_pic); // Delete the old profile picture
                        }

                        // Move the new file to the target directory
                        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                            // Update the profile picture path in the database
                            $sql = "UPDATE users SET ProfilePic = ? WHERE UserID = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $target_file, $user_id);
                            $stmt->execute();
                            $stmt->close();
                            $message .= " Profile picture updated!";
                        } else {
                            $message = "Error uploading profile picture.";
                        }
                    } else {
                        $message = "The file is not a valid image.";
                    }
                } else {
                    $message = "Only JPG, JPEG, PNG & GIF files are allowed. PHP files are not permitted.";
                }
            } else {
                $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }

    } else {
        $message = "Please fill out all required fields.";
    }
}

// Re-fetch user details to reflect updated profile picture
$sql = "SELECT ProfilePic FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
     <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="manage_profile.css">
</head>
<body class="header-blue">
<?php include('loader.html'); ?>
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
        h3{
            color: black;
            text-align: center;
        }
        /* Adjust body to prevent overlap with fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on the height of your navbar */
        }
        .chart-wrapper {
        background-color: #ffffff; /* White background for a clean look */
        border-radius: 10px; /* Slightly more rounded corners */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); /* Softer and larger shadow for depth */
        padding: 30px; /* Increased padding for a more spacious feel */
        margin: 20px; /* Margin to separate from other elements */
        max-width: 600px; /* Set a maximum width for better readability */
        transition: transform 0.3s; /* Add a transition effect */
        }

        .chart-wrapper:hover {
            transform: translateY(-5px); /* Lift effect on hover */
        }
        
        #weekly-summary {
        background-color: #f8f9fa; /* Light background */
        border: 1px solid #dee2e6; /* Light border */
        }

        #weekly-summary h2 {
            font-family: Arial, sans-serif; /* Font family */
            font-weight: bold; /* Bold title */
            margin-bottom: 20px; /* Space below title */
            color: #007bff; /* Primary color for the title */
        }

        #weekly-summary p {
            margin: 10px 0; /* Space above and below paragraphs */
            font-family: Arial, sans-serif; /* Font family for paragraphs */
            color: #343a40; /* Darker text color */
            font-size: 1.2em; /* Larger font size */
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
                    <li class="nav-item"><a class="nav-link active" href="screening_questionnaire.php">Self Report Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sleep_tracker.php">Sleep Tracker</a></li>
                    <li class="nav-item"><a class="nav-link active" href="productivity.php">Productivity</a></li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_profile.php">My Profile <i class="fa fa-user-circle"></i></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More Info</a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="adhd.php">What is ADHD?</a>
                                    <a class="dropdown-item" href="brain.php">The ADHD Brain</a>
                                    <a class="dropdown-item" href="symptoms.php">ADHD Symptoms</a>
                                    <a class="dropdown-item" href="children.php">ADHD in Children</a>
                                    <a class="dropdown-item" href="adult.php">ADHD in Adults</a>
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

<!-- Profile Form -->
<div class="container mt-5 pt-5">
        <div class="p-4 bg-light rounded shadow">
        <!-- Feedback message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Profile Picture Card -->
            <div class="card mx-auto" style="width: 18rem;">
                <!-- Clickable Image -->
                <img src="<?php echo !empty($profile_pic) ? $profile_pic : 'images/default_profile.png'; ?>" 
                    class="card-img-top" 
                    alt="Profile Picture" 
                    style="height: 200px; object-fit: cover; cursor: pointer;" 
                    data-toggle="modal" 
                    data-target="#imageModal">
            </div>
            <div class="form-group">
                <label for="profile_pic">Change Profile Picture</label>
                <input type="file" class="form-control-file" id="profile_pic" name="profile_pic">
            </div>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_initial">Middle Initial</label>
                <input type="text" class="form-control" id="middle_initial" name="middle_initial" value="<?php echo htmlspecialchars($middle_initial); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" disabled>
                <small class="form-text text-muted">Leave blank if you don't want to change your password.</small>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<!-- Modal to display full image -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Profile Picture</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <!-- Full-size image inside the modal -->
                <img src="<?php echo !empty($profile_pic) ? $profile_pic : 'images/default_profile.png'; ?>" 
                     alt="Full Size Profile Picture" 
                     style="width: 100%; max-height: 500px; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
<!-- Hover effect to show full image -->
<style>
    .card:hover img {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>

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


<script>
    document.getElementById('old_password').addEventListener('input', function() {
        const newPasswordField = document.getElementById('new_password');
        // Enable new password field if old password is not empty, otherwise disable it
        newPasswordField.disabled = this.value.trim() === '';
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
