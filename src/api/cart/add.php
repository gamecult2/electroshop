<?php
// api/cart/add.php
require_once '../../includes/init.php';
require_once '../../models/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
$selectedOptions = isset($input['selected_options']) ? $input['selected_options'] : null;

$cart = new Cart();

try {
    $result = $cart->add($productId, $quantity, $variantId, $selectedOptions);
    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart (unspecified error)']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
