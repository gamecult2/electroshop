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
    $conversationModel = new Conversation();
    $userId = get_current_user_id();
    
    $count = $conversationModel->getUnreadCount($userId);
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
