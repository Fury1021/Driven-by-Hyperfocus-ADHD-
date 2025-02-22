<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

require_once '../dbconnection.php';

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
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="about_us.css"> <!-- Include your custom CSS for logged-in about_us page -->
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
        }
        .about-section {
            padding: 30px 15px; /* Add padding for small screens */
        }
        .about-section h1 {
            font-size: 2.5rem; /* Responsive font size */
            margin-bottom: 20px;
        }
        .about-section p {
            font-size: 1.1rem; /* Adjust paragraph font size for readability */
            line-height: 1.6; /* Increase line height for better readability */
        }
        @media (max-width: 768px) {     
            .about-section h1 {
                font-size: 1.8rem; /* Smaller header on mobile */
            }
            .about-section p {
                font-size: 1rem; /* Smaller paragraph on mobile */
            }
        }

        header {
            position: fixed; /* Fix the header position */
            top: 0; /* Align to the top */
            width: 100%; /* Full width */
            z-index: 1000; /* Ensure it stays above other elements */
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
        .navbar-nav {
            white-space: nowrap; /* Prevent wrapping to a new line */
        }
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .about-section {
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        .about-section .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .about-section h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            text-align: center;
        }
        .about-section p {
            font-size: 1.1em;
            line-height: 1.8;
            margin-bottom: 15px;
            text-align: justify;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .about-section h1 {
                font-size: 2em;
            }
            .about-section p {
                font-size: 1em;
                line-height: 1.6;
            }
        }

        @media (max-width: 480px) {
            .about-section {
                padding: 10px;
            }
            .about-section h1 {
                font-size: 1.5em;
            }
            .about-section p {
                font-size: 0.9em;
                line-height: 1.5;
            }
        }
    </style>
</head>
<header>

<body>
    <?php include('loader.html'); ?>
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search" style="background-color: #212529;">
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
        <div class="about-section">
            <div class="container">
                <h1>About Us</h1>
                <p>
    The AD/HD Society of the Philippines, established in 2000, is a non-profit organization dedicated to supporting individuals with Attention Deficit/Hyperactivity Disorder (ADHD).
</p>

<p>
    Initially formed as a parent-based group, the Society has since evolved into a diverse and balanced collective of medical practitioners, academicians, Special Education (SPED) professionals, parents, and adults with ADHD.
</p>

<p>
    Our mission is to empower these individuals and their families, fostering a robust network of stakeholders to advocate for their needs. We strive to be an accessible, compassionate, and responsive organization, promoting the well-being of persons with ADHD through various programs, projects, and activities.
</p>

<p>
    Our notable achievements include raising ADHD awareness in the Philippines and emphasizing the significance of early identification and treatment. Our steadfast commitment to the welfare of individuals with ADHD propels our continuous efforts to ensure that everyone with this condition can access the necessary support and resources.
</p>

                
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

    <!-- jQuery and Bootstrap JS should be loaded in the correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
</body>

</html>
