<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'db.php';

// Get JSON input and decode it
$input = json_decode(file_get_contents("php://input"), true);

// Extract the data for registering the idea
$student_name = $input['student_name'] ?? null;
$school = $input['school'] ?? null;
$idea_title = $input['idea_title'] ?? null;
$status_id = $input['status_id'] ?? null;
$theme_id = $input['theme_id'] ?? null;
$type = $input['type'] ?? null;
$idea_description = $input['idea_description'] ?? null;

// Check if required fields for registering an idea are present
if (empty($student_name) || empty($school) || empty($idea_title) || empty($status_id) || empty($theme_id) || empty($type) || empty($idea_description)) {
    echo json_encode(["error" => "All idea registration fields are required."]);
    exit;
}

// Start transaction
$conn->autocommit(false);

try {
    // Insert the idea into the ideas table
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
