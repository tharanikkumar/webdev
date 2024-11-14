<?php
require 'vendor/autoload.php';
require 'db.php';  // Include your database connection file
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$secretKey = "your_secret_key";

// Middleware to check if the request content type is JSON
function ensureJsonContentType() {
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        echo json_encode(["error" => "Content-Type must be application/json"]);
        exit;
    }
}

// Middleware to parse and validate JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format: " . json_last_error_msg()]);
        exit;
    }
    return $data;
}

// Middleware function to check if required fields are provided and contain non-empty values
function checkRequiredFields($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "$field is required and cannot be empty.";
        }
    }

    if (!empty($errors)) {
        echo json_encode(["errors" => $errors]);
        exit;
    }
}


function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}


function getThemeNamesByIds($theme_ids) {
    global $conn;
    $theme_names = [];
    foreach ($theme_ids as $theme_id) {
 
        $query = "SELECT theme_name FROM theme WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $theme_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($theme_name);
            $stmt->fetch();
            $theme_names[] = $theme_name;
        } else {
            $theme_names[] = null; 
        }
        $stmt->close();
    }

    return $theme_names;
}


function handleSignup($data) {
    global $conn;

  
    $requiredFields = [
        "first_name", "last_name", "gender", "email", "phone_number", 
        "alternate_email", "alternate_phone_number", "college_name", "designation", 
        "total_experience", "city", "state", "knowledge_domain", 
        "theme_preference_1", "theme_preference_2", "theme_preference_3", 
        "expertise_in_startup_value_chain", "role_interested", "password"
    ];
    checkRequiredFields($data, $requiredFields);

    // Retrieve and sanitize input data
    $first_name = sanitizeInput($data['first_name']);
    $last_name = sanitizeInput($data['last_name']);
    $gender = sanitizeInput($data['gender']);
    $email = sanitizeInput($data['email']);
    $phone_number = sanitizeInput($data['phone_number']);
    $password = password_hash(sanitizeInput($data['password']), PASSWORD_DEFAULT);
    $alternate_email = sanitizeInput($data['alternate_email']);
    $alternate_phone_number = sanitizeInput($data['alternate_phone_number']);
    $college_name = sanitizeInput($data['college_name']);
    $designation = sanitizeInput($data['designation']);
    $total_experience = intval($data['total_experience']);
    $city = sanitizeInput($data['city']);
    $state = sanitizeInput($data['state']);
    $knowledge_domain = sanitizeInput($data['knowledge_domain']);
   
$theme_preference_1 = isset($data['theme_preference_1']) ? (int)$data['theme_preference_1'] : 0;
$theme_preference_2 = isset($data['theme_preference_2']) ? (int)$data['theme_preference_2'] : 0;
$theme_preference_3 = isset($data['theme_preference_3']) ? (int)$data['theme_preference_3'] : 0;

    $expertise_in_startup_value_chain = sanitizeInput($data['expertise_in_startup_value_chain']);
    $role_interested = sanitizeInput($data['role_interested']);
    $evaluator_status = 0; 

    // Get the theme names based on the IDs
    $theme_names = getThemeNamesByIds([$theme_preference_1, $theme_preference_2, $theme_preference_3]);

    // Check if any of the themes is invalid (null value)
    if (in_array(null, $theme_names)) {
        echo json_encode(["error" => "One or more theme IDs are invalid."]);
        exit;
    }

    // Prepare SQL statement to insert data into the evaluator table
    $stmt = $conn->prepare("INSERT INTO evaluator (
        first_name, last_name, gender, email, alternate_email, phone_number, 
        alternate_phone_number, college_name, designation, total_experience, 
        city, state, knowledge_domain, theme_preference_1, theme_preference_2, 
        theme_preference_3, expertise_in_startup_value_chain, role_interested, password, evaluator_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare SQL statement: " . $conn->error]);
        exit;
    }

    // Bind parameters for the SQL query
    $stmt->bind_param(
        "ssssssssissssssssssi",
        $first_name, $last_name, $gender, $email, $alternate_email, $phone_number,
        $alternate_phone_number, $college_name, $designation, $total_experience,
        $city, $state, $knowledge_domain, $theme_names[0], $theme_names[1],
        $theme_names[2], $expertise_in_startup_value_chain, $role_interested,
        $password, $evaluator_status
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["message" => "Evaluator registered successfully!"]);
    } else {
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
}

// Main script logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ensureJsonContentType();
    $data = getJsonInput();

    handleSignup($data);
} else {
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
}

$conn->close();
?>
