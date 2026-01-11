<?php
require_once 'includes/init.php';
require_once 'models/Order.php';
$orderModel = new Order();
header('Content-Type: application/json');
echo json_encode([
    'order_79' => $orderModel->getById(79),
    'order_80' => $orderModel->getById(80)
], JSON_PRETTY_PRINT);
?>
