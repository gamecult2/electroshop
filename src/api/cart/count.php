<?php
// api/cart/count.php
require_once '../../includes/init.php';
require_once '../../models/Cart.php';

header('Content-Type: application/json');

$cart = new Cart();
$count = $cart->getCount();

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>
