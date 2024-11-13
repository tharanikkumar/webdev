<?php
require 'vendor/autoload.php';
require 'db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "your_secret_key"; // Define your secret key for JWT

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

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

// Check if admin token and evaluator_id are provided for editing
if (empty($data['admin_token']) || empty($data['evaluator_id'])) {
    die(json_encode(["error" => "Admin token and evaluator ID are required for editing."]));
}

$adminEmail = validateAdminToken($data['admin_token'], $secretKey); // Validate the admin token
$evaluator_id = intval($data['evaluator_id']);

// Check if evaluator exists with the given ID and is associated with this admin token
$stmt = $conn->prepare("SELECT id FROM evaluator WHERE id = ? AND evaluator_status = 1");
$stmt->bind_param("i", $evaluator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["error" => "Evaluator ID not found or you do not have permission to edit this evaluator."]));
}

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
$conn->close();
?>
