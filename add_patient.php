<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Include the database connection
require_once '../dbconnection.php';

// Initialize the error message variable
$signupError = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input from the form
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_initial = htmlspecialchars($_POST['middle_initial']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    $phone = htmlspecialchars($_POST['phone']);
    
    // File upload logic
    $profile_pic = $_FILES['profile_pic']['name'];
    $target_dir = "ProfilePic/";  // Use the ProfilePic directory
    $target_file = $target_dir . basename($profile_pic);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($_FILES['profile_pic']['tmp_name']);
    if ($check === false) {
        $signupError = '<div class="alert alert-danger">File is not an image.</div>';
    }

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $signupError = '<div class="alert alert-danger">All fields are required.</div>';
    } elseif ($password !== $confirm_password) {
        $signupError = '<div class="alert alert-danger">Passwords do not match.</div>';
    } else {
        // Check if the email is already registered
        $sql = "SELECT Email FROM users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $signupError = '<div class="alert alert-danger">This email is already registered.</div>';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Move the uploaded file to the server
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                // Insert new user into the database with profile picture
                $sql = "INSERT INTO users (first_name, middle_initial, last_name, Username, Email, Password, PhoneNumber, ProfilePic) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die('Prepare failed: ' . $conn->error);
                }
                $stmt->bind_param("ssssssss", $first_name, $middle_initial, $last_name, $username, $email, $hashed_password, $phone, $target_file);

                if ($stmt->execute()) {
                    // Log the admin action
                    $admin_id = $_SESSION['admin_id']; // Get the logged-in admin's ID
                    $log_description = "'$username' account was successfully added.";
                    $log_sql = "INSERT INTO admin_logs (admin_id, description, dateandtime) VALUES (?, ?, NOW())";
                    $log_stmt = $conn->prepare($log_sql);
                    if ($log_stmt === false) {
                        die('Prepare failed: ' . $conn->error);
                    }
                    $log_stmt->bind_param("is", $admin_id, $log_description);
                    $log_stmt->execute();
                    $log_stmt->close();

                    // Send confirmation email using Hostinger's mail() function
                    $to = $email;  // Recipient email
                    $subject = 'Account Creation Confirmation';
                    $message = '
                        <p>Dear ' . $first_name . ' ' . $last_name . ',</p>
                        <p>Welcome to Driven By Hyperfocus! Your account has now been created, and you are now a member of the Driven by Hyperfocus family!</p>
                        <p>We are glad to welcome you to our community of dedicated individuals. As a member, you have access to a set of tools designed to help you stay focused and productive.</p>
                        <p>Thank you for selecting Driven By Hyperfocus. We are passionate to see what you can accomplish with us by your side.</p>
                        <p>Best Regards, Driven by Hyperfocus Team</p>
                    ';
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: Driven By Hyperfocus <drivenbyhyperfocus@gmail.com>" . "\r\n";
                    $headers .= "Reply-To: drivenbyhyperfocus@gmail.com" . "\r\n";

                    // Send the email
                    if (mail($to, $subject, $message, $headers)) {
                        $_SESSION['user_id'] = $stmt->insert_id; // Automatically log the user in
                        header("Location: list_of_patients.php");
                        exit;
                    } else {
                        $signupError = '<div class="alert alert-danger">Failed to send the confirmation email. Please try again later.</div>';
                    }
                } else {
                    $signupError = '<div class="alert alert-danger">Registration failed. Please try again.</div>';
                }
            } else {
                $signupError = '<div class="alert alert-danger">Sorry, there was an error uploading your file.</div>';
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="sign_up.css"> <!-- Assuming you're using the same CSS file -->
</head>
<body>
    <!-- Navigation Bar -->
    <div class="header-blue">
        <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
            <div class="container">
                <a href="index.php"><img src="images/logo3.png" alt="Your Logo"></a>
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
                        <a class="nav-link " href="admin_logs.php">Admin Logs</a>
                    </li>
                </ul>
                    <span class="navbar-text mr-2"><a href="user_login.php" class="login">Log In</a></span>
                </div>
            </div>
        </nav>
    </div>

    <!-- Sign Up Form -->
    <div class="login-dark">
        <form method="post" action="" enctype="multipart/form-data"> <!-- Added enctype -->
            <h2 class="sr-only">Sign Up Form</h2>
            <div class="illustration"><i class="icon ion-ios-person-add-outline"></i></div>
            <div class="form-group"><input class="form-control" type="text" name="first_name" placeholder="First Name" required></div>
            <div class="form-group"><input class="form-control" type="text" name="middle_initial" placeholder="Middle Initial"></div>
            <div class="form-group"><input class="form-control" type="text" name="last_name" placeholder="Last Name" required></div>
            <div class="form-group"><input class="form-control" type="text" name="username" placeholder="Username" required></div>
            <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
            <div class="form-group"><input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password" required></div>
            <div class="form-group"><input class="form-control" type="tel" name="phone" placeholder="Phone Number"></div>
            <div class="form-group"><input class="form-control" type="file" name="profile_pic" placeholder="Profile Picture" accept="image/*" required >Upload Profile Picture</div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">Create Account</button>
            </div>
            <?php if ($signupError != "") echo $signupError; ?>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
