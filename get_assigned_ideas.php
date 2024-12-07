<?php
<<<<<<< HEAD
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
=======
// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


>>>>>>> b6a10acb09cf7ae4453436863218b996372070e3

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

<<<<<<< HEAD
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function checkJwtCookie() {
    global $secretKey;

    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];

        try {
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            if (!isset($decoded->role) || $decoded->role !== 'evaluator') {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(["error" => "You are not an evaluator."]);
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

function getIdeasByEvaluatorId($evaluatorId) {
    global $conn;

    // Query to join ideas and idea_evaluators to fetch the ideas assigned to the evaluator
    $query = "
    SELECT i.*
    FROM ideas i
    INNER JOIN idea_evaluators ie ON i.id = ie.idea_id
    WHERE ie.evaluator_id = ?
";

    $stmt = $conn->prepare($query);

    // Check if the query was prepared successfully
    if ($stmt === false) {
        die(json_encode([ 
            "error" => "Failed to prepare SQL query.", 
            "sql_error" => $conn->error 
        ]));
    }

    // Bind the evaluator ID as a parameter
    $stmt->bind_param('i', $evaluatorId);

    // Execute the query
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch all ideas assigned to the evaluator
    $ideas = [];
    while ($row = $result->fetch_assoc()) {
        $ideas[] = $row;
    }

    return $ideas;
}


// Check JWT cookie for valid admin user
$decodedUser = checkJwtCookie();

// Get evaluator ID from request query parameters
$evaluatorId = isset($_GET['evaluator_id']) ? (int) $_GET['evaluator_id'] : null;

// Debugging step: log the evaluator_id
error_log("Received evaluator_id: " . $evaluatorId);


if ($evaluatorId) {
    // Fetch the evaluator by ID
    $ideas = getIdeasByEvaluatorId($evaluatorId);

    error_log("Fetched evaluator: " . json_encode($ideas));

    if ($ideas) {
        echo json_encode([
            "status" => "success",
            "ideas" => $ideas,
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Ideas not found."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No evaluator  ID provided."
    ]);
}

=======
// Include necessary files
require 'db.php';

// Get evaluator_id from query parameters
$evaluator_id = $_GET['evaluator_id'] ?? null;

if (!$evaluator_id) {
    echo json_encode(["error" => "Evaluator ID is required."]);
    exit();
}

// Query to fetch assigned ideas along with their evaluation scores and evaluator comment
$query = "
    SELECT 
        ideas.id AS idea_id,
        ideas.student_name,
        ideas.school,
        ideas.idea_title,
        ideas.status_id,
        ideas.theme_id,
        ideas.type,
        ideas.idea_description,
        ideas.assigned_count,
        idea_evaluators.novelity_score,
        idea_evaluators.usefulness_score,
        idea_evaluators.feasability_score,
        idea_evaluators.scalability_score,
        idea_evaluators.sustainability_score,
        idea_evaluators.evaluator_comment
    FROM ideas
    LEFT JOIN idea_evaluators ON ideas.id = idea_evaluators.idea_id
    WHERE idea_evaluators.evaluator_id = ?
";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluator_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all ideas as an array
$ideas = [];
while ($row = $result->fetch_assoc()) {
    $ideas[] = $row;
}

// Return ideas as JSON
echo json_encode($ideas);

// Close the connection
$stmt->close();
$conn->close();
>>>>>>> b6a10acb09cf7ae4453436863218b996372070e3
?>
