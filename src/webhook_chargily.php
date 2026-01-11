<?php
// src/webhook_chargily.php

// Disable any HTML output or error display that might break the JSON response/log
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'includes/init.php';
require_once 'models/Order.php';
require_once 'services/ChargilyService.php';

try {
    $service = new ChargilyService();
    $client = $service->getClient();

    $logFile = __DIR__ . '/debug_payment.log';
    
    // Log raw request for debugging (only headers and method)
    $headers = getallheaders();
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Request Received. Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
    
    // Specifically log if the signature header is present
    $sigHeader = $headers['Chargily-Signature'] ?? $headers['chargily-signature'] ?? $headers['Signature'] ?? $headers['signature'] ?? 'MISSING';
    $rawPayload = file_get_contents('php://input');
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - WEBHOOK: Received Sig: {$sigHeader}\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - WEBHOOK: Raw Body: {$rawPayload}\n", FILE_APPEND);

    // Get the webhook data
    $webhook = $client->webhook()->get();
    
    if ($webhook) {
        $eventType = $webhook->getType();
        $checkout = $webhook->getData();
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Verified Event: {$eventType}\n", FILE_APPEND);
        
        $metadata = $checkout->getMetadata();
        $orderId = (is_array($metadata)) ? ($metadata['order_id'] ?? null) : null;
        
        if (!$orderId) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Error: No Order ID in metadata\n", FILE_APPEND);
            http_response_code(200); // Still 200 to stop retries if logic failed
            echo json_encode(['status' => 'error', 'message' => 'No order ID']);
            exit;
        }

        $orderModel = new Order();
        $checkoutData = $checkout->toArray();

        switch ($eventType) {
            case 'checkout.paid':
                $orderModel->updatePaymentDetails($orderId, 'paid', $checkout->getId(), $checkoutData);
                $orderModel->updateStatus($orderId, 'processing', null, 'Payment confirmed via Chargily Webhook');
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$orderId} PAID (Tx: {$checkout->getId()})\n", FILE_APPEND);
                break;
                
            case 'checkout.failed':
                $orderModel->updatePaymentDetails($orderId, 'failed', $checkout->getId(), $checkoutData);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$orderId} FAILED via Webhook\n", FILE_APPEND);
                break;
                
            case 'checkout.expired':
                $orderModel->updatePaymentDetails($orderId, 'failed', $checkout->getId(), $checkoutData);
                $orderModel->updateStatus($orderId, 'cancelled', null, 'Order cancelled due to payment expiration');
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$orderId} EXPIRED via Webhook\n", FILE_APPEND);
                break;
                
            default:
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Event Ignored: {$eventType}\n", FILE_APPEND);
                break;
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Processed']);
        exit;
    }

    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Error: Invalid Webhook Payload\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);

} catch (Exception $e) {
    http_response_code(400);
    $logFile = __DIR__ . '/debug_payment.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}
