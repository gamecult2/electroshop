<?php
// api/cart/update.php
require_once '../../includes/init.php';
require_once '../../models/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['item_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID and quantity are required']);
    exit;
}

$itemId = (int)$input['item_id'];
$quantity = (int)$input['quantity'];

$cart = new Cart();

try {
    $result = $cart->update($itemId, $quantity);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating cart']);
}
?>
