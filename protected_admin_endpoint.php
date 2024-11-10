<?php
require 'verify_token.php';
require 'db.php';

// Verify token before accessing the endpoint
$decodedToken = verifyJWTToken($secretKey);

// Proceed with protected logic, such as fetching sensitive data
echo json_encode(["message" => "Access granted to protected admin endpoint"]);
?>
