<?php
$servername = "localhost"; // Change if necessary
$username = "root";        // Replace with your MySQL username
$password = "";            // Replace with your MySQL password
$dbname = "testingdb"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
