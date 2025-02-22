<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
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
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        $contactError = "Invalid submission. Please try again.";
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);

        // Validate input
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $contactError = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactError = "Invalid email format.";
        } else {
            // Email headers and body
            $to = 'drivenbyhyperfocus@gmail.com';  // Recipient email
            $headers = "From: $name <$email>\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $emailBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";

            if (mail($to, $subject, $emailBody, $headers)) {
                $contactSuccess = 'Message has been sent successfully!';
                unset($_SESSION['form_token']);
                $_SESSION['form_token'] = bin2hex(random_bytes(32));
            } else {
                $contactError = 'Message could not be sent. Please try again later.';
            }
        }
    }
}

// Fetch user's profile picture
$user_id = $_SESSION['user_id'];
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
    <title>Contact Us</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="user_login.css">
    <style>
        body { overflow-x: hidden; }
    </style>
</head>
<body>
    <?php include('loader.html'); ?>
   <!-- Navigation Bar -->
    <div class="header-blue">
        <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
            <div class="container">
                <a href="user_dashboard.php"><img src="images/logo3.png" alt="Your Logo"></a>
                <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
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
    <div class="login-dark">
        <form method="post" action="">
            <h2 class="text-center">Contact Us</h2>
            <div class="illustration"><i class="icon ion-ios-mail-outline"></i></div>
            <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">
            <div class="form-group"><input class="form-control" type="text" name="name" placeholder="Your Name" required></div>
            <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Your Email" required></div>
            <div class="form-group"><input class="form-control" type="text" name="subject" placeholder="Subject" required></div>
            <div class="form-group"><textarea class="form-control" name="message" placeholder="Your Message" rows="5" required></textarea></div>
            <div class="form-group"><button class="btn btn-primary btn-block" type="submit">Send Message</button></div>
        </form>
        <div class="alert-container">
            <?php if ($contactError) echo "<div class='alert alert-danger'>$contactError</div>"; ?>
            <?php if ($contactSuccess) echo "<div class='alert alert-success'>$contactSuccess</div>"; ?>
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
                       <a 
  href="https://mail.google.com/mail/?view=cm&fs=1&to=adhdsociety@yahoo.com" 
  style="color: white;" 
  target="_blank"

  adhdsociety@yahoo.com
</a><br>

                        09053906451
                    </address>
                </div>
                <div class="col-md-6 text-center text-md-right">
                    <a class="social-link" href="https://www.facebook.com/ADHDSOCPHILS/" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-facebook-square"></i></a>
                    <a class="social-link" href="https://x.com/adhdph" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-twitter-square"></i></a>
                    <a class="social-link" href="https://www.instagram.com/adhdsocph/" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-instagram-square"></i></a>
                    <a class="social-link" href="https://www.linkedin.com/company/adhdsocphils" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-linkedin"></i></a>
                    <a class="social-link" href="https://www.youtube.com/channel/UCKSnRxYSA4kf1Dnbydi2_mw" style="color: white; margin-right: 10px;" target="_blank"><i class="fa fa-youtube-square"></i></a>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6 text-center text-md-left">
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSeaVc3RhWL1HnLDnMihI_0KyB5FM6YG9imNcMCxvsnycILQTQ/viewform" style="color: white;" target="_blank">Be a Member</a>
                </div>
                <div class="col-md-6 text-center text-md-right">
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSd4Sf9BfC7kB4eBLRY-MRWYYhBkuRzKahNvQTNxebc12Gg0kQ/viewform" style="color: white;" target="_blank">Join our Free Online Support Groups</a> | 
                    <a href="https://www.facebook.com/groups/119000601519017" style="color: white;" target="_blank">Join our Facebook Group</a> | 
                    <a href="https://x.com/adhdph" style="color: white;" target="_blank">Follow</a>
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
</body>
</html>
