<?php
require 'vendor/autoload.php';
require 'db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Secret key for JWT (Make sure to change this to your actual secret key)
$secretKey = "sic";

// Middleware function to validate the admin session using cookies
function checkJwtCookie() {
    global $secretKey;

    // Check if the auth token is stored in the cookie
    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];

        try {
            // Decode the JWT token to verify and check the role
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            // Check if the role is 'admin'
            if (!isset($decoded->role) || $decoded->role !== 'admin') {
                // Return a 403 Forbidden status and a custom message
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(["error" => "You are not an admin."]);
                exit();
            }

            // Return the decoded JWT data if role is 'admin'
            return $decoded;

        } catch (Exception $e) {
            // If the JWT verification fails, return a 401 Unauthorized status with the error message
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(["error" => "Unauthorized - " . $e->getMessage()]);
            exit();
        }
    } else {
        // If the auth token is missing, return a 401 Unauthorized status with a message
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(["error" => "Unauthorized - No token provided."]);
        exit();
    }
}

// Read and decode the JSON payload (only the evaluator ID)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Ensure the evaluator ID is provided in the request
if (empty($data['evaluator_id'])) {
    die(json_encode(["error" => "Evaluator ID is required for approval."]));
}

// Get the admin's email from the session (cookie)
$adminEmail = checkJwtCookie(); // Validate the admin session using cookies
$evaluator_id = $data['evaluator_id'];

// Check if the evaluator exists and is pending approval
$stmt = $conn->prepare("SELECT id FROM evaluator WHERE id = ? AND evaluator_status = 0");
$stmt->bind_param("i", $evaluator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Evaluator ID not found or already approved."]);
} else {
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
$conn->close();
?>
