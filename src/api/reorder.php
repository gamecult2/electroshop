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

if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

foreach ($items as $item) {
    // Check if product still exists and has stock (simplified check)
    // Ideally use Product model to check current stock
    // $product = $productModel->getById($item['product_id']);
    // if (!$product || $product['stock_quantity'] < 1) { ... }
    
    // Add to cart session
    $productId = $item['product_id'];
    $variantId = $item['variant_id'] ?? null; // Handle if null properly
    $quantity = 1; // Or $item['quantity'] if we want to add full quantity

    $key = $variantId ? "{$productId}_{$variantId}" : $productId;
    
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity
        ];
    }
    $addedCount++;
}

if ($addedCount > 0) {
    echo json_encode([
        'success' => true, 
        'message' => "$addedCount items added to cart", 
        'cart_count' => count($_SESSION['cart'])
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No items could be added']);
}
?>
