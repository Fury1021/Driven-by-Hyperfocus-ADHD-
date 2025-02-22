<?php
require_once '../dbconnection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query to get weekly survey results with recommendations for the logged-in user
$query = "
    SELECT 
        YEAR(submission_date) AS year,
        MONTH(submission_date) AS month,
        WEEK(submission_date, 1) AS week,
        DATE(DATE_SUB(submission_date, INTERVAL WEEKDAY(submission_date) DAY)) AS week_start,  -- Start of the week (Monday)
        DATE(DATE_ADD(submission_date, INTERVAL (6 - WEEKDAY(submission_date)) DAY)) AS week_end, -- End of the week (Sunday)
        SUM(total_score) AS total_score,
        SUM(part_a_score) AS part_a_score,
        SUM(part_b_score) AS part_b_score,
        GROUP_CONCAT(recommendations SEPARATOR ', ') AS recommendations -- Concatenate recommendations
    FROM survey_results 
    WHERE user_id = ?
    GROUP BY year, month, week
    ORDER BY year DESC, month DESC, week DESC";


$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch interpretations
$interpretationQuery = "SELECT min_score, max_score, interpretation FROM interpretation";
$interpretations = [];
$interpretationResult = mysqli_query($conn, $interpretationQuery);
while ($row = mysqli_fetch_assoc($interpretationResult)) {
    $interpretations[] = $row;
}

$historyData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $year = $row['year'];
    $month = date('F', mktime(0, 0, 0, $row['month'], 1));
    $weekRange = date('F j', strtotime($row['week_start'])) . '-' . date('j', strtotime($row['week_end']));
    $totalScore = $row['total_score'];
    $partAScore = $row['part_a_score'];
    $partBScore = $row['part_b_score'];
    $recommendations = $row['recommendations']; // Include recommendations

    $interpretation = "No Interpretation Available";
    foreach ($interpretations as $entry) {
        if ($totalScore >= $entry['min_score'] && $totalScore <= $entry['max_score']) {
            $interpretation = $entry['interpretation'];
            break;
        }
    }

    $historyData[$year][$month][$weekRange] = [
        'total_score' => $totalScore,
        'part_a_score' => $partAScore,
        'part_b_score' => $partBScore,
        'interpretation' => $interpretation,
        'recommendations' => $recommendations, // Add to history data
    ];
}


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
    <title>Survey Submission Successful</title>
     <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
    <style>
        /* Collapsible styling */
        .collapsible {
            margin: 10px 0;
        }

        .collapsible-button {
            background-color: #f1f1f1;
            color: black;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
        }

        .collapsible-button.active, .collapsible-button:hover {
            background-color: #ccc;
        }

        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #f9f9f9;
        }

        .styled-button {
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .styled-button:hover {
            background-color: #0056b3; /* Darker shade on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }

        .styled-button:active {
            transform: scale(0.95); /* Slightly shrink button on click */
        }
        .back-button {
            background-color: #6c757d; /* Bootstrap secondary color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px; /* Add some space above the button */
        }

        .back-button:hover {
            background-color: #5a6268; /* Darker shade on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }

        .back-button:active {
            transform: scale(0.95); /* Slightly shrink button on click */
        }

    </style>
</head>
<body>
<header>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_profile.php">My Profile <i class="fa fa-user-circle"></i></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More Info</a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="adhd_public.php">What is ADHD?</a>
                                    <a class="dropdown-item" href="brain_public.php">The ADHD Brain</a>
                                    <a class="dropdown-item" href="symptoms_public.php">ADHD Symptoms</a>
                                    <a class="dropdown-item" href="children_public.php">ADHD in Children</a>
                                    <a class="dropdown-item" href="adhd_public.php">ADHD in Adults</a>
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
                </ul>

            </div>
        </div>
    </nav>
</header>

<div class="container mt-4">
    <h1>Survey History</h1>

    <?php if (empty($historyData)): ?>
        <!-- Display message if there is no survey history -->
        <p>You have no records or no records found.</p>
    <?php else: ?>
        <?php foreach ($historyData as $year => $months): ?>
            <div class="collapsible mb-3">
                <button class="collapsible-button btn btn-primary btn-block"><?php echo $year; ?></button>
                <div class="content">
                    <?php foreach ($months as $month => $weeks): ?>
                        <div class="collapsible mb-3">
                            <button class="collapsible-button btn btn-secondary btn-block"><?php echo $month; ?></button>
                            <div class="content">
                                <br>
                                <!-- Add the print button once per month -->
                                <button class="styled-button" onclick="window.location.href='print.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&score=<?php echo array_sum(array_column($weeks, 'total_score')); ?>&interpretation=<?php echo urlencode('Month Summary'); ?>'">
                                    Print Monthly Summary
                                </button>
                                <?php foreach ($weeks as $weekRange => $data): ?>
                                    <div class="collapsible mb-3">
                                        <button class="collapsible-button btn btn-info btn-block"><?php echo $weekRange; ?></button>
                                        <div class="content">
                                            <ul class="list-unstyled">
                                                <li>Part A score: <?php echo $data['part_a_score']; ?></li>
                                                <li>Part B score: <?php echo $data['part_b_score']; ?></li>
                                                <li>Total Score: <?php echo $data['total_score']; ?></li>
                                                <li>Interpretation: <?php echo $data['interpretation']; ?></li>
                                                <li>
                                                    Recommendations:
                                                    <?php if (!empty($data['recommendations'])): ?>
                                                        <?php 
                                                            // Decode the JSON string into an array
                                                            $recommendationsArray = json_decode($data['recommendations'], true); 
                                                        ?>
                                                        <?php if (is_array($recommendationsArray)): ?>
                                                            <ul>
                                                                <?php foreach ($recommendationsArray as $recommendation): ?>
                                                                    <li><?php echo htmlspecialchars(trim($recommendation)); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            Invalid recommendations format.
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        No recommendations available.
                                                    <?php endif; ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="text-center">
            <button class="back-button" onclick="window.history.back();">Back</button>
        </div>
        <br>
        <br>
<script>
    // Collapsible functionality
    const coll = document.getElementsByClassName("collapsible-button");
    for (let i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
            this.classList.toggle("active");
            const content = this.nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    }
</script>

<!-- Include the footer -->
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

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>