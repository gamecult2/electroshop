<?php
require_once __DIR__ . '/../../includes/init.php';
header('Content-Type: application/json');

require_once __DIR__ . '/../../models/Conversation.php';
require_once __DIR__ . '/../../models/Message.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = get_current_user_id();
$conversationId = $_GET['conversation_id'] ?? null;

if (!$conversationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID required']);
    exit;
}

try {
    $conversationModel = new Conversation();
    $messageModel = new Message();
    
    // Verify conversation ownership
    $conversation = $conversationModel->getById($conversationId, $userId);
    if (!$conversation) {
        throw new Exception('Conversation not found');
    }
    
    // Get messages
    $messages = $messageModel->getConversationMessages($conversationId);
    
    // Mark as read
    $conversationModel->markAsRead($conversationId, 'customer');
    
    // Get typing indicator
    $typingIndicators = $messageModel->getTypingIndicator($conversationId, 'customer');
    $adminTyping = !empty($typingIndicators);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'conversation' => $conversation,
        'admin_typing' => $adminTyping
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
