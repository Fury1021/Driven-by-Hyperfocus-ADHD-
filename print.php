<?php
require_once('TCPDF/tcpdf.php'); // Include the TCPDF library
require_once '../dbconnection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the user's name from the database
$userQuery = "SELECT first_name, middle_initial, last_name FROM users WHERE UserID = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param('i', $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userName = $user['first_name'] . ' ' . ($user['middle_initial'] ? $user['middle_initial'] . '. ' : '') . $user['last_name'];

// Query to fetch the survey results for the user
$query = "
    SELECT 
        YEAR(submission_date) AS year,
        MONTH(submission_date) AS month,
        WEEK(submission_date, 1) AS week,
        DAY(submission_date) AS submission_day,  -- Extract day of submission
        MIN(DATE(submission_date)) AS week_start,
        MAX(DATE(submission_date)) AS week_end,
        SUM(total_score) AS total_score,
        SUM(part_a_score) AS part_a_score,
        SUM(part_b_score) AS part_b_score,
        recommendations
    FROM survey_results 
    WHERE user_id = ?  
    GROUP BY year, month, week
    ORDER BY year DESC, month DESC, week DESC"; 


$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id); // Bind the user_id parameter
$stmt->execute();
$result = $stmt->get_result(); // Fetch the result set

// Fetch interpretations
$interpretationQuery = "SELECT min_score, max_score, interpretation FROM interpretation";
$interpretations = [];
$interpretationResult = mysqli_query($conn, $interpretationQuery);
while ($row = mysqli_fetch_assoc($interpretationResult)) {
    $interpretations[] = $row;
}

// Fetch survey questions with categories
$questionsQuery = "
    SELECT question_text, category 
    FROM survey_questions"; // Assume 'category' column exists
$questionsResult = mysqli_query($conn, $questionsQuery);
$questions = [];
while ($row = mysqli_fetch_assoc($questionsResult)) {
    $questions[] = $row; // Store both question text and category
}

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Survey Results');
$pdf->SetSubject('Survey Interpretation');
$pdf->SetKeywords('TCPDF, PDF, survey, results');

// Remove header data
// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING); // Removed this line

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(0); // Set header margin to 0
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Add the logo image (make sure the path is correct)
$logoPath = 'images/isthelogo.png'; // Path to your logo
$pdf->Image($logoPath, 80, 10, 50, 0, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false); // Centered logo

// Set font
$pdf->SetFont('helvetica', '', 12);

// Prepare content for the PDF
$content = '<h3 style="color:#204484;">Survey Results</h3>';
$content .= '<p><strong>User:</strong> ' . htmlspecialchars($userName) . '</p>'; // Include user name
$content .= '<h4 style="color:#333;">Questions:</h4><ul style="text-align: justify;">'; // Add text-align: justify

// Organize questions by category
$questionsByCategory = [
    'Inattentive' => [],
    'Hyperactive/Impulsive' => []
];

foreach ($questions as $question) {
    if ($question['category'] === 'Inattentive') {
        $questionsByCategory['Inattentive'][] = htmlspecialchars($question['question_text']);
    } elseif ($question['category'] === 'Hyperactive/Impulsive') {
        $questionsByCategory['Hyperactive/Impulsive'][] = htmlspecialchars($question['question_text']);
    }
}

// Display questions by category
foreach ($questionsByCategory as $category => $questions) {
    $content .= '<li><strong>' . $category . ':</strong></li>';
    foreach ($questions as $q) {
        $content .= '<li style="text-align: justify;">&nbsp;&nbsp;&nbsp;' . $q . '</li>'; // Justified text
    }
}

$content .= '</ul>';

// Add content to PDF
$pdf->writeHTML($content, true, false, true, false, '');

// Add a new page for Survey History
$pdf->AddPage(); // This will start a new page

// Prepare content for Survey History
$surveyHistoryContent = '<h2>Survey History</h2>';

while ($row = mysqli_fetch_assoc($result)) {
    $totalScore = $row['total_score'];
    $partAScore = $row['part_a_score'];
    $partBScore = $row['part_b_score'];

    // Find interpretation based on the total score
    $interpretation = "No Interpretation Available"; // Default value
    foreach ($interpretations as $entry) {
        if ($totalScore >= $entry['min_score'] && $totalScore <= $entry['max_score']) {
            $interpretation = $entry['interpretation'];
            break;
        }
    }

    // Decode recommendations (if they exist)
    $recommendations = json_decode($row['recommendations'], true); // Decode JSON
    $recommendationsList = "";
    if (is_array($recommendations) && count($recommendations) > 0) {
    foreach ($recommendations as $recommendation) {
        $recommendationsList .= '<li style="text-align: justify;">' . htmlspecialchars($recommendation) . '</li>';
    }
} else {
    $recommendationsList = '<p>No recommendations available.</p>';
}

    
    // Add section for each result
    $surveyHistoryContent .= '<h3 style="color:#204484;">Year: ' . $row['year'] . ', Month: ' . date('F', mktime(0, 0, 0, $row['month'], 1)) . ', Day: ' . date('l', strtotime($row['year'].'-'.$row['month'].'-'.$row['submission_day'])) . ' ' . $row['submission_day'] . '</h3>';
    $surveyHistoryContent .= '<p><strong>Category A (Inattentive):</strong> ' . $partAScore . '</p>';
    $surveyHistoryContent .= '<p><strong>Category B (Hyperactive/Impulsive):</strong> ' . $partBScore . '</p>';
    $surveyHistoryContent .= '<p><strong>Total Score:</strong> ' . $totalScore . '</p>';
    $surveyHistoryContent .= '<p><em>Interpretation:</em> ' . $interpretation . '</p>';
    $surveyHistoryContent .= '<h4 style="color:#333;">Recommendations:</h4>';
    if (!empty($recommendationsList)) {
        $surveyHistoryContent .= '<ul>' . $recommendationsList . '</ul>';
    } else {
        $surveyHistoryContent .= $recommendationsList; // No <ul> if empty.
    }
}


// Add Survey History content to PDF
$pdf->writeHTML($surveyHistoryContent, true, false, true, false, '');

// Output the PDF
$pdf->Output('survey_results.pdf', 'I'); // Change 'I' to 'D' to force download
?>