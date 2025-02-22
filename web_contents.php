<?php
session_start(); // Start the session

require_once '../dbconnection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Function to log admin actions securely
function logAdminAction($admin_id, $description, $conn) {
    $dateandtime = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, description, dateandtime) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $description, $dateandtime);
    $stmt->execute();
}

// Fetch all images from the images table securely
$imageQuery = "SELECT * FROM images";
$imageResult = $conn->query($imageQuery);

// Fetch interpretations from the interpretation table securely
$interpretationQuery = "SELECT * FROM interpretation";
$interpretationResult = $conn->query($interpretationQuery);

// Fetch topics from the topics table securely
$topicQuery = "SELECT * FROM topics";
$topicResult = $conn->query($topicQuery);

// Fetch survey questions from the survey_questions table securely
$surveyQuestionQuery = "SELECT * FROM survey_questions";
$surveyQuestionResult = $conn->query($surveyQuestionQuery);

// Handle the update of images, interpretations, topics, or survey questions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $table = htmlspecialchars($_POST['table'], ENT_QUOTES, 'UTF-8'); // Determine which table is being updated
    $admin_id = $_SESSION['admin_id']; // Get admin ID from session

    // Handle file upload for images and topics
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image_url']['tmp_name']);

        if (in_array($fileType, $allowedTypes)) {
            $targetDir = 'images/'; // General images directory
            $fileName = basename($_FILES['image_url']['name']);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['image_url']['tmp_name'], $targetFilePath)) {
                // Update the appropriate table based on the type
                if ($table === 'images') {
                    $stmt = $conn->prepare("UPDATE images SET url = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $targetFilePath, $description, $id);
                    if ($stmt->execute()) {
                        logAdminAction($admin_id, "Updated photo in Manage Images #$id", $conn);
                    } else {
                        echo "Error updating image: " . $stmt->error;
                    }
                } elseif ($table === 'topics') {
                    $stmt = $conn->prepare("UPDATE topics SET url = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $targetFilePath, $description, $id);
                    if ($stmt->execute()) {
                        logAdminAction($admin_id, "Updated photo in Topics #$id", $conn);
                    } else {
                        echo "Error updating topic image: " . $stmt->error;
                    }
                }
            } else {
                echo "Error uploading file.";
                exit;
            }
        } else {
            echo "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
            exit;
        }
    } else {
        // Log updated description for images, interpretations, topics, and survey questions
        if ($table === 'images') {
            $stmt = $conn->prepare("UPDATE images SET description = ? WHERE id = ?");
            $stmt->bind_param("si", $description, $id);
            if ($stmt->execute()) {
                logAdminAction($admin_id, "Updated the description in Manage Images #$id", $conn);
            } else {
                echo "Error updating description: " . $stmt->error;
            }
        } elseif ($table === 'interpretation') {
            $stmt = $conn->prepare("UPDATE interpretation SET interpretation = ? WHERE id = ?");
            $stmt->bind_param("si", $description, $id);
            if ($stmt->execute()) {
                logAdminAction($admin_id, "Updated the interpretation ID #$id", $conn);
            } else {
                echo "Error updating interpretation: " . $stmt->error;
            }
        } elseif ($table === 'topics') {
            $stmt = $conn->prepare("UPDATE topics SET description = ? WHERE id = ?");
            $stmt->bind_param("si", $description, $id);
            if ($stmt->execute()) {
                logAdminAction($admin_id, "Updated the description in Topics #$id", $conn);
            } else {
                echo "Error updating topics: " . $stmt->error;
            }
        } elseif ($table === 'survey_questions') {
            $stmt = $conn->prepare("UPDATE survey_questions SET question_text = ? WHERE question_id = ?");
            $stmt->bind_param("si", $description, $id);
            if ($stmt->execute()) {
                logAdminAction($admin_id, "Updated the Survey Question in Question #$id", $conn);
            } else {
                echo "Error updating survey questions: " . $stmt->error;
            }
        }
    }

    header("Location: web_contents.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Image Management</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="patients.css"> <!-- Dashboard-specific CSS -->
<style>
    body {
    background: linear-gradient(to bottom, #2596be, #475d62);
    color: white;
    font-family: 'Roboto', sans-serif;
    min-height: 100vh;
    margin: 0;
    display: flex;
    flex-direction: column;
    }

    .container {
        flex: 1; /* Ensures that the content expands and pushes the footer down */
        max-width: 1200px;
        margin: 0 auto;
        background-color: rgba(0, 0, 0, 0.85);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }

    footer {
        background-color: #2c3e50;
        padding: 20px;
        color: white;
        text-align: center;
        width: 100%;
        position: relative; /* Changed from fixed to relative */
        bottom: 0;
    }

    @media (max-width: 768px) {
        footer {
            position: relative; /* Ensure it's relative on smaller screens */
            padding-bottom: 10px;
        }
    }

    .card {
            margin-bottom: 20px;
        }
        .modal-lg {
            max-width: 80%;
        }
        .edit-btn {
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .table thead {
                display: none;
            }
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                overflow: hidden;
            }
            .table td {
                text-align: right;
                padding: 10px;
                position: relative;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                text-align: left;
                font-weight: bold;
                color: #555;
            }
            .table td:last-child {
                text-align: center;
            }
        }
</style>
</head>
<body>

<!-- Navbar -->
<div class="header-blue">
    <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
        <a href="admin_dashboard.php"><img src="images/logo3.png" alt="Your Logo" style="height: 40px;"></a>
        <button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navcol-1">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="web_contents.php">Web Contents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_of_patients.php">Manage Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_logs.php">Admin Logs</a>
                </li>
            </ul>
            <span class="navbar-text mr-2">
                <a href="admin_logout.php" class="login">Logout</a>
            </span>
        </div>
    </nav>
</div>

<!-- Main content container -->
<div class="container">
    <h1 class="mb-4 text-center">Manage Images</h1>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($imageResult)): ?>
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-0 shadow-lg">
                <img src="<?php echo htmlspecialchars($row['url']); ?>" class="card-img-top img-thumbnail" alt="Image">
                <div class="card-body">
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editDescriptionModal<?php echo $row['id']; ?>">
                        View Description
                    </button>

                    <!-- Modal for viewing and editing description -->
                    <div class="modal fade" id="editDescriptionModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editDescriptionModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editDescriptionModalLabel">View and Edit Description</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" enctype="multipart/form-data" onsubmit="return confirmUpdate(event)">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="table" value="images"> <!-- Added hidden table field -->
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="image_url">Upload New Image (Optional)</label>
                                            <input type="file" class="form-control-file" name="image_url">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <h1 class="mb-4 text-center">Manage Interpretations</h1>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($interpretationResult)): ?>
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">Interpretation ID: <?php echo $row['id']; ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($row['interpretation']); ?></p>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editInterpretationModal<?php echo $row['id']; ?>">
                        Edit Interpretation
                    </button>

                    <!-- Modal for editing interpretation -->
                    <div class="modal fade" id="editInterpretationModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editInterpretationModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editInterpretationModalLabel">Edit Interpretation</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="table" value="interpretation"> <!-- Added hidden table field -->
                                        <div class="form-group">
                                            <label for="description">Interpretation</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($row['interpretation']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <h1 class="mb-4 text-center">Manage Topics</h1>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($topicResult)): ?>
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-0 shadow-lg">
                <img src="<?php echo htmlspecialchars($row['url']); ?>" class="card-img-top img-thumbnail" alt="Image">
                <div class="card-body">
                    <h5 class="card-title">Topic ID: <?php echo $row['id']; ?></h5>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editTopicModal<?php echo $row['id']; ?>">
                        Edit Topic
                    </button>

                    <!-- Modal for editing topic -->
                    <div class="modal fade" id="editTopicModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editTopicModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editTopicModalLabel">Edit Topic</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="table" value="topics"> <!-- Added hidden table field -->
                                        <div class="form-group">
                                            <label for="description">Topic Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="image_url">Upload New Image (Optional)</label>
                                            <input type="file" class="form-control-file" name="image_url">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <h1 class="mb-4 text-center">Manage Survey Questions</h1>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($surveyQuestionResult)): ?>
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">Question ID: <?php echo $row['question_id']; ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($row['question_text']); ?></p>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editSurveyQuestionModal<?php echo $row['question_id']; ?>">
                        Edit Question
                    </button>

                    <!-- Modal for editing survey question -->
                    <div class="modal fade" id="editSurveyQuestionModal<?php echo $row['question_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSurveyQuestionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editSurveyQuestionModalLabel">Edit Survey Question</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $row['question_id']; ?>">
                                        <input type="hidden" name="table" value="survey_questions"> <!-- Added hidden table field -->
                                        <div class="form-group">
                                            <label for="description">Survey Question</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($row['question_text']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function confirmUpdate(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save the changes?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit(); // Submit the form
            }
        });
    }
</script>
</body>
</html>
