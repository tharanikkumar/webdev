<?php
echo "<pre>";
print_r($_COOKIE); 
echo "</pre>";
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow your frontend origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allow headers (like Content-Type and Authorization)
header("Access-Control-Allow-Credentials: true");  // Allow cookies and credentials

echo "Cookie before deletion: " . (isset($_COOKIE['auth_token']) ? $_COOKIE['auth_token'] : 'Not set') . "<br>";

// If it's a preflight OPTIONS request, return 200 OK without further processing
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    
    // Return a success message
    echo json_encode(["message" => "Logged out successfully"]);
} else {
    // If there's no 'auth_token' cookie, return an error message
    echo json_encode(["error" => "No authentication token found"]);
}
?>
