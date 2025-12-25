<?php
require_once 'includes/init.php';
require_once 'models/Cart.php';

header('Content-Type: application/json');

$cart = new Cart();
$isLoggedIn = is_logged_in();
$customerId = get_current_user_id();
$sessionId = session_id();

$cartItems = $cart->getItems();
$itemCount = $cart->getItemCount();
$isEmpty = $cart->isEmpty();

echo json_encode([
    'session' => [
        'id' => $sessionId,
        'status' => session_status(),
        'name' => session_name(),
        'data' => $_SESSION
    ],
    'auth' => [
        'is_logged_in' => $isLoggedIn,
        'customer_id' => $customerId
    ],
    'cart' => [
        'item_count' => $itemCount,
        'is_empty' => $isEmpty,
        'items' => $cartItems
    ],
    'server' => [
        'php_version' => PHP_VERSION,
        'cookie' => $_COOKIE
    ]
], JSON_PRETTY_PRINT);
?>
