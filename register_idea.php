<?php

require_once __DIR__ . '/vendor/autoload.php'; 

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Use the Firebase JWT classes
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Handle pre-flight OPTIONS request (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
include 'db.php';
include 'vendor/autoload.php'; // Ensure to include JWT library

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Define your secret key here or include it from a config file
$secretKey = 'sic'; // Replace this with your actual secret key

// Middleware function to validate the admin session using cookies
function checkJwtCookie() {
    global $secretKey;

    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];

        try {
            // Ensure the $secretKey is defined
            if (empty($secretKey)) {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(["error" => "Secret key is not defined."]);
                exit();
            }

            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            if (!isset($decoded->role) || $decoded->role !== 'admin') {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(["error" => "You are not an admin."]);
                exit();
            }

            return $decoded;
        } catch (Exception $e) {
            header("HTTP/1.1 401 Unauthorized");
            echo json_encode(["error" => "Unauthorized - " . $e->getMessage()]);
            exit();
        }
    } else {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(["error" => "Unauthorized - No token provided."]);
        exit();
    }
}

// Call the checkJwtCookie function to validate the cookie
checkJwtCookie();

// Secret key for JWT (ensure to use a strong, secret key in production)
$secretKey = "sic"; // Ideally, this should come from an environment variable

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

$adminData = checkJwtCookie();
// Get JSON input and decode it
$input = json_decode(file_get_contents("php://input"), true);

// Extract the data for registering the idea
$student_name = $input['student_name'] ?? null;
$school = $input['school'] ?? null;
$idea_title = $input['idea_title'] ?? null;
$theme_id = $input['theme_id'] ?? null;
$type = $input['type'] ?? null;
$idea_description = $input['idea_description'] ?? null;


$status_id = 3; // Pending status

// Check if required fields for registering an idea are present
if (empty($student_name) || empty($school) || empty($idea_title) || empty($theme_id) || empty($type) || empty($idea_description)) {
    echo json_encode(["error" => "All idea registration fields are required."]);
    exit;
}

// Start transaction
$conn->autocommit(false);

try {
    // Insert the idea into the ideas table with status_id as 3 (Pending)
    $stmt = $conn->prepare("INSERT INTO ideas (student_name, school, idea_title, status_id, theme_id, type, idea_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiss", $student_name, $school, $idea_title, $status_id, $theme_id, $type, $idea_description);
    $stmt->execute();
    
    // Get the inserted idea's ID
    $idea_id = $stmt->insert_id;
    $stmt->free_result();

    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => "Idea registered successfully."]);

} catch (Exception $e) {
    // Rollback if something goes wrong
    $conn->rollback();
    echo json_encode(["error" => "Failed to register idea: " . $e->getMessage()]);
} finally {
    // End transaction mode
    $conn->autocommit(true);
}
?>
