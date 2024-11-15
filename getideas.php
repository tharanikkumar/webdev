<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
require 'vendor/autoload.php'; // Include JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = 'sic'; // Replace with your secret key

// Middleware function to validate the admin session using cookies
function checkJwtCookie() {
    global $secretKey;

    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];

        try {
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            if (!isset($decoded->role) || $decoded->role !== 'admin') {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode(["error" => "You are not an admin."]);
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

// Validate the admin using the middleware
checkJwtCookie();

// Get JSON input and decode it
$input = json_decode(file_get_contents("php://input"), true);

// Extract idea_id from request body
$idea_id = $input['idea_id'] ?? null;

// Check if idea_id is provided
if (empty($idea_id)) {
    echo json_encode(["error" => "Idea ID is required."]);
    exit;
}

// Fetch the idea by ID
$stmt = $conn->prepare("SELECT * FROM ideas WHERE id = ?");
$stmt->bind_param("i", $idea_id);
$stmt->execute();
$result = $stmt->get_result();
$idea = $result->fetch_assoc();
$stmt->close();

// Check if idea exists
if (!$idea) {
    echo json_encode(["error" => "Idea not found."]);
    exit;
}

// Return the idea as JSON
echo json_encode(["success" => true, "idea" => $idea]);
?>
