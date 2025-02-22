<?php
require_once '../dbconnection.php';

function verifyResetCode($email, $resetCode) {
    global $conn; // Use the database connection from your dbconnection.php

    // Query to check if the reset code matches the email and is not expired
    $sql = "SELECT * FROM users WHERE Email = ? AND reset_code = ? AND reset_expiration > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $resetCode);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0; // Returns true if a matching record is found
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $resetCode = $_POST['reset_code'];

    // Debugging output
    echo "Email: $email<br>";
    echo "Reset Code: $resetCode<br>";

    if (verifyResetCode($email, $resetCode)) {
        header('Location: reset_password.php?email=' . urlencode($email)); // Redirect to reset password page
        exit(); // Make sure to exit after redirecting
    } else {
        echo "Invalid or expired reset code.";
    }
}
?>

<form method="post" action="reset_code.php">
    <label for="email">Email:</label>
    <input type="email" name="email" required>
    
    <label for="reset_code">Enter your reset code:</label>
    <input type="text" name="reset_code" required>
    
    <button type="submit">Verify Code</button>
</form>
