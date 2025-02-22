<?php
session_start();
require_once '../dbconnection.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: hplogin.php");
    exit();
}

// Initialize search term
$searchTerm = '';

// Check if search form is submitted
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
}

// Prepare the SQL statement with a search condition
$stmt = $conn->prepare("
    SELECT UserID, username, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name, email 
    FROM users
    WHERE username LIKE ? OR CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) LIKE ? OR email LIKE ?
");
$searchWildcard = "%$searchTerm%"; // Add wildcards for searching
$stmt->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Professionals Homepage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="patients.css"> <!-- Dashboard-specific CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1; /* Makes the content area grow to fill the available space */
        }
        .header {
            background-color: #007BFF; /* Blue background for the header */
            color: white; /* White text color */
            padding: 10px; /* Padding for the header */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="header-blue">
        <nav class="navbar navbar-expand-md navbar-dark">
            <a class="navbar-brand" href="index.php"><img src="images/logo3.png" alt="Your Logo" height="40"></a>
            <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="hphomepage.php">Manage Users</a>
                    </li>
                </ul>
                <a href="admin_logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </nav>
    </div>

   <!-- Main Content -->
   <div class="content container mt-5" style="background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
       <h1 class="text-center">Welcome, <?php echo $_SESSION['username']; ?></h1>
       <h3 class="text-center">List of Patients</h3>

       <!-- Search Form -->
       <form method="POST" class="text-center mb-4">
           <input type="text" name="search" class="form-control" placeholder="Search by username, full name, or email" value="<?php echo htmlspecialchars($searchTerm); ?>">
           <button type="submit" class="btn btn-primary mt-2">Search</button>
       </form>

       <table class="table table-bordered">
           <thead>
               <tr>
                   <th>Username</th>
                   <th>Full Name</th>
                   <th>Email</th>
               </tr>
           </thead>
           <tbody>
               <?php while ($row = $result->fetch_assoc()): ?>
               <tr>
                   <td><a href="hpuserdetails.php?UserID=<?php echo $row['UserID']; ?>"><?php echo $row['username']; ?></a></td>
                   <td><?php echo $row['full_name']; ?></td>
                   <td><?php echo $row['email']; ?></td>
               </tr>
               <?php endwhile; ?>
           </tbody>
       </table>
       <br>
       <form method="POST" action="hplogout.php" class="text-center">
           <button type="submit" class="btn btn-danger">Logout</button>
       </form>
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
