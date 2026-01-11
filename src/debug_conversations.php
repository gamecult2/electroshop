<?php
// src/debug_conversations.php
require_once 'includes/init.php';
require_once 'models/Conversation.php';

header('Content-Type: text/plain');

// if (!is_logged_in()) {
//     die("Error: Not logged in. Please login first.");
// }

// Find a valid customer ID
$stmt = $GLOBALS['pdo']->query("SELECT id FROM customers LIMIT 1");
$customer = $stmt->fetch();
if (!$customer) die("Error: No customers in database.");

$userId = $customer['id'];
echo "Debugging for User ID: $userId\n\n";

try {
    $conversationModel = new Conversation();
    
    echo "Testing getUserConversationsWithPagination(limit=10, offset=0)...";
    $conversations = $conversationModel->getUserConversationsWithPagination($userId, 10, 0);
    
    echo "Success! Found " . count($conversations) . " conversations.\n";
    print_r($conversations);
    
    echo "\nTesting getUserConversationsCount()...";
    $count = $conversationModel->getUserConversationsCount($userId);
    echo "Count: $count\n";

} catch (PDOException $e) {
    echo "SQL ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "GENERAL ERROR: " . $e->getMessage() . "\n";
}
?>
