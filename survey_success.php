<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Include the database connection
include 'dbconnection.php';

// Fetch the user's latest survey results from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT part_a_score, part_b_score, total_score FROM survey_results WHERE user_id = ? ORDER BY submission_date DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$survey_result = $result->fetch_assoc();

if ($survey_result) {
    $part_a_score = $survey_result['part_a_score'];
    $part_b_score = $survey_result['part_b_score'];
    $total_score = $survey_result['total_score'];
    
    // Fetch the interpretation based on the total score
    $interpretation_query = "SELECT interpretation FROM interpretation WHERE ? BETWEEN min_score AND max_score";
    $interp_stmt = $conn->prepare($interpretation_query);
    $interp_stmt->bind_param("i", $total_score);
    $interp_stmt->execute();
    $interp_result = $interp_stmt->get_result();
    $interpretation = $interp_result->fetch_assoc()['interpretation'] ?? 'No interpretation available.';
    
    // Determine the type of recommendation based on the total score
    $type = ($total_score >= 4) ? 'with_adhd' : 'without_adhd';
    
    // Fetch 5 random recommendations based on the score range and type
    $recommendation_query = "
        SELECT recommendation 
        FROM recommendations 
        WHERE ? BETWEEN min_score AND max_score 
        AND type = ? 
        ORDER BY RAND() 
        LIMIT 5";
    
    $rec_stmt = $conn->prepare($recommendation_query);
    $rec_stmt->bind_param("is", $total_score, $type);
    $rec_stmt->execute();
    $rec_result = $rec_stmt->get_result();
    $recommendations = $rec_result->fetch_all(MYSQLI_ASSOC);
    
    // Convert the recommendations array to a JSON-encoded string
    $recommendations_json = json_encode(array_column($recommendations, 'recommendation'));
    
    // Update the survey_results table to save the recommendations
    $update_query = "UPDATE survey_results SET recommendations = ? WHERE user_id = ? AND submission_date = (SELECT MAX(submission_date) FROM survey_results WHERE user_id = ?)";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sii", $recommendations_json, $user_id, $user_id);
    $update_stmt->execute();

    
} else {
    // If no survey results found
    $part_a_score = $part_b_score = $total_score = 0;
    $interpretation = 'No survey results found.';
    $recommendations = [];
}
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
        body {
            background-color: #f8f9fa; /* Light background color */
        }

        .resultcontainer {
            max-width: 600px; /* Adjust this value to make the container smaller or larger */
            margin: 30px auto; /* Center the container */
            padding: 15px; /* Reduce padding for a smaller appearance */
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff; /* Primary color */
        }

        .success-message {
            color: green;
        }

        footer {
            background-color: black; /* Footer background color */
            color: white; /* Footer text color */
            padding: 20px 0; /* Padding for the footer */
        }
        
        .btn-custom {
            background-color: #6c757d; /* Grey color */
            color: white; /* Button text color */
            border: none; /* Remove border */
        }

        .btn-custom:hover {
            color: white; /* Font color on hover */
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
                    <li class="nav-item"><a class="nav-link active" href="screening_questionnaire.php">Productivity</a></li>
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
                </ul>
                <span class="navbar-text">
                    <a href="user_logout.php" class="logout">Log Out</a>
                </span>
            </div>
        </div>
    </nav>
</header>

<div class="resultcontainer">
    <h1 class="success-message">Survey Submitted Successfully!</h1>
    <p>Thank you for completing the ADHD Screening Questionnaire.</p>
    
    <h3>Your Results:</h3>
    <ul>
        <li><strong>Part A Score:</strong> <?php echo htmlspecialchars($part_a_score); ?></li>
        <li><strong>Part B Score:</strong> <?php echo htmlspecialchars($part_b_score); ?></li>
        <li><strong>Total Score:</strong> <?php echo htmlspecialchars($total_score); ?></li>
    </ul>
    
    <h3>Your Interpretation:</h3>
    <p><?php echo htmlspecialchars($interpretation); ?></p>
    
    <h3>Recommended Activities:</h3>
    <ul>
        <?php
        // Decode JSON recommendations
        $decoded_recommendations = json_decode($recommendations_json, true);
        if ($decoded_recommendations) {
            foreach ($decoded_recommendations as $rec) {
                echo '<li>' . htmlspecialchars($rec) . '</li>';
            }
        } else {
            echo '<li>No recommendations available.</li>';
        }
        ?>
    </ul>

    
    <p>Please consult with a healthcare provider for further assessment and interpretation of your scores.</p>
    
    <a href="screening_questionnaire.php" class="btn btn-custom">Back</a>
    <a href="survey_history.php" class="btn btn-primary">View History</a>

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

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>
