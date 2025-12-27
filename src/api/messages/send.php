<?php
require_once __DIR__ . '/../../includes/init.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../../models/Conversation.php';
require_once __DIR__ . '/../../models/Message.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to send messages']);
    exit;
}

$userId = get_current_user_id();
$conversationModel = new Conversation();
$messageModel = new Message();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $data['conversation_id'] ?? null;
    $productId = $data['product_id'] ?? null;
    $message = trim($data['message'] ?? '');
    
    if (empty($message)) {
        throw new Exception('Message cannot be empty');
    }
    
    // Validate conversation ownership or create new one
    if ($conversationId) {
        $conversation = $conversationModel->getById($conversationId, $userId);
        if (!$conversation) {
            throw new Exception('Conversation not found');
        }
    } else {
        // Create new conversation
        $conversationId = $conversationModel->create($userId, $productId, $message);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'conversation_id' => $conversationId
        ]);
        exit;
    }
    
    // Send message
    $messageId = $messageModel->send($conversationId, 'customer', $userId, $message);
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'message_id' => $messageId,
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
