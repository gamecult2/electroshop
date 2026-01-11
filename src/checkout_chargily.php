<?php
// src/checkout_chargily.php
require_once 'includes/init.php';
require_once 'models/Order.php';
require_once 'services/ChargilyService.php';

// DEBUG LOGGING
$logFile = __DIR__ . '/debug_payment.log';
$logEntry = date('Y-m-d H:i:s') . " - Accessing checkout_chargily.php\n";
$logEntry .= "Session ID: " . session_id() . "\n";
$logEntry .= "Session Data: " . print_r($_SESSION, true) . "\n";
$logEntry .= "Order ID GET: " . ($_GET['order_id'] ?? 'NULL') . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

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
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Save Checkout ID for Order #{$orderId}: " . ($success ? "SUCCESS" : "FAILED") . " (ID: " . $checkout->getId() . ")\n", FILE_APPEND);
    
    // Redirect user to Chargily Payment Page
    header("Location: " . $checkout->getUrl());
    exit;
} else {
    // Handle error (log it and show message)
    die("Failed to initiate payment. Please try again later or contact support.");
}
