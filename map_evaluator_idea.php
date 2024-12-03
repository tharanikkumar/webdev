<?php
// Handle the preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow your frontend's origin
    header("Access-Control-Allow-Credentials: true");              // Allow credentials (if needed)
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");    // Allowed methods
    header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers
    header("Access-Control-Max-Age: 86400"); // Cache preflight request for 24 hours
    exit;  // End the script after handling the OPTIONS request
}

// The following headers will be applied to the actual request
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow your frontend's origin
header("Access-Control-Allow-Credentials: true");              // Allow credentials (if needed)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");    // Allowed methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers

require 'vendor/autoload.php'; // Include JWT library (e.g., Firebase JWT)
require 'db.php'; // Include database connection

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define your secret key for JWT
$secretKey = "sic";

// Middleware function to validate the admin or evaluator session using cookies
function checkJwtCookie() {
    global $secretKey;

    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];

        try {
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            // Allow only admin and evaluator roles
            if (!isset($decoded->role) || !in_array($decoded->role, ['admin', 'evaluator'])) {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(["error" => "You are not authorized to perform this action."]);
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

// Validate the user using the middleware
$user = checkJwtCookie();

// Get JSON input and decode it
$input = json_decode(file_get_contents("php://input"), true);

// Extract data from the request
$idea_id = $input['idea_id'] ?? null;
$evaluator_ids = $input['evaluator_ids'] ?? null;  // Array of evaluator IDs
$score = $input['score'] ?? null;
$evaluator_comments = $input['evaluator_comments'] ?? null;

// Check if required fields are present
if (empty($idea_id) || empty($evaluator_ids)) {

    http_response_code(400);
    echo json_encode(["error" => "Idea ID and Evaluator IDs are required."]);
    exit;
}

// Ensure evaluator_ids is an array
if (!is_array($evaluator_ids)) {
    http_response_code(400);
    echo json_encode(["error" => "Evaluator IDs must be provided as an array."]);

    exit;
}

// Check if the idea_id exists in the ideas table
$stmt_check_idea = $conn->prepare("SELECT COUNT(*) FROM ideas WHERE id = ?");
$stmt_check_idea->bind_param("i", $idea_id);
$stmt_check_idea->execute();
$stmt_check_idea->bind_result($idea_count);
$stmt_check_idea->fetch();
$stmt_check_idea->free_result();

if ($idea_count == 0) {
    http_response_code(404);
    echo json_encode(["error" => "Invalid idea_id: $idea_id. The idea does not exist."]);
    exit;
}

// Start transaction
$conn->autocommit(false);

try {

    foreach ($evaluator_ids as $evaluator_id) {
        // Check if the evaluator_id exists in the evaluator table
        $stmt_check_evaluator = $conn->prepare("SELECT COUNT(*) FROM evaluator WHERE id = ?");
        $stmt_check_evaluator->bind_param("i", $evaluator_id);
        $stmt_check_evaluator->execute();
        $stmt_check_evaluator->bind_result($evaluator_count);
        $stmt_check_evaluator->fetch();
        $stmt_check_evaluator->free_result();

        if ($evaluator_count == 0) {

            echo json_encode(["error" => "Invalid evaluator_id: $evaluator_id. The evaluator does not exist."]);
            exit;
        }

        // Insert data into idea_evaluators table for each evaluator
        $stmt = $conn->prepare("INSERT INTO idea_evaluators (idea_id, evaluator_id, score, evaluator_comments) VALUES (?, ?, ?, ?)");
        // If score or evaluator_comments is null, bind them as such
        $stmt->bind_param("iiis", $idea_id, $evaluator_id, $score, $evaluator_comments);
        $stmt->execute();
    }
    
    // Now update the idea_status to 2 (assuming 2 means "evaluated")
    $stmt_update_status = $conn->prepare("UPDATE ideas SET status_id = 2 WHERE id = ?");
    $stmt_update_status->bind_param("i", $idea_id);
    $stmt_update_status->execute();
    
    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => "Evaluators successfully mapped to the idea and idea status updated."]);

            throw new Exception("Invalid evaluator_id: $evaluator_id. The evaluator does not exist.");
        }

        // Insert data into idea_evaluators table
        $stmt = $conn->prepare("INSERT INTO idea_evaluators (idea_id, evaluator_id, score, evaluator_comments) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $idea_id, $evaluator_id, $score, $evaluator_comments);
        $stmt->execute();
    }

 

} catch (Exception $e) {
    // Rollback if something goes wrong
    $conn->rollback();

    http_response_code(500);
    echo json_encode(["error" => "Failed to map evaluators: " . $e->getMessage()]);

} finally {
    // End transaction mode
    $conn->autocommit(true);
}
?>
