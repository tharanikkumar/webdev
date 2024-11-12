<?php
require 'vendor/autoload.php';
require 'db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic"; 

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Middleware for JWT token verification
function verifyJWTToken($secretKey) {
    if (isset($_COOKIE['auth_token'])) {
        try {
            $jwt = $_COOKIE['evaluator_token'];
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            echo json_encode(["error" => "Invalid or expired token"]);
            exit;
        }
    } else {
        echo json_encode(["error" => "Authorization token is required"]);
        exit;
    }
}

// Verify token before processing the request
$decodedToken = verifyJWTToken($secretKey);

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

// Check for the correct action


// Check if evaluator_id is provided for editing
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
