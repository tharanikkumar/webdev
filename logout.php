<?php
// Enable CORS headers to allow frontend requests
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow your frontend origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allow headers (like Content-Type and Authorization)
header("Access-Control-Allow-Credentials: true");  // Allow cookies and credentials

// Display the current cookies for debugging purposes
echo "<pre>";
print_r($_COOKIE); 
echo "</pre>";

// Check for the presence of the 'auth_token' or 'auth_token1' cookies
echo "Cookie before deletion: " . (isset($_COOKIE['auth_token1']) ? $_COOKIE['auth_token1'] : 'Not set') . "<br>";
echo "Cookie auth_token: " . (isset($_COOKIE['auth_token']) ? $_COOKIE['auth_token'] : 'Not set') . "<br>";

// If either 'auth_token' or 'auth_token1' cookie is set, proceed to logout
if (isset($_COOKIE['auth_token1']) || isset($_COOKIE['auth_token'])) {
    // Expire both cookies by setting their expiration time to a past time
    setcookie("auth_token", "", time() - 3600, "/"); // Expired cookie
    setcookie("auth_token1", "", time() - 3600, "/"); // Expired cookie

    // Return a success message in JSON format
    echo json_encode(["message" => "Logged out successfully"]);
} else {
    // If neither 'auth_token' nor 'auth_token1' cookie is found, return an error message
    echo json_encode(["error" => "No authentication token found"]);
}
?>
