<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files and setup
require 'vendor/autoload.php';
require 'db.php'; // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Add CORS headers to allow requests from your frontend (adjust origin if needed)
header("Access-Control-Allow-Origin: http://localhost:5173");  // Your frontend URL
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); // If you need cookies with the requests
header("Content-Type: application/json");

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);  // No further processing for preflight request
}

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

// Fetch evaluator from the database, including evaluator_status and name
$stmt = $conn->prepare("SELECT id, first_name, last_name, password, evaluator_status FROM evaluator WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["error" => "No user found with this email."]));
}

$stmt->bind_result($id, $firstName, $lastName, $hashedPassword, $evaluatorStatus);
$stmt->fetch();

// Check if the evaluator is approved (status 1)
if ($evaluatorStatus == 3) {
    die(json_encode(["error" => "You are not approved yet. Please wait for verification."]));
}

// Verify the password if evaluator is approved (status 1)
if (password_verify($password, $hashedPassword)) {
    // JWT payload with evaluator role and claims
    $payload = [
        'iss' => 'your_website.com',
        'aud' => 'your_website.com',
        'iat' => time(),
        'exp' => time() + (60 * 60), // Token valid for 1 hour
        'email' => $email,

    ];

    // Generate JWT token
    $jwt = JWT::encode($payload, 'sic', 'HS256');

    // Set the JWT token as a cookie with an expiration time (e.g., 1 hour)

    setcookie("auth_token1", $jwt, [
        "expires" => time() + 3600, // 1 hour
        "path" => "/",
        "domain" => "localhost",
        "secure" => false, // Set true only for HTTPS
        "httponly" => false, // Set true if cookies are not accessed via JS
        "samesite" => "Lax" // Use "None" for cross-site requests if needed
    ]);

    // Set the role as a cookie
    setcookie("role", "evaluator", [
        "expires" => time() + 3600, // 1 hour
        "path" => "/",
        "domain" => "localhost",
        "secure" => false, // Set true only for HTTPS
        "httponly" => false, // Allow JS to access the role if needed
        "samesite" => "Lax" // Use "None" for cross-site requests if needed
    ]);

    // Send the token, role, and evaluator ID in the response
    echo json_encode([
        "message" => "Signin successful!",
        "role" => "evaluator",
        "id" => $id, // Include evaluator ID
        "token" => $jwt

    ]);
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
