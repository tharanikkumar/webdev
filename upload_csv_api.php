<?php
header('Content-Type: application/json');
include('db.php');  // Ensure this file contains your database connection logic.
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); 

// Check if file is uploaded
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error uploading the file.']);
        exit;
    }

    // Validate file type (allow only CSV files)
    $allowedTypes = ['text/csv', 'application/csv', 'application/vnd.ms-excel', 'text/plain'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only CSV files are allowed.']);
        exit;
    }

    // Set the upload directory (ensure this is writable)
    $uploadDirectory = '/Applications/XAMPP/xamppfiles/htdocs/uploads/';
    $filePath = $uploadDirectory . basename($file['name']);

    // Move the file to the server directory
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Error moving the file.']);
        exit;
    }

    // Process the CSV file
    if (($csvFile = fopen($filePath, 'r')) !== FALSE) {
        $header = fgetcsv($csvFile);  // Skip the header row
        $response = [];

        while (($data = fgetcsv($csvFile)) !== FALSE) {
            // Extract data from CSV columns
            $student_name = $data[0];
            $school = $data[1];
            $idea_title = $data[2];
            $status_id = $data[3];
            $theme_id = $data[4];
            $type = $data[5];
            $idea_description = $data[6];
            $idea_id = $data[7];
            $evaluator_id = $data[8];

            // Check if the evaluator_id and idea_id exist in the idea_evaluators table
            $stmt = $conn->prepare("SELECT * FROM idea_evaluators WHERE idea_id = ? AND evaluator_id = ?");
            $stmt->bind_param("ii", $idea_id, $evaluator_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the idea status in the ideas table
                $updateStmt = $conn->prepare("UPDATE ideas SET status_id = 1 WHERE id = ?");
                $updateStmt->bind_param("i", $idea_id);
                $updateStmt->execute();

                // Fetch the updated idea information
                $ideaQuery = $conn->prepare("SELECT student_name, idea_title, status_id FROM ideas WHERE id = ?");
                $ideaQuery->bind_param("i", $idea_id);
                $ideaQuery->execute();
                $ideaResult = $ideaQuery->get_result();
                $idea = $ideaResult->fetch_assoc();

                // Prepare response
                $response[] = [
                    'student_name' => $idea['student_name'],
                    'idea_title' => $idea['idea_title'],
                    'view' => 'View link',  // Add actual link if needed
                    'action' => 'Assigned',
                    'status_id' => 1
                ];
            }
        }

        fclose($csvFile);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'File processed successfully!',
            'data' => $response
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error reading the CSV file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
}
?>
