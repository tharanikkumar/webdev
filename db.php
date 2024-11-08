<?php
$host = 'localhost';        // Database host
$username = 'root';         // Database username
$password = '';             // Database password (usually empty for XAMPP)
$dbname = 'testingdb';   // Your database name

// Create a new connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
