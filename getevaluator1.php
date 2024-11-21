<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

<<<<<<< HEAD
// Function to fetch a single evaluator by ID
function getEvaluatorById($id) {
=======
// Function to fetch common statistics
function getCommonStatistics() {
    global $conn; // Ensure you're referring to the global $conn object

    // Query to get the common statistics with accurate counts for ideas, evaluators, and pending verifications
    $query = "
        SELECT 
            (SELECT COUNT(*) FROM ideas) AS ideas_registered,  -- Total ideas across all evaluators
            (SELECT COUNT(*) FROM evaluator WHERE delete_status = 0) AS total_evaluators,  -- Total evaluators where delete_status is 0 (active)
            (SELECT COUNT(*) FROM evaluator WHERE evaluator_status = 3) AS pending_evaluators  -- Evaluators with status 0 (pending)
    ";

    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        // Log and display detailed error information
        die(json_encode([
            "error" => "Failed to prepare SQL query.",
            "sql_error" => $conn->error
        ]));
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Function to fetch evaluators based on IDs or all evaluators
function getEvaluators($ids = null) {
>>>>>>> ad9dcde5de2dba79753565dcd5eec92bdacb123b
    global $conn;

    // Prepare SQL query to fetch evaluator by ID
    $query = "SELECT * FROM evaluator WHERE id = ? AND delete_status = 0";
    $stmt = $conn->prepare($query);

    // Bind the ID as a parameter
    $stmt->bind_param('i', $id);

    if ($stmt === false) {
        // Log and display detailed error information
        die(json_encode([ "error" => "Failed to prepare SQL query.", "sql_error" => $conn->error ]));
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();  // Return a single evaluator
    } else {
        return null;  // No evaluator found
    }
}

// Check JWT cookie for valid admin user
$decodedUser = checkJwtCookie();

// Get evaluator ID from request query parameters
$evaluatorId = isset($_GET['evaluator_id']) ? (int) $_GET['evaluator_id'] : null;

// Debugging step: log the evaluator_id
error_log("Received evaluator_id: " . $evaluatorId);

// Check if evaluator_id is provided
if ($evaluatorId) {
    // Fetch the evaluator by ID
    $evaluator = getEvaluatorById($evaluatorId);

    // Debugging step: log the evaluator data
    error_log("Fetched evaluator: " . json_encode($evaluator));

    if ($evaluator) {
        // Return evaluator details as JSON response
        echo json_encode([
            "status" => "success",
            "evaluator" => $evaluator,
        ]);
    } else {
        // If evaluator not found
        echo json_encode([
            "status" => "error",
            "message" => "Evaluator not found."
        ]);
    }
} else {
    // If evaluator_id is not provided
    echo json_encode([
        "status" => "error",
        "message" => "No evaluator ID provided."
    ]);
}

?>
