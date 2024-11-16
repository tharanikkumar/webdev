<?php
// Include necessary files and setup
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["error" => "Invalid request method. Only POST is allowed."]));
}

// Read and decode the JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// Validate input fields for signin
if (empty($data['email']) || empty($data['password'])) {
    die(json_encode(["error" => "Email and password are required for signin."]));
}

// Sanitize input data
$email = sanitizeInput($data['email']);
$password = sanitizeInput($data['password']);

// Fetch evaluator from the database, including evaluator_status
$stmt = $conn->prepare("SELECT id, password, evaluator_status FROM evaluator WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["error" => "No user found with this email."]));
}

$stmt->bind_result($id, $hashedPassword, $evaluatorStatus);
$stmt->fetch();

// Check if the evaluator is approved
if ($evaluatorStatus == 3) {
    die(json_encode(["error" => "You are not approved yet. Please wait for verification."]));
}

// Verify the password if evaluator is approved (status 1)
if (password_verify($password, $hashedPassword)) {
    // JWT payload with standard claims
    $payload = [
        'iss' => 'your_website.com',
        'aud' => 'your_website.com',
        'iat' => time(),
        'exp' => time() + (60 * 60),  // Token valid for 1 hour
        'email' => $email
    ];

    // Generate JWT token
    $jwt = JWT::encode($payload, 'sic', 'HS256');

    // Set the JWT token as a cookie with an expiration time (e.g., 1 hour)
    setcookie('auth_token1', $jwt, time() + (60 * 60), '/', 'localhost', true, true); // Secure cookie flag, HTTPOnly

    // Send the token in the response as well, in case the client wants to handle it
    echo json_encode(["message" => "Signin successful!", "token" => $jwt]);
} else {
    echo json_encode(["error" => "Invalid password."]);
}

$stmt->close();
$conn->close();

// Helper function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
