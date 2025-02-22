<?php
session_start();
require_once '../dbconnection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get last Monday's date (correctly this time)
$lastMonday = date('Y-m-d', strtotime('last monday'));

// Check for existing submission this week
$query = "SELECT COUNT(*) FROM survey_results WHERE user_id = ? AND submission_date >= ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $lastMonday);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    $message = 'You can only take the survey once a week. Please try again next week.';
} else {
    $message = ''; // Clear the message if the user is allowed to take the survey
}

// Fetch the questions from the database
$query = "SELECT * FROM survey_questions";
$result = mysqli_query($conn, $query);

// Get all questions as an associative array
$questions = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Define the ranges for Part A and Part B
$partA_questions = array_slice($questions, 0, 6); // Questions 1-6
$partB_questions = array_slice($questions, 6, 12); // Questions 7-18 (6 questions)

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
    <title>Screening Questionnaire</title>
     <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link rel="stylesheet" href="task_manager.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background color for contrast */
        }

        .survey-container {
            background-color: #ffffff; /* White background for the container */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            padding: 30px; /* Padding inside the container */
            margin-top: 30px; /* Space above the container */
        }

        h1 {
            color: black; /* Set heading color to black */
        }

        h2 {
            color: #007bff; /* Set section title color */
            margin-top: 20px; /* Space above section titles */
        }

        table {
            width: 100%; /* Full width for the table */
            margin-bottom: 20px; /* Space below the table */
        }

        th, td {
            text-align: left; /* Align text to the left */
            color: black; /* Black text color */
            padding: 8px; /* Add padding for better spacing */
        }

        .message {
            color: black; /* Set message text color */
            margin-bottom: 20px; /* Space below the message */
            font-weight: bold; /* Bold text for visibility */
            background-color: beige; /* Soft orange background */
            padding: 15px; /* Add padding around the text */
            border-radius: 8px; /* Rounded corners */
            text-align: center; /* Center the text */
        }


        th {
            background-color: #f1f1f1; /* Light grey background for headers */
        }

        .submit-btn {
            background-color: #007BFF; /* Blue color for Survey History button */
            color: white;              /* Text color */
            padding: 10px 15px;       /* Adjust padding for the button */
            border: none;             /* Remove border */
            border-radius: 5px;      /* Rounded corners */
            text-decoration: none;    /* Remove underline */
            cursor: pointer;          /* Change cursor to pointer */
            display: inline-block;     /* Ensure it behaves like a button */
            margin-right: 10px;      /* Add space to the right */
        }

        .submit-btn.submit-survey { 
            background-color: #28a745; /* Green color for Submit Survey button */
        }

        .submit-btn:hover {
            background-color: #0056b3; /* Darker blue for hover effect on Survey History */
            color: white;               /* Ensure text stays white on hover */
            text-decoration: none;      /* Remove underline on hover */
        }

        .submit-btn.submit-survey:hover {
            background-color: #218838; /* Darker shade of green for hover effect */
        }   



        footer {
            background-color: black; /* Footer background color */
            color: white; /* Footer text color */
        }

        input[type="radio"] {
            transform: scale(1.5); /* Adjust this value to increase/decrease size */
            margin: 0 10px; /* Optional: add space around the radio buttons */
        }


    </style>
</head>
<body class="header-blue">
<?php include('loader.html'); ?>
<header>
    <style>
        header {
            position: fixed; /* Fix the header position */
            top: 0; /* Align to the top */
            width: 100%; /* Full width */
            z-index: 1000; /* Ensure it stays above other elements */
        }


        .navbar-nav .nav-item {
            display: inline-block; /* Keep items in one line */
        }

        .navbar-collapse {
            display: flex; /* Use flexbox for the navbar items */
            flex-wrap: nowrap; /* Prevent wrapping */
            align-items: center; /* Center items vertically */
        }

        .navbar img {
            max-height: 50px; /* Adjust logo size as needed */
            margin-right: 15px; /* Space between logo and nav items */
        }

        .navbar-text {
            margin-left: auto; /* Push log out link to the right */
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
        h3{
            color: black;
            text-align: center;
        }
        h5{
            color: black;
            text-align: left;
        }
        h6 {
            color: black;
            text-align: left;
            font-style: italic;
        }
        /* Adjust body to prevent overlap with fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on the height of your navbar */
        }
        .chart-wrapper {
        background-color: #ffffff; /* White background for a clean look */
        border-radius: 10px; /* Slightly more rounded corners */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); /* Softer and larger shadow for depth */
        padding: 30px; /* Increased padding for a more spacious feel */
        margin: 20px; /* Margin to separate from other elements */
        max-width: 600px; /* Set a maximum width for better readability */
        transition: transform 0.3s; /* Add a transition effect */
        }

        .chart-wrapper:hover {
            transform: translateY(-5px); /* Lift effect on hover */
        }
        
        #weekly-summary {
        background-color: #f8f9fa; /* Light background */
        border: 1px solid #dee2e6; /* Light border */
        }

        #weekly-summary h2 {
            font-family: Arial, sans-serif; /* Font family */
            font-weight: bold; /* Bold title */
            margin-bottom: 20px; /* Space below title */
            color: #007bff; /* Primary color for the title */
        }

        #weekly-summary p {
            margin: 10px 0; /* Space above and below paragraphs */
            font-family: Arial, sans-serif; /* Font family for paragraphs */
            color: #343a40; /* Darker text color */
            font-size: 1.2em; /* Larger font size */
        }
        .centered-container {
            display: flex;            /* Use flexbox to align items */
            justify-content: center;   /* Center items vertically */
            margin: 20px;            /* Optional: add some margin for spacing */
        }
        .table-responsive td:nth-child(2) { /* Selects the second column (Question) */
    text-align: left; /* Align text to the left */
}
    </style>
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
         <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
<div class="container survey-container">
    <h1>ADHD Screening Questionnaire</h1>
    <h5>Please answer the questions below, rating yourself on each of the criteria shown using the scale on the right side of the page. As you answer each question, click on the box that best describes how you have felt and conducted yourself.</h5>
    <h6>*This is a standardized questionnaire from "JB Schweitzer, et al. The Adult ADHD Self-Report Scale (ASRSv1.1). 85(3): Med Clin North Am. 757-777. 2001".</h6>

    <div id="disclaimer" class="disclaimer" style="display: none;">
        <p>Swipe or drag to view more options.</p>
    </div>

    <form action="process_survey.php" method="POST">
        <!-- Part A -->
        <div id="partA">
            <h2>Part A</h2>
            <p>Please answer the following questions about how often you have experienced the listed behaviors in the past 6 months. Mark one answer per row.</p>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Question</th>
                            <th>Never</th>
                            <th>Rarely</th>
                            <th>Sometimes</th>
                            <th>Often</th>
                            <th>Very Often</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partA_questions as $index => $question) { ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="0" required></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="0"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="centered-container">
                <button type="button" class="btn btn-primary" onclick="showPart('partB')">Next: Part B</button>
            </div>
            <div class="centered-container">
                <a href="survey_history.php" class="btn btn-info">Survey History</a>
            </div>
        </div>

        <!-- Part B -->
        <div id="partB" class="d-none">
            <h2>Part B</h2>
            <p>Now, answer the following questions about how often you have experienced the behaviors related to work or study performance in the past 6 months.</p>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Question</th>
                            <th>Never</th>
                            <th>Rarely</th>
                            <th>Sometimes</th>
                            <th>Often</th>
                            <th>Very Often</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partB_questions as $index => $question) { ?>
                            <tr>
                                <td><?php echo $index + 7; ?></td>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="0" required></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="0"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                                <td class="text-center"><input type="radio" name="q<?php echo $question['question_id']; ?>" value="1"></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="centered-container">
                <a href="survey_history.php" class="btn btn-info">Survey History</a>
            </div>
            <div class="centered-container">
                <button type="button" class="btn btn-secondary" onclick="showPart('partA')">Back to Part A</button>
                <button type="submit" class="btn btn-success">Submit Survey</button>
            </div>
        </div>
    </form>
</div>

<script>
    function showPart(part) {
        // Hide both parts first
        document.getElementById('partA').classList.add('d-none');
        document.getElementById('partB').classList.add('d-none');

        // Show the selected part
        document.getElementById(part).classList.remove('d-none');
    }

    // Check if the screen width requires showing the swipe or drag disclaimer
    if (window.innerWidth < 768) {
        document.getElementById('disclaimer').style.display = 'block';
    }
</script>

<style>
    /* Ensure the radio buttons and content are fully visible on smaller screens */
    .table {
        width: 100%;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .centered-container {
        text-align: center;
        margin: 20px 0; /* Adjust the margin as needed */
    }
</style>





<br><br>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Add SweetAlert2 script -->

<script>
    // Show SweetAlert on page load
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Important Notice',
            html: "<div style='text-align: justify;'>" +
                  "No response is correct or incorrect. If your score is high enough, the results will indicate that it may suggest to consult a health professional for ADHD. " +
                  "If it is low, there is little chance that you would meet the requirements for an ADHD diagnosis. " +
                  "Remember that this is neither a diagnostic tool nor a substitute for an extensive evaluation. This will only be used for preliminary analysis of your health professional." +
                  "</div>",
            icon: 'info',
            confirmButtonText: 'OK'
        });
    });

    function validateForm() {
        // Get all radio button groups
        const questions = document.querySelectorAll('input[type="radio"]');
        let answeredQuestions = new Set();

        // Loop through all radio buttons and check which questions have been answered
        questions.forEach(radio => {
            if (radio.checked) {
                answeredQuestions.add(radio.name); // Add the question name to the set
            }
        });

        // Check if all questions have been answered
        const totalQuestions = <?php echo count($questions); ?>; // Total questions from PHP
        if (answeredQuestions.size < totalQuestions) {
            // Use SweetAlert for validation alert
            Swal.fire({
                title: 'Validation Error',
                text: "Please answer all questions before submitting.",
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false; // Prevent form submission
        }
        
        return true; // Allow form submission if all questions are answered
    }
</script>


</body>
</html>

