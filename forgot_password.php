<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    // If an admin is logged in, redirect to the admin dashboard
    header("Location: admin_dashboard.php");
    exit;
}

// Check if a regular user is logged in
if (isset($_SESSION['user_id'])) {
    // If a regular user is logged in, redirect to the user dashboard
    header("Location: user_dashboard.php");
    exit;
}

require_once '../dbconnection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $currentTime = time(); // Get the current time

    // Check if there's a cooldown timer in session
    if (isset($_SESSION['last_request_time']) && $currentTime - $_SESSION['last_request_time'] < 180) {
        $timeRemaining = 180 - ($currentTime - $_SESSION['last_request_time']);
        echo "Please wait " . $timeRemaining . " seconds before requesting a new code.";
    } else {
        // Check if the email exists
        $sql = "SELECT * FROM users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Generate a random 7-digit reset code
            $resetCode = str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Code valid for 1 hour

            // Store reset code and expiry in the database
            $sql = "UPDATE users SET reset_code = ?, reset_expiration = ? WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $resetCode, $expiry, $email);
            $stmt->execute();

            // Store the request time in session
            $_SESSION['last_request_time'] = $currentTime;

            // Send reset code via email using PHP mail() function
            $subject = 'Password Reset Request';
            $headers = "From: Driven By Hyperfocus <drivenbyhyperfocus@gmail.com>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            // Email body
            $message = '
                <html>
                <head>
                    <style>
                        .reset-code {
                            font-size: 32px;
                            color: #ff4500;
                            font-weight: bold;
                            padding: 15px;
                            border: 2px dashed #ff4500;
                            display: inline-block;
                            background-color: #f7f7f7;
                            text-align: center;
                            letter-spacing: 2px;
                        }
                        .content {
                            font-family: Arial, sans-serif;
                            font-size: 16px;
                            color: #333;
                            margin: 0 auto;
                            padding: 20px;
                            max-width: 600px;
                            background-color: #ffffff;
                            border: 1px solid #e0e0e0;
                        }
                        .content p {
                            line-height: 1.6;
                        }
                        .footer {
                            font-size: 12px;
                            color: #777;
                            margin-top: 20px;
                            text-align: center;
                        }
                    </style>
                </head>
                <body>
                    <div class="content">
                        <h2>Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>You requested a password reset. Your password reset code is:</p>
                        <div class="reset-code">' . $resetCode . '</div>
                        <p>Please use this code within the next hour to reset your password.</p>
                        <p>If you didn\'t request this, you can safely ignore this email.</p>
                        <div class="footer">
                            <p>Thank you for using our services.</p>
                            <p>&copy; ' . date("Y") . ' Driven By Hyperfocus. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>';

            // Use the mail function to send the email
            if (mail($email, $subject, $message, $headers)) {
                // Redirect to reset password page after sending the email
                header("Location: reset_password.php");
                exit(); // Terminate script after redirect
            } else {
                echo "Error: Message could not be sent.";
            }
        } else {
            echo "Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="user_login.css">
    <style>
        body {
            overflow-x: hidden;
        }
    </style>
    <script>
        // Timer for cooldown
        let countdown = <?php echo isset($timeRemaining) ? $timeRemaining : 0; ?>;

        function startTimer() {
            if (countdown > 0) {
                let timer = setInterval(function () {
                    if (countdown <= 0) {
                        clearInterval(timer);
                        document.getElementById("timer").innerHTML = "You can request a new code now.";
                    } else {
                        document.getElementById("timer").innerHTML = "Please wait " + countdown + " seconds before requesting a new code.";
                    }
                    countdown--;
                }, 1000);
            }
        }
        window.onload = startTimer;
    </script>
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

    <!-- Forgot Password Form -->
    <div class="login-dark">
        <form method="post" action="forgot_password.php">
            <h2 class="sr-only">Forgot Password</h2>
            <div class="illustration"><i class="icon ion-ios-locked-outline"></i></div>
            <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
            <div class="form-group"><button class="btn btn-primary btn-block" type="submit">Send Reset Code</button></div>
            <a href="reset_password.php" class="forgot">Already have a code?</a>
            <div id="timer"></div>
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
</body>

</html>
