<?php
require 'vendor/autoload.php';
require 'db.php';
use Firebase\JWT\JWT;

$secretKey = "your_secret_key"; // Make sure this matches with register_evaluator.php

function verifyJWTToken($secretKey) {
    if (isset($_COOKIE['evaluator_token'])) {
        try {
            // Decode the token from the cookie
            $jwt = $_COOKIE['evaluator_token'];
            $decoded = JWT::decode($jwt, $secretKey, ['HS256']);
            return $decoded; // Token is valid
        } catch (Exception $e) {
            // Invalid or expired token
            echo json_encode(["error" => "Invalid or expired token"]);
            exit;
        }
    } else {
        echo json_encode(["error" => "Authorization token is required"]);
        exit;
    }
}

// Verify token before accessing the endpoint
$decodedToken = verifyJWTToken($secretKey);

// Protected logic here, for example, fetching sensitive data
echo json_encode(["message" => "Access granted to protected endpoint"]);
?>
