<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$secretKey = "your_secret_key"; // This should match the registration secret key

function verifyJWTToken($secretKey) {
    if (isset($_COOKIE['admin_token'])) {
        try {
            // Decode the token from the cookie
            $jwt = $_COOKIE['admin_token'];
            $decoded = JWT::decode($jwt, $secretKey, ['HS256']);
            return $decoded; // Return decoded token for further use if needed
        } catch (Exception $e) {
            // Token is invalid or expired
            echo json_encode(["error" => "Invalid or expired token"]);
            exit;
        }
    } else {
        echo json_encode(["error" => "Authorization token is required"]);
        exit;
    }
}
?>
