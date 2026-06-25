<?php
// newsletter-subscribe.php
// Secure endpoint to handle email newsletter subscriptions

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/admin/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST request.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Email address is required.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please provide a valid email address.'
    ]);
    exit;
}

try {
    $db = getDBConnection();
    
    // Check if email already subscribed
    $stmtCheck = $db->prepare("SELECT id FROM subscribers WHERE email = :email LIMIT 1");
    $stmtCheck->execute([':email' => $email]);
    if ($stmtCheck->fetch()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'You are already subscribed to the CloudCush Circle!'
        ]);
        exit;
    }
    
    // Insert new subscription
    $stmtInsert = $db->prepare("INSERT INTO subscribers (email) VALUES (:email)");
    $stmtInsert->execute([':email' => $email]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! You have successfully subscribed to the CloudCush Circle.'
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to process subscription. Please try again later.'
    ]);
    exit;
}
