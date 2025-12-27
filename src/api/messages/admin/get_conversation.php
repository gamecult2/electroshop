<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../models/Conversation.php';
require_once __DIR__ . '/../../../models/Message.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conversationId = $_GET['id'] ?? null;

if (!$conversationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID required']);
    exit;
}

try {
    $conversationModel = new Conversation();
    $messageModel = new Message();
    
    // Get conversation details
    $conversation = $conversationModel->getById($conversationId);
    if (!$conversation) {
        throw new Exception('Conversation not found');
    }
    
    // Get messages
    $messages = $messageModel->getConversationMessages($conversationId);
    
    // Mark as read for admin
    $conversationModel->markAsRead($conversationId, 'admin');
    
    // Get canned responses
    global $pdo;
    $cannedStmt = $pdo->query("SELECT * FROM canned_responses WHERE is_active = 1 ORDER BY usage_count DESC LIMIT 10");
    $cannedResponses = $cannedStmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'conversation' => $conversation,
        'messages' => $messages,
        'canned_responses' => $cannedResponses
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
