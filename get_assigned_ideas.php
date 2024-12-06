<?php
// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");



// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
?>
