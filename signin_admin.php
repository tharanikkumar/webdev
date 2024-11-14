<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";

// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input fields for signin
if (empty($data['email']) || empty($data['password'])) {
    die(json_encode(["error" => "Email and password are required for signin."]));
}

$email = htmlspecialchars(trim($data['email']));
$password = $data['password'];

// Prepare SQL query to fetch admin data
$stmt = $conn->prepare("SELECT id, password FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["error" => "Invalid email or password."]));
}

$stmt->bind_result($id, $hashedPassword);
$stmt->fetch();

// Verify the password
if (password_verify($password, $hashedPassword)) {
    // JWT payload with role 'admin'
    $payload = [
        "iss" => "your_issuer",
        "aud" => "your_audience",
        "iat" => time(),
        "nbf" => time(),
        "email" => $email,
        "role" => "admin"  // Add role claim to the token
    ];
    
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    
    // Set the token as a secure, HttpOnly cookie
    setcookie("auth_token", $jwt, [
        'expires' => time() + (86400 * 7),  // 7 days expiration
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),  // Ensure HTTPS for secure cookie
        'samesite' => 'Strict'  // Ensure the cookie is sent only to same-site requests
    ]);

    echo json_encode(["message" => "Signin successful", "role" => "admin"]);
} else {
    echo json_encode(["error" => "Invalid email or password."]);
}

$stmt->close();
$conn->close();
?>
