<?php
$servername = "localhost";
$username = "u722506168_dbh27";
$password = "Antoni27!";
$dbname = "u722506168_dbh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
