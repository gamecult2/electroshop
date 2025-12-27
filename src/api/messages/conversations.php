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
    
    if (!$userId) {
        throw new Exception('User identification failed');
    }

    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    // Get conversations
    // Try paginated first, fallback to all if it fails (for debugging)
    try {
        $conversations = $conversationModel->getUserConversationsWithPagination($userId, $limit, $offset);
    } catch (Exception $e) {
        error_log("Pagination failed, falling back: " . $e->getMessage());
        $conversations = $conversationModel->getUserConversations($userId);
    }

    // Get total count for pagination
    $totalConversations = (int)$conversationModel->getUserConversationsCount($userId);

    // Ensure we always return an array
    if ($conversations === false || $conversations === null) {
        $conversations = [];
    }

    $result = json_encode([
        'success' => true,
        'conversations' => $conversations,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalConversations,
            'total_pages' => $totalConversations > 0 ? ceil($totalConversations / $limit) : 1
        ]
    ], JSON_UNESCAPED_UNICODE);

    if ($result === false) {
        throw new Exception('JSON encoding failed: ' . json_last_error_msg());
    }

    echo $result;

} catch (Exception $e) {
    error_log("Messages API Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
