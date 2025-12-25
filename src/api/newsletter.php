<?php
// api/newsletter.php - Newsletter subscription API

header('Content-Type: application/json');
require_once '../includes/functions.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$endpoint = 'newsletter';

if (is_rate_limited($ip, $endpoint)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
    exit;
}

record_api_request($ip, $endpoint);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    try {
        $result = subscribe_newsletter($email);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred while subscribing']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
