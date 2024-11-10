<?php
// Include Composer's autoload file for Firebase JWT and other packages
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Secret key for JWT signing (should match across your application)
$secretKey = "your_secret_key";

// Middleware to check if the request content type is JSON
function ensureJsonContentType() {
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        echo json_encode(["error" => "Content-Type must be application/json"]);
        exit;
    }
}

// Middleware to parse and validate JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format: " . json_last_error_msg()]);
        exit;
    }
    return $data;
}

// Middleware function to check if required fields are provided in the data
function checkRequiredFields($data, $fields) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["error" => "$field is required."]);
            exit;
        }
    }
}

// Middleware to validate JWT token
function validateToken($token) {
    global $secretKey;
    
    try {
        // Decode the token
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded;  // Return decoded payload if validation is successful
    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid or expired token."]);
        exit;
    }
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Main API routing logic
function handleRequest($data) {
    global $conn;

    // Check the action and handle accordingly
    $action = sanitizeInput($data['action'] ?? '');

    switch ($action) {
        case 'signup':
            handleSignup($data);
            break;

        case 'signin':
            handleSignin($data);
            break;

        case 'restrictedAction':
            // Check if token is provided and validate it
            if (empty($data['token'])) {
                echo json_encode(["error" => "Token is required for this action."]);
                exit;
            }
            $decoded = validateToken($data['token']);  // Middleware for token validation
            handleRestrictedAction($decoded);  // Only accessible with a valid token
            break;

        default:
            echo json_encode(["error" => "Invalid action. Use 'signup', 'signin', or 'restrictedAction'."]);
            break;
    }
}

// Signup logic for evaluator registration
function handleSignup($data) {
    global $conn;

    // Define required fields for registration (signup)
    $requiredFields = ["first_name", "last_name", "gender", "email", "phone_number", "college_name", "password"];
    checkRequiredFields($data, $requiredFields);

    // Retrieve and sanitize input data
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
    $evaluator_status = 0;  // Default to inactive

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
}

// Signin logic for evaluator authentication
function handleSignin($data) {
    global $conn, $secretKey;

    // Define required fields for signin
    $requiredFields = ["email", "password"];
    checkRequiredFields($data, $requiredFields);

    // Retrieve and sanitize input data
    $email = sanitizeInput($data['email']);
    $password = sanitizeInput($data['password']);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, password FROM evaluator WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashedPassword)) {
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

            echo json_encode(["message" => "Signin successful!", "token" => $jwt]);
        } else {
            echo json_encode(["error" => "Invalid password."]);
        }
    } else {
        echo json_encode(["error" => "No user found with this email."]);
    }

    $stmt->close();
}

// Example of a restricted action that requires token validation
function handleRestrictedAction($decoded) {
    echo json_encode(["message" => "Restricted action accessed!", "user" => $decoded->email]);
}

// Main script logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ensureJsonContentType();
    $data = getJsonInput();
    handleRequest($data);
} else {
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
}

$conn->close();
?>