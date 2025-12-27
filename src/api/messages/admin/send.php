<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../models/Message.php';
require_once __DIR__ . '/../../../models/Conversation.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $conversationId = $data['conversation_id'] ?? null;
    $customerId = $data['customer_id'] ?? null;
    $message = trim($data['message'] ?? '');
    
    if ((!$conversationId && !$customerId) || empty($message)) {
        throw new Exception('Conversation ID (or Customer ID) and message are required');
    }
    
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
    
    // If no conversation ID, create new conversation
    if (!$conversationId && $customerId) {
        $conversationModel = new Conversation();
        // Check if open conversation exists first? create() handles logic usually but let's be sure.
        // The Conversation model's create() method creates a NEW one usually, or checks product.
        // Here we just want a generic conversation.
        $conversationId = $conversationModel->create($customerId, null, null);
    }
    
    $messageModel = new Message();
    $messageId = $messageModel->send($conversationId, 'admin', $adminId, $message);
    
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
