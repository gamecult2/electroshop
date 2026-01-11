<?php
// src/services/ChargilyService.php

use Chargily\ChargilyPay\ChargilyPay;
use Chargily\ChargilyPay\Auth\Credentials;

// Fix for WAMP SSL Error
$cacert = __DIR__ . '/../includes/cacert.pem';
if (file_exists($cacert)) {
    ini_set('curl.cainfo', $cacert);
    ini_set('openssl.cafile', $cacert);
}

class ChargilyService {
    private $client;

    public function __construct() {
        // Fetch settings from database with robust fallback to constants
        $mode = get_setting('chargily_mode');
        if (!$mode) $mode = defined('CHARGILY_MODE') ? CHARGILY_MODE : 'test';

        $public = get_setting('chargily_public_key');
        if (!$public) $public = defined('CHARGILY_PUBLIC_KEY') ? CHARGILY_PUBLIC_KEY : '';

        $secret = get_setting('chargily_secret_key');
        if (!$secret) $secret = defined('CHARGILY_SECRET_KEY') ? CHARGILY_SECRET_KEY : '';
        
        if (empty($public) || empty($secret)) {
            $logFile = __DIR__ . '/../debug_payment.log';
            $logEntry = date('Y-m-d H:i:s') . " - API Keys Missing! Public: " . (empty($public)?'NO':'YES') . ", Secret: " . (empty($secret)?'NO':'YES') . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }

        $this->client = new ChargilyPay(new Credentials([
            "mode" => $mode,
            "public" => $public,
            "secret" => $secret,
        ]));
    }

    /**
     * Create a checkout session for an order
     * 
     * @param int $orderId
     * @param float $totalAmount
     * @param string $currency
     * @return string|null Checkout URL or null on failure
     */
    public function createPayment($orderData, $currency = 'dzd') {
        try {
            // Check if input is just ID/Amount (legacy support) or array
            $orderId = is_array($orderData) ? ($orderData['id'] ?? 0) : $orderData;
            $totalAmount = is_array($orderData) ? ($orderData['total_amount'] ?? 0) : func_get_arg(1);
            // currency might be 3rd arg if legacy call
            
            // Debug: Start Payment Creation
            $logFile = __DIR__ . '/../debug_payment.log';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Starting Payment for Order #{$orderId}\n", FILE_APPEND);

            // 1. Create Customer (Use provided data if available)
            $customerId = null;
            if (is_array($orderData)) {
                try {
                    // Extract Phone and Address
                    $billingAddr = is_string($orderData['billing_address']) ? json_decode($orderData['billing_address'], true) : ($orderData['billing_address'] ?? []);
                    $shippingAddr = is_string($orderData['shipping_address']) ? json_decode($orderData['shipping_address'], true) : ($orderData['shipping_address'] ?? []);
                    
                    // Prioritize billing phone, then shipping, then user phone (if joined)
                    $phone = $billingAddr['phone_number'] ?? ($shippingAddr['phone_number'] ?? ($orderData['phone'] ?? null));
                    
                    $customerPayload = [
                        "name" => $orderData['customer_name'] ?? 'Guest Customer',
                        "email" => $orderData['customer_email'] ?? 'guest@example.com',
                        "phone" => $phone,
                        "address" => [
                            "country" => "dz", // Default to Algeria
                            "state" => $orderData['wilaya'] ?? ($billingAddr['wilaya'] ?? 'Alger'),
                            "address" => trim(($orderData['commune'] ?? '') . ' ' . ($billingAddr['street_address'] ?? '')),
                        ]
                    ];
                    
                    // Filter empty values
                    if (empty($customerPayload['phone'])) unset($customerPayload['phone']);
                    
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Creating Customer: " . json_encode($customerPayload) . "\n", FILE_APPEND);

                    $customer = $this->client->customers()->create($customerPayload);
                    if ($customer) {
                        $customerId = $customer->getId();
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Customer Created: {$customerId}\n", FILE_APPEND);
                    }
                } catch (\Exception $custEx) {
                    // Log but proceed without customer if creation fails (optional)
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Customer Creation Failed: " . $custEx->getMessage() . "\n", FILE_APPEND);
                }
            }

            // 2. Prepare URLs
            $successUrl = str_replace(' ', '%20', SITE_URL . "/order_status.php?status=success&id=" . $orderId);
            $failureUrl = str_replace(' ', '%20', SITE_URL . "/order_status.php?status=failed&error=payment_declined&id=" . $orderId);
            
            // 3. Create Checkout
            $checkoutData = [
                "amount" => $totalAmount,
                "currency" => $currency,
                "success_url" => $successUrl,
                "failure_url" => $failureUrl,
                "customer_id" => $customerId, // Attach the customer
                "metadata" => [
                    "order_id" => $orderId
                ],
                "locale" => "en",
            ];

            // Remove null customer_id if creation failed
            if (!$customerId) unset($checkoutData['customer_id']);

            $checkout = $this->client->checkouts()->create($checkoutData);

            // Debug: Log checkout URL
            $checkoutUrl = $checkout->getUrl();
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Checkout URL: {$checkoutUrl}\n", FILE_APPEND);

            return $checkout;
        } catch (\Exception $e) {
            error_log("Chargily Payment Error: " . $e->getMessage());
            
            // Debug Log
            $logFile = __DIR__ . '/../debug_payment.log';
            $logEntry = date('Y-m-d H:i:s') . " - Chargily Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            
            return null;
        }
    }

    /**
     * Get checkout details by ID
     * 
     * @param string $checkoutId
     * @return \Chargily\ChargilyPay\Elements\CheckoutElement|null
     */
    public function getCheckout($checkoutId) {
        try {
            return $this->client->checkouts()->get($checkoutId);
        } catch (\Exception $e) {
            error_log("Chargily Get Checkout Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * List recent checkouts
     * 
     * @param int $per_page
     * @param int $page
     * @return \Chargily\ChargilyPay\Elements\PaginationElement|null
     */
    public function listCheckouts($per_page = 20, $page = 1) {
        try {
            return $this->client->checkouts()->all($per_page, $page);
        } catch (\Exception $e) {
            error_log("Chargily List Checkouts Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the client instance if needed directly
     */
    public function getClient() {
        return $this->client;
    }
}
