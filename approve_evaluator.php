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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$secretKey = "sic";

// Function to validate JWT and check admin role
function checkJwtCookie() {
    global $secretKey;

    // Check if the auth token is stored in the cookie
    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token'];
        error_log("JWT Token: " . $_COOKIE['auth_token']);

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
        // If the auth token is missing, return a 401 Unauthorized status with a message
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(["error" => "Unauthorized - No token provided."]);
        exit();
    }
}

// Read and decode the JSON payload (only the evaluator ID)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Ensure the evaluator ID is provided in the request
if (empty($data['evaluator_id'])) {
    die(json_encode(["error" => "Evaluator ID is required for approval."]));
}

// Get the admin's email from the session (cookie)
$adminEmail = checkJwtCookie(); // Validate the admin session using cookies
$evaluator_id = $data['evaluator_id'];

// Check if the evaluator exists and is pending approval
$stmt = $conn->prepare("SELECT id FROM evaluator WHERE id = ? AND evaluator_status = 0");
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
        // Fetch the evaluator's email
        $stmt = $conn->prepare("SELECT email FROM evaluator WHERE id = ?");
        $stmt->bind_param("i", $evaluator_id);
        $stmt->execute();
        $stmt->bind_result($evaluatorEmail);
        $stmt->fetch();

        // Send email with sign-in link
        sendEmail($evaluatorEmail);

        echo json_encode(["message" => "Evaluator approved successfully! An email has been sent to the evaluator.", "evaluator_id" => $evaluator_id]);
    } else {
        // Handle errors (e.g., if something went wrong during update)
        echo json_encode(["error" => "Failed to approve evaluator. " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();

// Function to send email with sign-in link
function sendEmail($evaluatorEmail) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to Gmail
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username   = 'tharanikkumar6@gmail.com'; // SMTP username (Your Gmail address)
        $mail->Password   = 'srze tvqy enbt imqc'; // App password generated for Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // Port for TLS

        // Debugging output (Enable to view SMTP conversation)
        $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages

        // Recipients
        $mail->setFrom('tharanikkumar6@gmail.com', 'Admin'); // Sender's email and name
        $mail->addAddress($evaluatorEmail); // Recipient's email

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Your Account has been Approved';
        $mail->Body    = 'Dear Evaluator, <br><br>This email confirms that your account has been approved.<br><br>Please click the link below to log in:<br><a href="http://localhost/webdev/signin_evaluator.php">Log in to your account</a><br><br>Regards,<br>Admin Team';

        // Send email
        if ($mail->send()) {
            echo "Email sent successfully!";
        } else {
            echo "Email could not be sent.";
        }

    } catch (Exception $e) {
        // Output the error
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
