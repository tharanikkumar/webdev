<?php
require 'vendor/autoload.php';
require 'db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "your_secret_key"; // Define your secret key for JWT

// Middleware function to validate the admin token
function validateAdminToken($token, $secretKey) {
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded->email; // Return the email if token is valid
    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid or expired admin token."]);
        exit;
    }
}

// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Ensure the admin token and evaluator ID are provided
if (empty($data['admin_token']) || empty($data['evaluator_id'])) {
    die(json_encode(["error" => "Admin token and evaluator ID are required for approval."]));
}

$adminEmail = validateAdminToken($data['admin_token'], $secretKey); // Validate the admin token
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
