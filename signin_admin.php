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
require 'db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";
$secretKey2 = "sicchecker";
// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);




if (empty($data['email']) || empty($data['password'])) {
    die(json_encode(["error" => "Email and password are required for signin."]));
}

$email = htmlspecialchars(trim($data['email']));
$password = $data['password'];


$stmt = $conn->prepare("SELECT id, password FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["error" => "Invalid email or password."]));
}

$stmt->bind_result($id, $hashedPassword);
$stmt->fetch();


if (password_verify($password, $hashedPassword)) {
    $payload = [
        "iss" => "your_issuer",
        "aud" => "your_audience",
        "iat" => time(),
        "nbf" => time(),
        "email" => $email,
        "role"=>"admin"
    ];
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    setcookie("auth_token", $jwt, [
        'expires' => time() + (86400 * 7),
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Strict'
    ]);
    echo json_encode(["message" => "success","role"=>"admin", ]);
} else {
    echo json_encode(["error" => "Invalid email or password."]);
}

$stmt->close();
$conn->close();
?>
