<?php
// api/shipping.php - Shipping calculation API

header('Content-Type: application/json');
require_once '../includes/functions.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$endpoint = 'shipping';

if (is_rate_limited($ip, $endpoint)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
    exit;
}

record_api_request($ip, $endpoint);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $wilaya = $input['wilaya'] ?? '';
    $weight = $input['weight'] ?? 0.0;
    $cartAmount = $input['cart_amount'] ?? 0.0;
    $productIds = $input['product_ids'] ?? [];
    
    if (empty($wilaya)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Wilaya is required']);
        exit;
    }
    
    $shippingCost = calculateShippingCost($wilaya, $weight, $cartAmount);
    
    if ($shippingCost !== false) {
        echo json_encode([
            'success' => true,
            'shipping_cost' => $shippingCost,
            'estimated_days' => $shippingCost['delivery_days'] ?? 5
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Shipping not available for this location']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function calculateShippingCost($wilaya, $weight, $cartAmount) {
    global $pdo;
    
    // Get shipping rate for the wilaya
    $sql = "SELECT * FROM shipping_rates WHERE wilaya = ? AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$wilaya]);
    $rate = $stmt->fetch();
    
    if (!$rate) {
        return false;
    }
    
    // Check if free shipping threshold is met
    if ($cartAmount >= $rate['free_shipping_threshold']) {
        return [
            'cost' => 0,
            'delivery_days' => $rate['delivery_days'],
            'method' => 'free'
        ];
    }
    
    // Calculate cost based on weight
    if ($weight <= 1) {
        $cost = $rate['weight_tier_1'];
    } elseif ($weight <= 5) {
        $cost = $rate['weight_tier_2'];
    } elseif ($weight <= 10) {
        $cost = $rate['weight_tier_3'];
    } else {
        $cost = $rate['weight_tier_4'];
    }
    
    return [
        'cost' => $cost,
        'delivery_days' => $rate['delivery_days'],
        'method' => 'standard'
    ];
}
?>
