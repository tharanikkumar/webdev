<?php

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include ('db.php');
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

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

// Function to fetch evaluators (only id and name)
function getEvaluators() {
    global $conn;

    // Define the query to fetch only the id and name of evaluators
    $query = "SELECT id, first_name FROM evaluator WHERE delete_status = 0  AND evaluator_status= 1"; 

    // Prepare the statement
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        // Log and display detailed error information if query preparation fails
        die(json_encode([
            "error" => "Failed to prepare SQL query.",
            "sql_error" => $conn->error
        ]));
    }

    // Execute the statement
    if (!$stmt->execute()) {
        // Log and display error message if the execution fails
        die(json_encode([
            "error" => "Failed to execute SQL query.",
            "sql_error" => $stmt->error
        ]));
    }

    // Fetch all evaluators who meet the condition
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch evaluators (only id and name) as an associative array
        $evaluators = [];
        while ($row = $result->fetch_assoc()) {
            $evaluators[] = [
                'id' => $row['id'], 
                'name' => $row['first_name']
            ];
        }
        return json_encode($evaluators);
    } else {
        // Return a message indicating no evaluators found
        return json_encode([
            "message" => "No evaluators found with fewer than 3 assigned ideas."
        ]);
    }
}

// Check JWT cookie for valid admin user
$decodedUser = checkJwtCookie();

// Fetch all evaluators
$evaluators = getEvaluators();

// Return evaluators as JSON response
echo json_encode([
    "status" => "success",
    "evaluators" => json_decode($evaluators),  // Return the evaluator list
]);
?>
