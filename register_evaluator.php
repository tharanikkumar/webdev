<?php
require 'vendor/autoload.php';
require 'db.php'; // Include your database connection file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
    exit;
}

// Ensure content type is JSON
if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    echo json_encode(["error" => "Content-Type must be application/json"]);
    exit;
}

// Read and decode the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Ensure required fields are provided
$requiredFields = ["first_name", "last_name", "gender", "email", "phone_number", "college_name", "password"];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(["error" => "$field is required."]);
        exit;
    }
}

// Sanitize input data
$first_name = sanitizeInput($data['first_name']);
$last_name = sanitizeInput($data['last_name']);
$gender = sanitizeInput($data['gender']);
$email = sanitizeInput($data['email']);
$phone_number = sanitizeInput($data['phone_number']);
$password = password_hash(sanitizeInput($data['password']), PASSWORD_DEFAULT);
$alternate_email = sanitizeInput($data['alternate_email'] ?? null);
$alternate_phone_number = sanitizeInput($data['alternate_phone_number'] ?? null);
$college_name = sanitizeInput($data['college_name']);
$designation = sanitizeInput($data['designation'] ?? null);
$total_experience = intval($data['total_experience'] ?? 0);
$city = sanitizeInput($data['city'] ?? null);
$state = sanitizeInput($data['state'] ?? null);
$knowledge_domain = sanitizeInput($data['knowledge_domain'] ?? null);
$theme_preference_1 = sanitizeInput($data['theme_preference_1'] ?? null);
$theme_preference_2 = sanitizeInput($data['theme_preference_2'] ?? null);
$theme_preference_3 = sanitizeInput($data['theme_preference_3'] ?? null);
$expertise_in_startup_value_chain = sanitizeInput($data['expertise_in_startup_value_chain'] ?? null);
$role_interested = sanitizeInput($data['role_interested'] ?? null);
$evaluator_status = 0; // Default to inactive

// Prepare SQL statement to insert data into the evaluator table
$stmt = $conn->prepare("INSERT INTO evaluator (
    first_name, last_name, gender, email, alternate_email, phone_number, 
    alternate_phone_number, college_name, designation, total_experience, 
    city, state, knowledge_domain, theme_preference_1, theme_preference_2, 
    theme_preference_3, expertise_in_startup_value_chain, role_interested, password, evaluator_status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare SQL statement: " . $conn->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssissssssssssi",
    $first_name, $last_name, $gender, $email, $alternate_email, $phone_number,
    $alternate_phone_number, $college_name, $designation, $total_experience,
    $city, $state, $knowledge_domain, $theme_preference_1, $theme_preference_2,
    $theme_preference_3, $expertise_in_startup_value_chain, $role_interested,
    $password, $evaluator_status
);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["message" => "Evaluator registered successfully!"]);
} else {
    echo json_encode(["error" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
