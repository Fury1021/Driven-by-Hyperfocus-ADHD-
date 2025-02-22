<?php
session_start();

// Secure session settings
session_set_cookie_params([
    'lifetime' => 0, // Until the browser closes
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']), // Secure flag
    'httponly' => true, // Prevent JavaScript access
    'samesite' => 'Strict', // Prevent CSRF
]);

// Prevent access to this page if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Prevent access to this page if already logged in as a regular user
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

require_once '../dbconnection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input to prevent XSS and other attacks
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Validate the inputs before processing
    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        // Prepared statement to fetch admin details securely
        $query = "SELECT * FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin'] = $username;

            // Redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Invalid login credentials
            $error_message = "Invalid login credentials.";
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="admin_login.css">
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
                        <li class="nav-item"><a class="nav-link active" href="guest_about_us.php">About Us</a></li>
                        <li class="nav-item"><a class="nav-link active" href="public_contact.php">Contact Us</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More Info</a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">First Item</a>
                                <a class="dropdown-item" href="#">Second Item</a>
                                <a class="dropdown-item" href="#">Third Item</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <!-- Login Form -->
    <div class="login-dark">
        <form method="POST">
            <div class="admin-logo text-center">
                <i class="bi bi-person-circle" style="font-size: 80px; color: #2980ef;"></i>
            </div>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Admin Username" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Admin Password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
            <a href="forgot_password.php" class="forgot">Forgot your email or password?</a>
            <a href="hplogin.php" class="forgot">Login as Healthcare Professional</a>
            <a href="user_login.php" class="forgot">Login as User</a>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
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
                    <a class="social-link" href="https://www.facebook.com/ADHDSOCPHILS/" style="color: white;" target="_blank"><i class="fa fa-facebook-square"></i></a>
                    <a class="social-link" href="#" style="color: white;" target="_blank"><i class="fa fa-twitter-square"></i></a>
                    <a class="social-link" href="https://www.instagram.com/adhdsocph/" style="color: white;" target="_blank"><i class="fa fa-instagram-square"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
