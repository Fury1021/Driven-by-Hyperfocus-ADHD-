<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: user_login.php");
    exit;
}

// Database connection details
require_once '../dbconnection.php';

$sql = "SELECT url, description FROM images";
$result = $conn->query($sql);

// Get the logged-in user's profile picture
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
    <?php include('loader.html'); ?>
    <header class="header-dark">
        <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
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

    <main>
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <?php
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
        
        window.onload = function() {
            const loader = document.getElementById('loader');
            const content = document.getElementById('content');

            // Hide loader and show content after a brief delay
            setTimeout(function() {
                loader.style.display = 'none';
                content.style.display = 'block';
            }, 2000); // Adjust the time to how long you want the loader to stay
        };
    </script>
</body>
</html>
