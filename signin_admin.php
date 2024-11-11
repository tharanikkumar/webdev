<?php
require 'vendor/autoload.php';
require 'db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "your_secret_key";

// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Ensure that the action is 'signin'
if (empty($data['action']) || $data['action'] !== 'signin') {
    die(json_encode(["error" => "Invalid action. Only 'signin' is allowed in this file."]));
}

// Ensure email and password are provided
if (empty($data['email']) || empty($data['password'])) {
    die(json_encode(["error" => "Email and password are required for signin."]));
}

$email = htmlspecialchars(trim($data['email']));
$password = $data['password'];

// Check if the email exists in the admin table
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
    $payload = [
        "iss" => "your_issuer",
        "aud" => "your_audience",
        "iat" => time(),
        "nbf" => time(),
        "email" => $email
    ];
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    echo json_encode(["message" => "Signin successful", "token" => $jwt]);
} else {
    echo json_encode(["error" => "Invalid email or password."]);
}

$stmt->close();
$conn->close();
?>
