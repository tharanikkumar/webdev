<?php
// Include Composer's autoload file for Firebase JWT and other packages
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Secret key for JWT signing (should match across your application)
$secretKey = "your_secret_key";

// Function to sanitize and validate input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to check if required fields are provided in the data
function checkRequiredFields($data, $fields) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["error" => "$field is required."]);
            exit;
        }
    }
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for JSON content-type
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        echo json_encode(["error" => "Content-Type must be application/json"]);
        exit;
    }

    // Read and decode JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check JSON validity
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format: " . json_last_error_msg()]);
        exit;
    }

    // Define required fields for registration
    $requiredFields = ["name", "email", "password"];
    checkRequiredFields($data, $requiredFields);

    // Retrieve and sanitize input data
    $name = sanitizeInput($data['name']);
    $email = sanitizeInput($data['email']);
    $password = password_hash(sanitizeInput($data['password']), PASSWORD_DEFAULT);

    // Prepare SQL statement to insert data into the admin table
    $stmt = $conn->prepare("INSERT INTO admin (name, email, password) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare SQL statement: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sss", $name, $email, $password);

    // Execute the statement and handle errors
    if ($stmt->execute()) {
        // JWT payload with standard claims
        $payload = [
            'iss' => 'your_website.com',
            'aud' => 'your_website.com',
            'iat' => time(),
            'exp' => time() + (60 * 60),  // Token valid for 1 hour
            'email' => $email
        ];

        // Generate JWT token
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // Set JWT as an HTTP-only cookie
        setcookie("admin_token", $jwt, time() + (60 * 60), "/", "", false, true);

        // Send success response
        echo json_encode(["message" => "Admin registered successfully!", "token" => $jwt]);
    } else {
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
} 

$conn->close();
?>
