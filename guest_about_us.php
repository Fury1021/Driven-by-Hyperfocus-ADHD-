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
    <link rel="stylesheet" href="about_us.css">
     <style>
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

<body>
    <div class="header-blue">
        <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search" style="background-color: #212529;">
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
                        </ul>

                    <span class="navbar-text mr-2">
                        <a href="user_login.php" class="login">Log In</a>
                    </span>
                    <a class="btn btn-light action-button" role="button" href="sign_up.php">Sign Up</a>
                </div>
            </div>
        </nav>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
