<?php
// api/compare.php - Product comparison API

header('Content-Type: application/json');
require_once '../includes/init.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$endpoint = 'compare';

if (is_rate_limited($ip, $endpoint)) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit;
}

record_api_request($ip, $endpoint);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'add';
    $productId = $input['product_id'] ?? null;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    
    // Initialize comparison session if needed
    if (!isset($_SESSION['product_comparison'])) {
        $_SESSION['product_comparison'] = [];
    }
    
    switch ($action) {
        case 'add':
            if (count($_SESSION['product_comparison']) < 4) {
                if (!in_array($productId, $_SESSION['product_comparison'])) {
                    $_SESSION['product_comparison'][] = $productId;
                    echo json_encode([
                        'success' => true,
                        'message' => t('added_to_comparison'),
                        'count' => count($_SESSION['product_comparison'])
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => t('already_in_comparison')
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => t('comparison_limit_reached')
                ]);
            }
            break;
            
        case 'remove':
            $_SESSION['product_comparison'] = array_filter(
                $_SESSION['product_comparison'], 
                function($id) use ($productId) { 
                    return $id != $productId; 
                }
            );
            
            echo json_encode([
                'success' => true,
                'message' => t('removed_from_comparison'),
                'count' => count($_SESSION['product_comparison'])
            ]);
            break;
            
        case 'clear':
            $_SESSION['product_comparison'] = [];
            echo json_encode([
                'success' => true,
                'message' => t('comparison_cleared'),
                'count' => 0
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return current comparison items
    $productIds = $_SESSION['product_comparison'] ?? [];
    $products = [];
    
    if (!empty($productIds)) {
        global $pdo;
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $sql = "SELECT id, name_en, final_price, discount_percentage, stock_quantity 
                FROM products 
                WHERE id IN ($placeholders) AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'count' => count($products)
    ]);
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
