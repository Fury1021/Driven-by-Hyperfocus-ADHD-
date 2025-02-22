<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

// Check if a regular user is logged in
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

require_once '../dbconnection.php';

$showPasswordFields = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_code'])) {
        $resetCode = htmlspecialchars($_POST['reset_code']);

        // Check if the reset code is valid and not expired
        $sql = "SELECT Email, reset_code, reset_expiration FROM users WHERE reset_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $resetCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['Email'];
            $storedExpiration = $row['reset_expiration'];

            if ($storedExpiration > date('Y-m-d H:i:s')) {
                $showPasswordFields = true;
                $_SESSION['email'] = $email;
            } else {
                $message = "Invalid or expired reset code.";
            }
        } else {
            $message = "Invalid reset code.";
        }
    } elseif (isset($_POST['new_password'])) {
        // Handle new password submission
        $newPassword = htmlspecialchars($_POST['new_password']);
        $confirmPassword = htmlspecialchars($_POST['confirm_password']);

        if ($newPassword !== $confirmPassword) {
            $message = "Passwords do not match.";
        } else {
            // Update the password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $email = $_SESSION['email'];

            $sql = "UPDATE users SET Password = ?, reset_code = NULL, reset_expiration = NULL WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashedPassword, $email);
            if ($stmt->execute()) {
                $message = "Password has been successfully reset.";
                unset($_SESSION['email']);
            } else {
                $message = "Error resetting password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="user_login.css">
    <style> body { overflow-x: hidden; } </style>
</head>

<body>
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
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">More Info</a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="adhd_public.php">What is ADHD?</a>
                                <a class="dropdown-item" href="brain_public.php">The ADHD Brain</a>
                                <a class="dropdown-item" href="symptoms_public.php">ADHD Symptoms</a>
                                <a class="dropdown-item" href="children_public.php">ADHD in Children</a>
                                <a class="dropdown-item" href="adhd_public.php">ADHD in Adults</a>
                            </div>
                        </li>
                    </ul>
                    <span class="navbar-text mr-2"><a href="user_login.php" class="login">Log In</a></span>
                    <a class="btn btn-light action-button" href="sign_up.php">Sign Up</a>
                </div>
            </div>
        </nav>
    </div>

    <div class="login-dark">
        <form method="post" action="reset_password.php">
            <h2 class="sr-only">Reset Password</h2>
            <?php if (!$showPasswordFields): ?>
                <div class="form-group">
                    <label for="reset_code">Enter reset code:</label>
                    <input type="text" name="reset_code" class="form-control" required>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary btn-block" type="submit">Confirm Reset Code</button>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="new_password">Enter new password:</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm new password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button class="btn btn-success btn-block" type="submit">Reset Password</button>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($message): ?>
            <div class="alert alert-warning" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer mt-auto py-3" style="background-color: black; color: white;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-left">
                    <h4>ADHD Society of the Philippines</h4>
                    <address>
                        3rd Floor Uniplan Overseas Agency Office, 302 JP Rizal Street, Quezon City, Philippines<br>
                        <a href="mailto:adhdsociety@yahoo.com" style="color: white;">adhdsociety@yahoo.com</a><br>
                        09053906451
                    </address>
                </div>
                <div class="col-md-6 text-center text-md-right">
                    <a href="https://www.facebook.com/ADHDSOCPHILS/" class="social-link" target="_blank"><i class="fa fa-facebook-square"></i></a>
                    <a href="https://www.instagram.com/adhdsocph/" class="social-link" target="_blank"><i class="fa fa-instagram-square"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
