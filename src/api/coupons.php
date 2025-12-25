<?php
// api/coupons.php - Coupons API endpoint

header('Content-Type: application/json');
require_once '../includes/init.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$endpoint = 'coupons';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate coupon code
    $input = json_decode(file_get_contents('php://input'), true);
    $code = $input['code'] ?? '';
    $cartAmount = $input['cart_amount'] ?? 0;
    $productIds = $input['product_ids'] ?? [];
    
    if (empty($code)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
        exit;
    }
    
    $userId = is_logged_in() ? get_current_user_id() : null;

    try {
        // Rate limiting check moved inside try-catch
        if (is_rate_limited($ip, $endpoint)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
            exit;
        }

        record_api_request($ip, $endpoint);

        $validation = validate_coupon($code, $userId, $cartAmount, $productIds);

        if ($validation['valid']) {
            $discountAmount = calculate_discount($validation['coupon'], $cartAmount);
            echo json_encode([
                'success' => true,
                'message' => 'Coupon is valid',
                'coupon' => $validation['coupon'],
                'discount_amount' => $discountAmount
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $validation['message']
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Coupon validation error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error during coupon validation'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function calculate_discount($coupon, $cartAmount) {
    $discountType = $coupon['discount_type'] ?? '';
    $discountValue = $coupon['discount_value'] ?? 0;

    if ($discountType === 'percentage') {
        return $cartAmount * ($discountValue / 100);
    } elseif ($discountType === 'fixed_amount') {
        return min($discountValue, $cartAmount);
    } elseif ($discountType === 'free_shipping') {
        return 0; // Free shipping discount applied separately
    }
    return 0;
}
