<?php


header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include ('db.php');
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "sic";

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

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

function getAllEvaluators() {

    global $conn; // Make sure you're referring to the global $db object

  

    $query = "SELECT * FROM evaluator";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die(json_encode(["error" => "Failed to prepare SQL query."]));
    }

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $evaluators = [];
        while ($row = $result->fetch_assoc()) {
            $evaluators[] = $row;
        }
        return $evaluators;
    } else {
        return [];
    }
}

$decodedUser = checkJwtCookie();
$evaluators = getAllEvaluators();

echo json_encode([
    "status" => "success",
    "evaluators" => $evaluators,

]);
?>


