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
    <title>Home</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="index.css">
    <style>
        /* Background gradient */
        body {
            background: linear-gradient(to bottom, #2d8dbd, #0b5d8e);
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header styling with new color */
        .header-dark {
            background-color: #1e3240;
            padding: 10px 0;
        }

        /* Main section */
        main {
            padding: 20px 0;
            flex: 1;
        }

        /* Carousel styling */
        .carousel-item {
            height: 500px;
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }

        /* Separate caption container */
        .carousel-captions-container {
            padding: 20px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            border-radius: 10px;
            max-width: 90%;
            margin: 20px auto;
            text-align: center;
        }

        /* Footer styling */
        footer {
            background-color: #000;
            color: #fff;
            padding: 20px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .carousel-item {
                height: 300px;
            }

            .carousel-captions-container h5 {
                font-size: 16px;
            }

            .carousel-captions-container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <header class="header-dark">
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
    </header>

    <main>
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <?php
                require_once '../dbconnection.php';
                $sql = "SELECT url, description FROM images";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $i = 0;
                    while ($row = $result->fetch_assoc()) {
                        $activeClass = $i === 0 ? 'active' : '';
                        echo '<li data-target="#carouselExampleIndicators" data-slide-to="' . $i . '" class="' . $activeClass . '"></li>';
                        $i++;
                    }
                } else {
                    echo "<li data-target='#carouselExampleIndicators' data-slide-to='0' class='active'></li>";
                }
                ?>
            </ol>
            <div class="carousel-inner">
                <?php
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $i = 0;
                    while ($row = $result->fetch_assoc()) {
                        $activeClass = $i === 0 ? 'active' : '';
                        echo '<div class="carousel-item ' . $activeClass . '" style="background-image: url(' . $row["url"] . ');" data-description="' . $row["description"] . '">';
                        echo '</div>';
                        $i++;
                    }
                } else {
                    echo '<div class="carousel-item active" style="background-image: url(images/default.jpeg);" data-description="No images found">';
                    echo '</div>';
                }
                $conn->close();
                ?>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>

        <!-- Captions container outside carousel -->
        <div class="carousel-captions-container">
            <h5 id="carousel-caption">No images found</h5>
        </div>
    </main>

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
    <script>
        $(document).ready(function() {
            // Set initial caption
            updateCaption();
    
            // Update caption on carousel slide event
            $('#carouselExampleIndicators').on('slid.bs.carousel', function () {
                updateCaption();
            });
    
            function updateCaption() {
                var activeItem = $('.carousel-item.active');
                var caption = activeItem.data('description'); // Get the description
    
                if (caption) {
                    $('#carousel-caption').text(caption); // Update caption text
                    $('.carousel-captions-container').show(); // Show the caption container
                } else {
                    $('.carousel-captions-container').hide(); // Hide the caption container if no description
                }
            }
        });
    </script>
</body>
</html>