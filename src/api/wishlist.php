<?php
// api/wishlist.php - Wishlist API endpoints

header('Content-Type: application/json');
require_once '../includes/init.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get user's wishlist
        $customerId = get_current_user_id();
        $sql = "SELECT p.*, w.id as wishlist_id, w.created_at as added_date
                FROM wishlists w
                JOIN products p ON w.product_id = p.id
                WHERE w.customer_id = ?
                ORDER BY w.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customerId]);
        $items = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'items' => $items]);
        break;
    
    case 'POST':
        // Toggle wishlist item (Add/Remove)
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        $customerId = get_current_user_id();
        
        // Check if already in wishlist
        $checkSql = "SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$customerId, $productId]);
        
        if ($checkStmt->fetch()) {
            // Already exists, so remove it
            $deleteSql = "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?";
            $deleteStmt = $pdo->prepare($deleteSql);
            $result = $deleteStmt->execute([$customerId, $productId]);
            $action = 'removed';
            $message = t('removed_from_wishlist');
        } else {
            // Does not exist, so add it
            $insertSql = "INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $result = $insertStmt->execute([$customerId, $productId]);
            $action = 'added';
            $message = t('added_to_wishlist');
        }
        
        if ($result) {
            // Get updated count
            $countSql = "SELECT COUNT(*) FROM wishlists WHERE customer_id = ?";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute([$customerId]);
            $count = $countStmt->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'message' => $message, 
                'action' => $action, 
                'count' => $count
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;
    
    case 'DELETE':
        // Remove from wishlist
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        $customerId = get_current_user_id();
        $sql = "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$customerId, $productId]);
        
        if ($result) {
            // Get updated count
            $countSql = "SELECT COUNT(*) FROM wishlists WHERE customer_id = ?";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute([$customerId]);
            $count = $countStmt->fetchColumn();

            echo json_encode(['success' => true, 'message' => t('removed_from_wishlist'), 'action' => 'removed', 'count' => $count]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to remove from wishlist']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
