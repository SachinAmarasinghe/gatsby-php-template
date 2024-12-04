<?php
require_once 'database.php';

// Capture POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validation
if (!isset($input['g-recaptcha-response']) || empty($input['g-recaptcha-response'])) {
    die("reCAPTCHA verification failed.");
}

$recaptchaResponse = $input['g-recaptcha-response'];

// Verify reCAPTCHA
$secretKey = RECAPTCHA_SECRET_KEY;
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
$responseKeys = json_decode($response, true);

// if (!$responseKeys['success']) {
//     die("reCAPTCHA verification failed.");
// }

// Check required fields
$requiredFields = ['email', 'first_name', 'last_name', 'postal_code'];
foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        die("$field is required.");
    }
}

// Prepare data
$email = $input['email'];
$firstName = $input['first_name'];
$lastName = $input['last_name'];
$postalCode = $input['postal_code'];
$howDidYouHear = $input['how_did_you_hear'] ?? '';
$isRealtor = isset($input['is_realtor']) ? (int)$input['is_realtor'] : 0;

// Check for duplicate email
$stmt = $conn->prepare("SELECT id FROM registrations WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Email already registered.");
}
$stmt->close();

// Insert into database
$stmt = $conn->prepare("INSERT INTO registrations (email, first_name, last_name, postal_code, how_did_you_hear, is_realtor) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $email, $firstName, $lastName, $postalCode, $howDidYouHear, $isRealtor);

if ($stmt->execute()) {
    // Send autoresponder email
    $emailTemplate = file_get_contents('email_template.html');
    $emailTemplate = str_replace('{{first_name}}', $firstName, $emailTemplate);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    mail($email, "Thank You for Registering", $emailTemplate, $headers);

    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
