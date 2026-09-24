<?php
// src/checkout_chargily.php
require_once 'includes/init.php';
require_once 'models/Order.php';
require_once 'services/ChargilyService.php';

// 1. Validate Request First
if (!isset($_GET['order_id'])) {
    die("Invalid request: No order ID provided.");
}

$orderId = (int)$_GET['order_id'];

// 2. Fetch Order
$orderModel = new Order();
$order = $orderModel->getById($orderId);

if (!$order) {
    die("Order not found.");
}
if ($order['payment_method'] !== 'chargily') {
    http_response_code(400);
    exit('This order does not use CIB / Edahabia payment.');
}

// 3. Authorization Check
// Use helper function that checks both user_id and customer_id
$userId = get_current_user_id();

if ($userId) {
    // Registered user: must match customer_id
    if ($order['customer_id'] != $userId) {
        die("Unauthorized access to this order.");
    }
} else {
    // Guest user: Order must have null customer_id
    // Security Note: We assume trust because the user was redirected here immediately after placing the order
    // Additional security could involve verifying a session token or cookie set during placeOrder
    if (!empty($order['customer_id'])) {
         // This order belongs to a registered user, so guest cannot pay for it -> redirect to login
         header('Location: login.php?redirect=' . urlencode("checkout_chargily.php?order_id=$orderId"));
         exit;
    }
}

// 4. Check Payment Status
if ($order['payment_status'] === 'paid') {
    header("Location: order_details.php?id=$orderId");
    exit;
}

// 5. Initiate Payment
$chargilyService = new ChargilyService();
$checkout = $chargilyService->createPayment($order);

if ($checkout) {
    // 6. Save Checkout ID to database immediately
    // This allows us to track the payment even if the user skips the return link
    $success = $orderModel->updatePaymentDetails($orderId, 'pending', $checkout->getId(), null);
    
    if (!$success) {
        error_log("Could not save Chargily checkout ID for order #{$orderId}");
        http_response_code(500);
        exit('Payment could not be started. Please try again from your order details.');
    }
    
    // Redirect user to Chargily Payment Page
    header("Location: " . $checkout->getUrl());
    exit;
} else {
    // Handle error (log it and show message)
    die("Failed to initiate payment. Please try again later or contact support.");
}
