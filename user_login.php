<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    // Redirect to admin dashboard if an admin is logged in
    header("Location: admin_dashboard.php");
    exit;
}

// Check if a regular user is logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to user dashboard if a user is logged in
    header("Location: user_dashboard.php");
    exit;
}

$loginError = ''; // Clear previous error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database connection
   require_once '../dbconnection.php';

    // Sanitize user input to prevent XSS and other vulnerabilities
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING); // no need to sanitize the password field

    // Validate the sanitized email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = '<div class="alert alert-danger">Invalid email format.</div>';
    } else {
        // Fetch user from the database using a prepared statement to prevent SQL injection
        $sql = "SELECT UserID, Password FROM users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error); // Handle error if the statement preparation fails
        }

        $stmt->bind_param("s", $email); // Bind the sanitized email parameter
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($userID, $stored_password);
        $stmt->fetch();

        // Verify the password
        if ($stmt->num_rows > 0 && password_verify($password, $stored_password)) {
            // Start the session for the user if credentials are correct
            $_SESSION['user_id'] = $userID;
            header("Location: user_dashboard.php");
            exit;
        } else {
            // Display error message if login fails
            $loginError = '<div class="alert alert-danger">Incorrect email or password.</div>';
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
    <title>Login</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="user_login.css">
    <style>
        /* Apply overflow hidden to the entire body */
        body {
            overflow-x: hidden;
        }
    </style>
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
                            <a class="nav-link active" href="guest_about_us.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="public_contact.php">Contact Us</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                More Info
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="adhd_public.php">What is ADHD?</a>
                                <a class="dropdown-item" href="brain_public.php">The ADHD Brain</a>
                                <a class="dropdown-item" href="symptoms_public.php">ADHD Symptoms</a>
                                <a class="dropdown-item" href="children_public.php">ADHD in Children</a>
                                <a class="dropdown-item" href="adhd_public.php">ADHD in Adults</a>
                            </div>
                        </li>
                    </ul>

                    <span class="navbar-text mr-2">
                        <a href="user_login.php" class="login">Log In</a>
                    </span>
                    <a class="btn btn-light action-button" role="button" href="sign_up.php">Sign Up</a>
                </div>
            </div>
        </nav>
    </div>

      <!-- Login Form -->
    <div class="login-dark">
        <form method="post" action="">
            <h2 class="sr-only">Login Form</h2>
            <div class="illustration"><i class="icon ion-ios-locked-outline"></i></div>
            <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
            <div class="form-group"><button class="btn btn-primary btn-block" type="submit">Log In</button></div>
            <a href="forgot_password.php" class="forgot">Forgot your email or password?</a>
            <a href="hplogin.php" class="forgot">Login as Healthcare Professional</a>
            <a href="admin_login.php" class="forgot">Login as Admin</a>
            <?php if (isset($loginError)) echo $loginError; ?>
        </form>
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
