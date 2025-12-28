<?php
// api/orders/get_items.php - Get items for a specific order

header('Content-Type: application/json');
require_once '../../includes/init.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$orderId = (int)($_GET['order_id'] ?? 0);
$customerId = get_current_user_id();

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Order ID is required']);
    exit;
}

// Ensure the order belongs to the customer
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$orderId, $customerId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access to order items']);
    exit;
}

try {
    // Fetch order items with product images and review status
    $sql = "SELECT oi.product_id, oi.product_name, 
                   (SELECT image_url FROM product_images WHERE product_id = oi.product_id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as product_image,
                   r.id as review_id, r.rating as review_rating, r.review_text, r.is_approved as review_status
            FROM order_items oi
            LEFT JOIN reviews r ON r.product_id = oi.product_id AND r.customer_id = ?
            WHERE oi.order_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId, $orderId]);
    $items = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch items: ' . $e->getMessage()]);
}
?>
