<?php
require_once '../dbconnection.php';

// Function to fetch 5 random questions from a specific category
function getRandomQuestions($category_id, $limit = 5) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("ii", $category_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Initialize questions array
$questions = [];

// Fetch categories
$categories = $conn->query("SELECT id FROM categories");

if ($categories) {
    while ($category = $categories->fetch_assoc()) {
        $category_id = $category['id'];
        $questions[$category_id] = getRandomQuestions($category_id);
    }
} else {
    echo "Error fetching categories: " . $conn->error;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $answers = $_POST['answers'];
    $user_id = 1; // Replace with the actual user ID

    $category_scores = [];

    foreach ($answers as $question_id => $response) {
        // Fetch the category ID for this question
        $stmt = $conn->prepare("SELECT category_id FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->bind_result($category_id);
        $stmt->fetch();
        $stmt->close();

        // Insert the user's response into the user_responses table
        $stmt = $conn->prepare("INSERT INTO user_responses (user_id, question_id, response, category_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $user_id, $question_id, $response, $category_id);
        $stmt->execute();

        // Calculate the score per category
        if (!isset($category_scores[$category_id])) {
            $category_scores[$category_id] = 0;
        }
        $category_scores[$category_id] += $response;
    }

    // Fetch and display recommendations based on scores
    echo "<h2>Your Recommendations</h2>";
    foreach ($category_scores as $category_id => $total_score) {
        $stmt = $conn->prepare("SELECT recommendation_text FROM recommendations WHERE category_id = ? AND ? BETWEEN min_score AND max_score");
        $stmt->bind_param("ii", $category_id, $total_score);
        $stmt->execute();
        $stmt->bind_result($recommendation_text);
        $stmt->fetch();

        echo "<h3>Category $category_id</h3>";
        echo "<p>$recommendation_text</p>";

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-set {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Survey</h1>
        <form id="surveyForm" method="POST">
            <?php 
            $setNumber = 1;
            foreach ($questions as $category_id => $qs): ?>
                <div class="question-set" id="set-<?php echo $setNumber; ?>" style="display: <?php echo $setNumber === 1 ? 'block' : 'none'; ?>;">
                    <h3>Category: <?php echo $category_id; ?></h3>
                    <ul class="list-group">
                        <?php foreach ($qs as $index => $question): ?>
                            <li class="list-group-item">
                                <label><?php echo htmlspecialchars($question['question_text']); ?></label><br>
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="1" required> 1
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="2"> 2
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="3"> 3
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="4"> 4
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="5"> 5
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php $setNumber++; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-success" id="submitButton" style="display: none;">Submit</button>
        </form>
    </div>

    <script>
        let currentSet = 1;
        const totalSets = <?php echo $setNumber - 1; ?>;

        function checkAnswers() {
            const questions = document.querySelectorAll(`#set-${currentSet} input[type="radio"]`);
            let allAnswered = true;

            questions.forEach(question => {
                const questionId = question.name.match(/\d+/)[0];
                if (!document.querySelector(`input[name="answers[${questionId}]"]:checked`)) {
                    allAnswered = false;
                }
            });

            if (allAnswered) {
                setTimeout(() => {
                    document.getElementById(`set-${currentSet}`).style.display = 'none';
                    currentSet++;
                    
                    if (currentSet <= totalSets) {
                        document.getElementById(`set-${currentSet}`).style.display = 'block';
                    }
                    
                    if (currentSet > totalSets) {
                        document.getElementById('submitButton').style.display = 'block';
                    }
                }, 500); // Small delay to ensure smooth transition
            }
        }

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', checkAnswers);
        });
    </script>
</body>
</html>
