<?php
session_start();
header('Content-Type: application/json');

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
    if (!$conversationId) {
        throw new Exception('Conversation ID required');
    }
    
    $conversationModel = new Conversation();
    
    // Update status if provided
    if (isset($data['status'])) {
        $conversationModel->updateStatus($conversationId, $data['status']);
    }
    
    // Update priority if provided
    if (isset($data['priority'])) {
        $conversationModel->updatePriority($conversationId, $data['priority']);
    }
    
    // Update assignment if provided
    if (isset($data['assigned_to'])) {
        $conversationModel->assignTo($conversationId, $data['assigned_to'] ?: null);
    }
    
    // Update tags if provided
    if (isset($data['tags'])) {
        $conversationModel->updateTags($conversationId, $data['tags']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
