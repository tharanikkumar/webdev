<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';
require 'vendor/autoload.php';
// Allow CORS
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Read the raw POST data (JSON)
$data = json_decode(file_get_contents('php://input'), true);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure that the expected fields are present in the incoming data
if (isset($data['student_name'], $data['school'], $data['idea_title'], $data['theme_id'], $data['type'], $data['idea_description'], $data['evaluator_id_1'], $data['evaluator_id_2'], $data['evaluator_id_3'])) {
    // Get the data from the JSON
    $student_name = $data['student_name'];
    $school = $data['school'];
    $idea_title = $data['idea_title'];
    $theme_id = $data['theme_id'];
    $type = $data['type'];
    $idea_description = $data['idea_description'];
    $evaluator_id_1 = $data['evaluator_id_1'];
    $evaluator_id_2 = $data['evaluator_id_2'];
    $evaluator_id_3 = $data['evaluator_id_3'];


    $status_id = 3;

    // Insert the idea into the database with the initial status_id as 3
    $insertStmt = $conn->prepare("INSERT INTO ideas (student_name, school, idea_title, status_id, theme_id, type, idea_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssiiss", $student_name, $school, $idea_title, $status_id, $theme_id, $type, $idea_description);

    if ($insertStmt->execute()) {
        $idea_id = $insertStmt->insert_id; // Get the auto-generated idea_id

        // Now insert the individual evaluator IDs into the idea_evaluators table
        $insertEvaluatorStmt = $conn->prepare("INSERT INTO idea_evaluators (idea_id, evaluator_id) VALUES (?, ?)");

        // Insert evaluator_id_1, evaluator_id_2, and evaluator_id_3 individually if they are provided
        if (!empty($evaluator_id_1)) {
            $insertEvaluatorStmt->bind_param("ii", $idea_id, $evaluator_id_1);
            if (!$insertEvaluatorStmt->execute()) {
                echo json_encode(['error' => 'Error assigning evaluator_id_1 to the idea.']);
                exit;
            }
        }

        if (!empty($evaluator_id_2)) {
            $insertEvaluatorStmt->bind_param("ii", $idea_id, $evaluator_id_2);
            if (!$insertEvaluatorStmt->execute()) {
                echo json_encode(['error' => 'Error assigning evaluator_id_2 to the idea.']);
                exit;
            }
        }

        if (!empty($evaluator_id_3)) {
            $insertEvaluatorStmt->bind_param("ii", $idea_id, $evaluator_id_3);
            if (!$insertEvaluatorStmt->execute()) {
                echo json_encode(['error' => 'Error assigning evaluator_id_3 to the idea.']);
                exit;
            }
        }

        // Update the status of the idea to 2 (Assigned) after adding evaluators
        $updateStatusStmt = $conn->prepare("UPDATE ideas SET status_id = 2 WHERE id = ?");
        $updateStatusStmt->bind_param("i", $idea_id);
        $updateStatusStmt->execute();

        echo json_encode(['success' => 'Idea registered and evaluators assigned successfully.']);
    } else {
        echo json_encode(['error' => 'Error inserting idea into the database.']);
    }
} else {
    echo json_encode(['error' => 'Missing required fields.']);
}
?>
