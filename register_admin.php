<?php
require 'vendor/autoload.php';
require 'db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$secretKey = "your_secret_key";

// Middleware function to validate the admin token
function validateAdminToken($token, $secretKey) {
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded->email;
    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid or expired admin token."]);
        exit;
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
    die(json_encode(["error" => "Action is required (signup, signin, approve, or edit)."]));
}

$action = sanitizeInput($data['action']);

if ($action === 'approve') {
    // Approval logic for admin
    if (empty($data['admin_token'])) {
        die(json_encode(["error" => "Admin token is required for approval."]));
    }

    // Validate the admin token
    $adminEmail = validateAdminToken($data['admin_token'], $secretKey);

    // Retrieve the first evaluator pending approval
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

} elseif ($action === 'edit') {
    // Editing logic for admin
    if (empty($data['admin_token'])) {
        die(json_encode(["error" => "Admin token is required for editing."]));
    }

    // Validate the admin token
    $adminEmail = validateAdminToken($data['admin_token'], $secretKey);

    // Check if evaluator_id is provided
    if (empty($data['evaluator_id'])) {
        die(json_encode(["error" => "Evaluator ID is required for editing."]));
    }

    $evaluator_id = intval($data['evaluator_id']);

    // Fields that can be updated
    $fields = [
        "first_name", "last_name", "gender", "email", "phone_number", "college_name", 
        "alternate_email", "alternate_phone_number", "designation", "total_experience", 
        "city", "state", "knowledge_domain", "theme_preference_1", "theme_preference_2", 
        "theme_preference_3", "expertise_in_startup_value_chain", "role_interested"
    ];

    // Prepare the SQL update query dynamically based on the provided fields
    $updateFields = [];
    $updateValues = [];
    foreach ($fields as $field) {
        if (!empty($data[$field])) {
            $updateFields[] = "$field = ?";
            $updateValues[] = sanitizeInput($data[$field]);
        }
    }

    // Check if there is anything to update
    if (empty($updateFields)) {
        die(json_encode(["error" => "No fields provided to update."]));
    }

    // Add evaluator_id to the values array
    $updateValues[] = $evaluator_id;

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE evaluator SET " . implode(", ", $updateFields) . " WHERE id = ?");
    $stmt->bind_param(str_repeat("s", count($updateValues) - 1) . "i", ...$updateValues);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Evaluator details updated successfully!"]);
    } else {
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(["error" => "Invalid action. Use 'signup', 'signin', 'approve', or 'edit'."]);
}

$conn->close();
?>
