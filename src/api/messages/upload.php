<?php
require_once __DIR__ . '/../../includes/init.php';
header('Content-Type: application/json');


require_once __DIR__ . '/../../models/Message.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }
    
    $conversationId = $_POST['conversation_id'] ?? null;
    if (!$conversationId) {
        throw new Exception('Conversation ID required');
    }
    
    $slug = $_POST['slug'] ?? null;
    
    $messageModel = new Message();
    $filePath = $messageModel->uploadAttachment($_FILES['file'], $conversationId, $slug);
    
    echo json_encode([
        'success' => true,
        'file_path' => $filePath,
        'file_name' => basename($filePath)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
