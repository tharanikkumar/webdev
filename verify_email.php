<?php
require 'db.php';  // Include your database connection file

// Check if the token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Query to find the evaluator with the provided token
    $stmt = $conn->prepare("SELECT id FROM evaluator WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If the token is valid, activate the evaluator's account
        $stmt->bind_result($id);
        $stmt->fetch();

        // Update evaluator status to active and clear verification token
        $updateStmt = $conn->prepare("UPDATE evaluator SET evaluator_status = 1, verification_token = NULL WHERE id = ?");
        $updateStmt->bind_param("i", $id);
        if ($updateStmt->execute()) {
            echo "Your email has been verified successfully! You can now login.";
        } else {
            echo "Failed to verify email.";
        }
    } else {
        echo "Invalid or expired verification token.";
    }
} else {
    echo "No verification token provided.";
}

$conn->close();
?>
