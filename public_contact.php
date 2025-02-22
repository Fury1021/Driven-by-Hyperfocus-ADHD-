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

// Include the database connection
require_once '../dbconnection.php';

// Initialize variables for success and error messages
$contactSuccess = '';
$contactError = '';

// Generate a unique token for the form if not already set
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check token validity
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        $contactError = "Invalid submission. Please try again.";
    } else {
        // Sanitize and validate input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

        // Validate the sanitized input
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $contactError = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactError = "Invalid email format.";
        } else {
            // Use prepared statements to prevent SQL injection (although not used for this specific form, it's good practice)
            // Here we're sending an email, so no DB interaction is involved

            // Construct the email message
            $to = 'drivenbyhyperfocus@gmail.com';  // Change to your email address
            $headers = "From: $name <$email>" . "\r\n" .
                       "Reply-To: $email" . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();
            $emailMessage = "Name: $name\nEmail: $email\n\nMessage:\n$message";

            // Send the email using the built-in mail() function
            if (mail($to, $subject, $emailMessage, $headers)) {
                $contactSuccess = 'Message has been sent successfully!';
                // Clear form token and fields after successful submission
                unset($_SESSION['form_token']);
                $_SESSION['form_token'] = bin2hex(random_bytes(32));
            } else {
                $contactError = 'Failed to send the message. Please try again later.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
                                <a class="dropdown-item" href="adult_public.php">ADHD in Adults</a>
                            </div>
                        </ul>
                        <span class="navbar-text mr-2">
                            <a href="user_login.php" class="login">Log In</a>
                        </span>
                        <a class="btn btn-light action-button" role="button" href="sign_up.php">Sign Up</a>
                    </div>
                </div>
            </nav>
    </div>

    <!-- Contact Us Form -->
    <div class="login-dark">
        <form method="post" action="">
            <h2 class="text-center">Contact Us</h2>
            <div class="illustration"><i class="icon ion-ios-mail-outline"></i></div>

            <!-- Hidden Token Input -->
            <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">

            <!-- Name Input -->
            <div class="form-group">
                <input class="form-control" type="text" name="name" placeholder="Your Name" required>
            </div>

            <!-- Email Input -->
            <div class="form-group">
                <input class="form-control" type="email" name="email" placeholder="Your Email" required>
            </div>

            <!-- Subject Input -->
            <div class="form-group">
                <input class="form-control" type="text" name="subject" placeholder="Subject" required>
            </div>

            <!-- Message Textarea -->
            <div class="form-group">
                <textarea class="form-control" name="message" placeholder="Your Message" rows="5" required></textarea>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">Send Message</button>
            </div>
        </form>

        <!-- Alert Container -->
        <div class="alert-container">
            <?php if (!empty($contactError)) echo "<div class='alert alert-danger fade-out'>$contactError</div>"; ?>
            <?php if (!empty($contactSuccess)) echo "<div id='success-message' class='alert alert-success fade-out'>$contactSuccess</div>"; ?>
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

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript for fading out success message -->
    <script>
        $(document).ready(function() {
            var successMessage = $('#success-message');
            if (successMessage.length) {
                setTimeout(function() {
                    successMessage.addClass('hidden');
                }, 4000);  // Hide after 4 seconds
            }
        });
    </script>
</body>
</html>
