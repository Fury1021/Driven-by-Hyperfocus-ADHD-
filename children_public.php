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

// Specify the ID to fetch
$topic_id = 4;

// Fetch image URLs and descriptions based on ID
$sql = "SELECT url, description FROM topics WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize variables to store fetched data
$topic_url = '';
$topic_description = 'No description available'; // Default value

// Check if data was fetched successfully
if ($result->num_rows > 0) {
    // Output data of the first row
    $row = $result->fetch_assoc();
    $topic_url = $row["url"];
    // Sanitize and format the description for HTML output
    $topic_description = nl2br(htmlspecialchars($row["description"]));
} else {
    echo "0 results";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADHD in Children</title>
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
    </style>
</head>
<header>
   
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
</header>
    <style>
    /* About Section */
    .about-section {
        background-color: #1A242F;
        padding: 60px 0;
        border-radius: 10px;
        display: flex;
        justify-content: center;
    }

    /* Card Style */
    .about-card {
        display: flex;
        background-color: #2C3E50;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        padding: 20px;
        max-width: 1200px;
        width: 100%;
    }

    /* Image Styling */
    .about-image img {
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        width: 100%;
        height: auto;
        transition: transform 0.3s;
    }

    .about-image img:hover {
        transform: scale(1.05); /* Zoom effect on hover */
    }

    /* Content Styling */
    .about-content {
        padding: 30px;
        color: #ECF0F1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }

    .about-content h2 {
        font-size: 24px;
        margin-bottom: 15px;
        color: #ECF0F1;
    }

    /* Automatic Paragraph Formatting */
    .about-content p {
        line-height: 1.6;
        font-size: 16px;
        color: #BDC3C7;
        text-align: justify; /* Justify text for cleaner look */
        margin-bottom: 1.5em; /* Add space between paragraphs */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .about-card {
            flex-direction: column; /* Stack image and content */
        }

        .about-section {
            padding: 30px 15px;
        }

        .about-content {
            padding: 20px;
        }

        .about-content h2 {
            font-size: 20px;
        }
    }
</style>


<section class="about-section py-5">
    <div class="about-card">
        <!-- Image Column -->
        <div class="col-lg-5 col-md-5 mb-4"> 
            <div class="about-image">
                <img src="<?php echo $topic_url; ?>" class="img-fluid" alt="About Image">
            </div>
        </div>
        <!-- Content Column -->
        <div class="col-lg-7 col-md-7 d-flex align-items-center"> 
            <div class="about-content text-white w-100">
                <h2>About This Topic</h2> <!-- Section Title -->
                <p><?php echo $topic_description; ?></p>
            </div>
        </div>
    </div>
</section>







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

