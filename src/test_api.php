<?php
// src/test_api.php
$_SESSION['customer_id'] = 1; // Mock user 1
$_SESSION['initialized'] = true;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_first_name'] = 'Test';
$_SESSION['user_last_name'] = 'User';

// Define constants that init.php might expect or use
define('INIT_PHP_INCLUDED', true);
require_once 'includes/functions.php';
require_once 'db_connect.php';
require_once 'models/Conversation.php';

try {
    $conversationModel = new Conversation();
    $userId = 1;
    $limit = 10;
    $offset = 0;
    
    echo "Running getUserConversationsWithPagination...\n";
    $conversations = $conversationModel->getUserConversationsWithPagination($userId, $limit, $offset);
    echo "Success! Result type: " . gettype($conversations) . "\n";
    echo "Count: " . count($conversations) . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
