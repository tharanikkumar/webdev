<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST,GET, OPTIONS");
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
    if (isset($_COOKIE['auth_token1'])) {
        $jwt = $_COOKIE['auth_token1'];

        try {
            // Decode the JWT token to verify and check the role
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            // Check if the role is 'admin'
            if (!isset($decoded->role) || $decoded->role !== 'evaluator') {
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

// Function to fetch a single evaluator by ID
function getEvaluatorById($id) {
    global $conn;

    // Prepare SQL query
    if ($ids) {
        // If IDs are provided, fetch evaluators by IDs
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT id, first_name, last_name, email, phone_number, city,gender,college_name,designation,knowledge_domain,theme_preference_1,theme_preference_2,theme_preference_3,role_interested,evaluator_status,expertise_in_startup_value_chain,alternate_email,alternate_phone_number,total_experience, state FROM evaluator WHERE id IN ($placeholders) AND delete_status = 0";
        $stmt = $conn->prepare($query);

        // Bind the IDs as parameters
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    } else {
        // Fetch all evaluators if no IDs are provided
        $query = "SELECT id, first_name, last_name, email, phone_number, city,gender,college_name,designation,knowledge_domain,theme_preference_1,theme_preference_2,theme_preference_3,role_interested,evaluator_status,expertise_in_startup_value_chain,alternate_email,alternate_phone_number,total_experience, state FROM evaluator WHERE delete_status = 0";
        $stmt = $conn->prepare($query);
    }

    if ($stmt === false) {
        // Log and display detailed error information
        die(json_encode([
            "error" => "Failed to prepare SQL query.",
            "sql_error" => $conn->error
        ]));
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        // Log any errors related to query execution
        die(json_encode([
            "error" => "Failed to execute SQL query.",
            "sql_error" => $conn->error
        ]));
    }

    if ($result->num_rows > 0) {
        $evaluators = [];
        while ($row = $result->fetch_assoc()) {
            $evaluators[] = $row;
        }
        return $evaluators;
    } else {
        return [];
    }
}

// Check JWT cookie for valid admin user
$decodedUser = checkJwtCookie();

// Fetch common statistics (Total ideas, evaluators, pending verifications)
$commonStatistics = getCommonStatistics();

// Get evaluator IDs from request (if any)
$input = json_decode(file_get_contents("php://input"), true);
$evaluatorIds = isset($input['evaluator_ids']) ? $input['evaluator_ids'] : null;

if ($evaluatorIds === null || !is_array($evaluatorIds)) {
    // Return an error if evaluator_ids is not provided or is not an array
    echo json_encode(["error" => "Invalid evaluator_ids format."]);
    exit();
}

// Fetch evaluators based on provided IDs (or all evaluators if no IDs)
$evaluators = getEvaluators($evaluatorIds);

// Return common statistics and evaluators as JSON response
echo json_encode([
    "status" => "success",
    "common_statistics" => $commonStatistics,
    "evaluators" => $evaluators,
]);
?>
