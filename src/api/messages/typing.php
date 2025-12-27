<?php
require_once __DIR__ . '/../../includes/init.php';
header('Content-Type: application/json');

require_once __DIR__ . '/../../models/Conversation.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $data['conversation_id'] ?? null;
    
    if (!$conversationId) {
        throw new Exception('Conversation ID required');
    }
    
    $messageModel = new Message();
    $messageModel->setTypingIndicator($conversationId, 'customer', get_current_user_id());
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
