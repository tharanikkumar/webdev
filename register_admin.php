<?php
require 'vendor/autoload.php';
require 'db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$secretKey = "your_secret_key";

// Middleware function for admin token validation
function validateAdminToken($token) {
    global $secretKey;
    try {
        // Decode JWT token
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded->email; // Return email if token is valid
    } catch (Exception $e) {
        die(json_encode(["error" => "Invalid or expired admin token."]));
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["error" => "Invalid request method. Only POST is allowed."]));
}

// Ensure content type is JSON
if (empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    die(json_encode(["error" => "Content-Type must be application/json"]));
}

// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if 'action' is provided
if (empty($data['action'])) {
    die(json_encode(["error" => "Action is required (signup, signin, or approve)."]));
}

$action = sanitizeInput($data['action']);

// API route handling
switch ($action) {
    case 'approve':
        // Admin approval logic with middleware
        if (empty($data['admin_token'])) {
            die(json_encode(["error" => "Admin token is required for approval."]));
        }

        // Middleware call to validate admin token
        $adminEmail = validateAdminToken($data['admin_token']);

        // Approval logic
        $stmt = $conn->prepare("SELECT id FROM evaluator WHERE evaluator_status = 0 LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["error" => "No pending evaluators for approval."]);
        } else {
            $evaluator = $result->fetch_assoc();
            $evaluator_id = $evaluator['id'];

            // Approve the evaluator
            $stmt = $conn->prepare("UPDATE evaluator SET evaluator_status = 1 WHERE id = ?");
            $stmt->bind_param("i", $evaluator_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Evaluator approved successfully!", "evaluator_id" => $evaluator_id]);
            } else {
                die(json_encode(["error" => "Database error: " . $stmt->error]));
            }
        }

        $stmt->close();
        break;

    case 'signup':
        // Signup logic
        // Here you’d validate and save the new evaluator’s information in the database
        echo json_encode(["message" => "Signup functionality goes here."]);
        break;

    case 'signin':
        // Signin logic
        // Here you’d authenticate the evaluator and return a JWT token
        echo json_encode(["message" => "Signin functionality goes here."]);
        break;

    default:
        echo json_encode(["error" => "Invalid action. Use 'signup', 'signin', or 'approve'."]);
        break;
}

$conn->close();
?>
