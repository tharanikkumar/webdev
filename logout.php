<?php
// Enable CORS for all domains (You can limit this to specific origins for security)
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow your frontend origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allow headers (like Content-Type and Authorization)
header("Access-Control-Allow-Credentials: true");  // Allow cookies and credentials

// If it's a preflight OPTIONS request, return 200 OK without further processing
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Your logout logic
if (isset($_COOKIE['auth_token'])) {
    // Remove the 'auth_token' cookie by setting its expiration to the past
    setcookie('auth_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);  // Ensure the path and secure attributes match
    echo json_encode(["message" => "Logged out successfully"]);
} else {
    echo json_encode(["error" => "No authentication token found"]);
}
?>
