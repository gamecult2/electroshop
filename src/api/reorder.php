<?php
// api/reorder.php - Reorder functionality

header('Content-Type: application/json');
require_once '../includes/init.php';
require_once '../models/Order.php';
require_once '../models/Product.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$customerId = get_current_user_id();
$orderModel = new Order();
$productModel = new Product(); // Assuming Product model exists for checking stock

$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID required']);
    exit;
}

// Verify order belongs to user
$order = $orderModel->getById($orderId);
if (!$order || $order['customer_id'] != $customerId) {
    http_response_code(403);
    echo json_encode(['error' => 'Order not found or unauthorized']);
    exit;
}

// Get items
$items = $orderModel->getItems($orderId);
$addedCount = 0;
$errors = [];

require_once '../models/Cart.php';
$cart = new Cart();
foreach ($items as $item) {
    if (!$item['variant_id'] && !empty($item['variant_name'])) {
        $errors[]='An old variant is no longer available. Choose its options again.';
        continue;
    }
    $result=$cart->add($item['product_id'],$item['quantity'],$item['variant_id']);
    if ($result['success']) $addedCount++; else $errors[]=$result['message'];
}

if ($addedCount > 0) {
    echo json_encode([
        'success' => true, 
        'message' => "$addedCount items added to cart", 
        'cart_count' => $cart->getCount(), 'warnings' => $errors
    ]);
} else {
    echo json_encode(['success' => false, 'message' => implode(' ', array_unique($errors)) ?: 'No items could be added']);
}
?>
