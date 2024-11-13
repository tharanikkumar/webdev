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

// Fetch evaluator from the database
$stmt = $conn->prepare("SELECT id, password FROM evaluator WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["error" => "No user found with this email."]));
}

$stmt->bind_result($id, $hashedPassword);
$stmt->fetch();

// Verify the password
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
    $jwt = JWT::encode($payload, 'your_secret_key', 'HS256');

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
