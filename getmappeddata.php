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

// Include database connection
include 'db.php';

// Get the idea_id from query parameters
$idea_id = $_GET['idea_id'] ?? null;  // Retrieve the idea_id from the query string

if (empty($idea_id)) {
    echo json_encode(["error" => "Idea ID is required."]);
    exit;
}

// Prepare the SQL query to fetch the mapped evaluators for the given idea_id
$stmt = $conn->prepare(
    "SELECT ie.evaluator_id, CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name, ie.score, ie.evaluator_comments
     FROM idea_evaluators ie
     JOIN evaluator e ON ie.evaluator_id = e.id
     WHERE ie.idea_id = ?"
);
$stmt->bind_param("i", $idea_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any results
if ($result->num_rows > 0) {
    $evaluators = [];
    while ($row = $result->fetch_assoc()) {
        $evaluators[] = $row;
    }
    echo json_encode(["success" => "Data retrieved successfully.", "data" => $evaluators]);
} else {
    echo json_encode(["error" => "No evaluators found for the given idea_id."]);
}

$stmt->close();
$conn->close();
?>
