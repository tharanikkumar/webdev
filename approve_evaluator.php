<?php
// Handle preflight OPTIONS request for CORS
// Handle preflight OPTIONS request for CORS


// Handle the actual request after OPTIONS is complete
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow specific origin
header("Access-Control-Allow-Credentials: true");  // Allow credentials (cookies)
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");  // Allowed methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers

// Include dependencies
require 'vendor/autoload.php';
require 'db.php';



use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";

// Function to validate JWT and check admin role
function checkJwtCookie() {
    global $secretKey;
 

    // Check if the auth_token is stored in the cookie
    if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];
        error_log("JWT Token: " . $_COOKIE['auth_token']);

        try {
            // Decode the JWT token to verify and check the role
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            // Check if the role is 'admin'
            if (!isset($decoded->role) || $decoded->role !== 'admin') {
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


// Get evaluator_id from query parameter
$evaluator_id = $_GET['evaluator_id'];

// Check JWT cookie (validate if user is an admin)
$adminData = checkJwtCookie();

// SQL query to check if evaluator exists and is in "Pending" state (status 3)
$stmt = $conn->prepare("SELECT id FROM evaluator WHERE id = ? AND evaluator_status = 3");
$stmt->bind_param("i", $evaluator_id);
$stmt->execute();
$result = $stmt->get_result();

// If evaluator not found or already approved
if ($result->num_rows === 0) {
    echo json_encode(["error" => "Evaluator ID not found or already approved."]);
} else {
    // Approve the evaluator (set status to 1)
    $stmt = $conn->prepare("UPDATE evaluator SET evaluator_status = 1 WHERE id = ?");
    $stmt->bind_param("i", $evaluator_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Evaluator approved successfully!", "evaluator_id" => $evaluator_id]);
    } else {
        // Handle errors (e.g., if something went wrong during update)
        echo json_encode(["error" => "Failed to approve evaluator. " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>
