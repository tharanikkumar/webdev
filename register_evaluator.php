<?php
// Include Composer's autoload file for Firebase JWT and other packages
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Secret key for JWT signing (should match across your application)
$secretKey = "your_secret_key";

// Function to sanitize and validate input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to check if required fields are provided in the data
function checkRequiredFields($data, $fields) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["error" => "$field is required."]);
            exit;
        }
    }
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for JSON content-type
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        echo json_encode(["error" => "Content-Type must be application/json"]);
        exit;
    }

    // Read and decode JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check JSON validity
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format: " . json_last_error_msg()]);
        exit;
    }

    // Define required fields for registration
    $requiredFields = ["first_name", "last_name", "gender", "email", "phone_number", "college_name", "password"];
    checkRequiredFields($data, $requiredFields);

    // Retrieve and sanitize input data
    $first_name = sanitizeInput($data['first_name']);
    $last_name = sanitizeInput($data['last_name']);
    $gender = sanitizeInput($data['gender']);
    $email = sanitizeInput($data['email']);
    $alternate_email = sanitizeInput($data['alternate_email'] ?? null);
    $phone_number = sanitizeInput($data['phone_number']);
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
    $password = password_hash(sanitizeInput($data['password']), PASSWORD_DEFAULT);

    // Prepare SQL statement to insert data into the evaluator table
    $stmt = $conn->prepare("INSERT INTO evaluator (
        first_name, last_name, gender, email, alternate_email, phone_number, 
        alternate_phone_number, college_name, designation, total_experience, 
        city, state, knowledge_domain, theme_preference_1, theme_preference_2, 
        theme_preference_3, expertise_in_startup_value_chain, role_interested, 
        password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare SQL statement: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssssissssssssss",
        $first_name, $last_name, $gender, $email, $alternate_email, $phone_number,
        $alternate_phone_number, $college_name, $designation, $total_experience,
        $city, $state, $knowledge_domain, $theme_preference_1, $theme_preference_2,
        $theme_preference_3, $expertise_in_startup_value_chain, $role_interested,
        $password
    );

    // Execute the statement and handle errors
    if ($stmt->execute()) {
        // JWT payload with standard claims
        $payload = [
            'iss' => 'your_website.com',
            'aud' => 'your_website.com',
            'iat' => time(),
            'exp' => time() + (60 * 60),  // Token valid for 1 hour
            'email' => $email
        ];

        // Generate JWT token
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // Set JWT as an HTTP-only cookie
        setcookie("evaluator_token", $jwt, time() + (60 * 60), "/", "", false, true);

        // Send success response
        echo json_encode(["message" => "Evaluator registered successfully!", "token" => $jwt]);
    } else {
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
} 

$conn->close();
?>
