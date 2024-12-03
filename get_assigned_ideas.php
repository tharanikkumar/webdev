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

// Query to fetch assigned ideas for the evaluator
$query = "
    SELECT i.id, i.student_name, i.school, i.idea_title, i.status_id, i.theme_id, i.type, i.idea_description, i.assigned_count
    FROM ideas i
    JOIN idea_evaluators ie ON i.id = ie.idea_id
    WHERE ie.evaluator_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluator_id);
$stmt->execute();
$result = $stmt->get_result();

$ideas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Return the ideas as JSON
echo json_encode($ideas);
?>
