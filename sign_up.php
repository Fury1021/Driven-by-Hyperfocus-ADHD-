<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

// Check if a regular user is logged in
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

// Include the database connection
require_once '../dbconnection.php';

// Initialize the error message variable
$signupError = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input from the form
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_initial = htmlspecialchars(trim($_POST['middle_initial']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    // File upload logic
    $profile_pic = $_FILES['profile_pic']['name'];
    $target_dir = "ProfilePic/";  // Use the ProfilePic directory
    $imageFileType = strtolower(pathinfo($profile_pic, PATHINFO_EXTENSION));
    
    // Generate a unique file name to avoid name conflicts and PHP file uploads
    $new_file_name = uniqid('profile_', true) . '.' . $imageFileType;
    $target_file = $target_dir . $new_file_name;

    // Check if image file is a valid image by MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
    if (!in_array($file_mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
        $signupError = '<div class="alert alert-danger">The uploaded file is not a valid image.</div>';
        finfo_close($finfo);
        exit;
    }
    finfo_close($finfo);

    // Validate input fields
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $signupError = '<div class="alert alert-danger">All fields are required.</div>';
    } elseif ($password !== $confirm_password) {
        $signupError = '<div class="alert alert-danger">Passwords do not match.</div>';
    } else {
        // Check if the email is already registered
        $sql = "SELECT Email FROM users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $signupError = '<div class="alert alert-danger">This email is already registered.</div>';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Move the uploaded file to the server
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                // Insert new user into the database with profile picture
                $sql = "INSERT INTO users (first_name, middle_initial, last_name, Username, Email, Password, PhoneNumber, ProfilePic) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die('Prepare failed: ' . $conn->error);
                }
                $stmt->bind_param("ssssssss", $first_name, $middle_initial, $last_name, $username, $email, $hashed_password, $phone, $target_file);

                if ($stmt->execute()) {
                    // Send confirmation email using the built-in mail() function
                    $to = $email;  // Recipient email
                    $subject = 'Account Creation Confirmation';
                    $message = '
                        <p>Dear ' . $first_name . ' ' . $last_name . ',</p>
                        <p>Welcome to Driven By Hyperfocus! Your account has now been created, and you are now a member of the Driven by Hyperfocus family!</p>
                        <p>We are glad to welcome you to our community of dedicated individuals. As a member, you have access to a set of tools designed to help you stay focused and productive.</p>
                        <p>Thank you for selecting Driven By Hyperfocus. We are passionate to see what you can accomplish with us by your side.</p>
                        <p>Best Regards, Driven by Hyperfocus Team</p>
                    ';
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: Driven By Hyperfocus <drivenbyhyperfocus@gmail.com>" . "\r\n";
                    $headers .= "Reply-To: $email" . "\r\n";

                    // Send the email
                    if (mail($to, $subject, $message, $headers)) {
                        $_SESSION['user_id'] = $stmt->insert_id; // Automatically log the user in
                        header("Location: user_dashboard.php");
                        exit;
                    } else {
                        $signupError = '<div class="alert alert-danger">Failed to send the confirmation email. Please try again later.</div>';
                    }
                } else {
                    $signupError = '<div class="alert alert-danger">Registration failed. Please try again.</div>';
                }
            } else {
                $signupError = '<div class="alert alert-danger">Sorry, there was an error uploading your file.</div>';
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="icon" href="images/sitelogo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href=".css">
    <style>
        /* Ensure the body and html take the full height */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        /* Main content container */
        .login-dark {
            flex: 1; /* Fills the remaining space between the header and footer */
            padding: 100px 20px; /* Adjust padding to prevent overlap with header */
            background: linear-gradient(to bottom, #2596be, #475d62);
        }

        .header-blue {
            background-color: rgba(30, 40, 51, 0.9);
            padding: 10px 0;
            color: white;
            z-index: 1000;
            position: relative;
        }

        .terms-container {
            max-height: 150px;
            overflow-y: scroll;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Footer stays at the bottom when the content is smaller */
        .footer {
            background-color: black;
            color: white;
            padding: 20px;
            margin-top: auto;
        }

        /* Adjust form styles */
        form {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Buttons and inputs should have proper spacing */
        .form-control {
            margin-bottom: 15px;
        }

        /* Adjust the logo */
        .navbar-brand img {
            height: 50px;
        }
        /* Responsive styles */
@media (max-width: 768px) {
    .login-dark {
        padding: 50px 10px; /* Reduce padding for smaller screens */
    }

    form {
        padding: 15px; /* Less padding for form on mobile */
    }

    .form-control {
        margin-bottom: 10px; /* Less margin for inputs on mobile */
    }

    .header-blue {
        text-align: center; /* Center header content on mobile */
    }

    .navbar-nav {
        flex-direction: column; /* Stack navigation items vertically on mobile */
        align-items: center; /* Center align items */
    }
}

@media (max-width: 480px) {
    .login-dark {
        padding: 30px 5px; /* Further reduce padding for very small screens */
    }

    form {
        max-width: 100%; /* Allow form to take up full width on mobile */
    }
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
                        <li class="nav-item"><a class="nav-link active" href="guest_about_us.php">About Us</a></li>
                        <li class="nav-item"><a class="nav-link active" href="public_contact.php">Contact Us</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                More Info
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">First Item</a>
                                <a class="dropdown-item" href="#">Second Item</a>
                                <a class="dropdown-item" href="#">Third Item</a>
                            </div>
                        </li>
                    </ul>
                    <span class="navbar-text mr-2"><a href="user_login.php" class="login">Log In</a></span>
                    <a class="btn btn-light action-button" role="button" href="sign_up.php">Sign Up</a>
                </div>
            </div>
        </nav>
    </div>

   <!-- Sign Up Form -->
<div class="login-dark">
    <form method="post" action="" enctype="multipart/form-data"> <!-- Added enctype -->
        <h2 class="sr-only">Sign Up Form</h2>
        <div class="illustration"><i class="icon ion-ios-person-add-outline"></i></div>
        <div class="form-group">
            <input class="form-control" type="text" name="first_name" placeholder="First Name" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="middle_initial" placeholder="Middle Initial">
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="last_name" placeholder="Last Name" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <div class="form-group">
            <input class="form-control" type="tel" name="phone" placeholder="Phone Number">
        </div>
        <div class="form-group">
            <input class="form-control" type="file" name="profile_pic" accept="image/*" required>
            <label for="profile_pic">Upload Profile Picture</label>
        </div>

        <div class="terms-container">
            <h3>Terms and Agreement</h3>
            <p>By using this site, you agree to abide by the following terms and conditions.</p>
            <p>1. Acceptance of Terms: By accessing or using our services, you agree to these terms and any additional terms applicable to specific areas of the website.</p>
            <p>2. Registration and Account Security: Users must provide accurate and complete information during the registration process. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account.</p>
            <p>3. Use of the Website: You agree to use the website for lawful purposes only and not to engage in any activity that could harm the website or its users.</p>
            <p>4. Privacy Policy: Our Privacy Policy describes how we handle your personal information. By using our website, you consent to the collection and use of your data as described in the Privacy Policy.</p>
            <p>5. Intellectual Property: All content on the website, including text, graphics, logos, and software, is the property of Driven by Hyperfocus or its content suppliers and is protected by intellectual property laws.</p>
            <p>6. Self-Report Questionnaire: Our website includes a self-report questionnaire. This feature is not intended to serve as a self-diagnosis tool and will be used solely as a basis for healthcare professionals to assist in their assessments.</p>
            <p>7. Limitation of Liability: Driven by Hyperfocus is not liable for any direct, indirect, incidental, or consequential damages arising from the use or inability to use the website.</p>
            <p>8. Termination: We reserve the right to terminate or suspend your account and access to the website without notice for conduct that we believe violates these terms or is harmful to other users.</p>
            <p>9. Changes to Terms: We may update these terms from time to time. Your continued use of the website after changes are made constitutes acceptance of the new terms.</p>
        </div>

        <!-- Checkbox to Agree to Terms and Agreement -->
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="agreeCheckbox" onchange="toggleSubmitBtn()">
            <label class="form-check-label" for="agreeCheckbox">I agree with the terms and agreements</label>
        </div>

        <div class="form-group">
            <button id="submitBtn" class="btn btn-primary btn-block" type="submit" disabled>Sign Up</button> <!-- Initially disabled -->
        </div>

        <?php if ($signupError != "") echo $signupError; ?>
        <a href="user_login.php" class="forgot">Already have an account? Log in</a>
    </form>
</div>

<!-- Footer Section -->
<footer class="footer mt-auto py-3" style="background-color: black; color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-left">
                <h4>ADHD Society of the Philippines</h4>
                <address style="margin-top: 10px;">
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

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // Enable the submit button only when the checkbox is checked
        const agreeCheckbox = document.getElementById("agreeCheckbox");
        const submitBtn = document.getElementById("submitBtn");

        agreeCheckbox.addEventListener("change", function() {
            submitBtn.disabled = !this.checked;
        });
    </script>
</body>
</html>
