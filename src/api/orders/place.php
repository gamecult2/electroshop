<?php
header('Content-Type: application/json');
require_once '../../includes/init.php';
require_once '../../models/Cart.php';
require_once '../../models/Order.php';
require_once '../../models/Customer.php';

// Calculate discount function
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

// Remove mandatory login check
// if (!is_logged_in()) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

$cart = new Cart();
$itemCount = $cart->getItemCount();
$isLoggedIn = is_logged_in();
$userId = $isLoggedIn ? get_current_user_id() : null;
$sessionId = session_id();

error_log("Order placement attempt: sessionId=$sessionId, isLoggedIn=" . ($isLoggedIn ? 'YES' : 'NO') . ", customerId=" . ($userId ?? 'NULL') . ", itemCount=$itemCount");

if ($cart->isEmpty()) {
    // Second check with raw query just in case model instantiation had issues
    $cartItems = $cart->getItems();
    error_log("Cart reports empty. getItems count: " . count($cartItems));

    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty',
        'error_type' => 'stock_error',
        'debug_info' => [
            'session_id' => $sessionId,
            'is_logged_in' => $isLoggedIn,
            'customer_id' => $userId,
            'item_count' => $itemCount,
            'items_from_get_items' => count($cartItems)
        ]
    ]);
    exit;
}

$isLoggedIn = is_logged_in();
$userId = $isLoggedIn ? get_current_user_id() : null;
$customerModel = new Customer();
$orderModel = new Order();

// Get address
$addressId = $input['delivery_address_id'] ?? null;
$address = null;

if ($isLoggedIn && $addressId && $addressId !== 'new') {
    $address = $customerModel->getAddressById($addressId, $userId);
} else if ($addressId === 'new') {
    $address = $input['address_data'] ?? null;
}

if (!$address) {
    echo json_encode(['success' => false, 'message' => 'Valid delivery address is required', 'error_type' => 'server_error']);
    exit;
}

// Calculate costs
$cartItems = $cart->getItems();
$subtotal = $cart->getSubtotal();
if (!isset($input['expected_subtotal']) || (int)round((float)$input['expected_subtotal']*100)!==(int)round((float)$subtotal*100)) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Prices or cart contents changed. Refresh checkout to review your total.']);
    exit;
}

if (($input['payment_method'] ?? 'cod') === 'bank_transfer' && get_setting('bank_transfer_enabled', '1') !== '1') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Bank transfer is currently unavailable. Please choose another payment method.']);
    exit;
}
if (($input['payment_method'] ?? 'cod') === 'baridimob' && get_setting('baridimob_enabled', '0') !== '1') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'BaridiMob is currently unavailable. Please choose another payment method.']);
    exit;
}
if (($input['payment_method'] ?? 'cod') === 'chargily' && get_setting('chargily_enabled', '0') !== '1') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'CIB / Edahabia payment is currently unavailable. Please choose another payment method.']);
    exit;
}
$deliveryOption = $input['delivery_option'] ?? 'standard';
$shippingCost = 500;
switch($deliveryOption) {
    case 'express': $shippingCost = 1000; break;
    case 'home_delivery': $shippingCost = 600; break;
}
$total = $subtotal + $shippingCost;

// Generate order number
$orderNumber = 'QWS-' . strtoupper(bin2hex(random_bytes(4)));

// Prepare guest/user info
$firstName = '';
$lastName = '';
$email = '';

if ($isLoggedIn) {
    $user = $customerModel->getById($userId);
    $firstName = $user['first_name'];
    $lastName = $user['last_name'];
    $email = $user['email'];
} else {
    $guestInfo = $input['guest_info'] ?? null;
    if (!$guestInfo || empty($guestInfo['email'])) {
        echo json_encode(['success' => false, 'message' => 'Contact information is required']);
        exit;
    }
    $firstName = $guestInfo['first_name'];
    $lastName = $guestInfo['last_name'];
    $email = $guestInfo['email'];
}

// Handle coupon application
$discountAmount = 0;
$couponId = null;

if (!empty($input['promo_code'])) {
    $promoCode = trim($input['promo_code']);

    // Validate coupon again for security
    $validation = validate_coupon($promoCode, $userId, $subtotal, array_column($cartItems, 'product_id'));

    if ($validation['valid']) {
        $coupon = $validation['coupon'];
        $discountAmount = calculate_discount($coupon, $subtotal);
        $couponId = $coupon['id'];

        // Update total with discount
        $total = $subtotal + $shippingCost - $discountAmount;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid coupon code', 'error_type' => 'coupon_error']);
        exit;
    }
}

// Prepare order data
$orderData = [
    'order_number' => $orderNumber,
    'customer_id' => $userId, // Can be null for guests
    'customer_name' => $firstName . ' ' . $lastName,
    'customer_email' => $email,
    'subtotal' => $subtotal,
    'shipping_cost' => $shippingCost,
    'discount_amount' => $discountAmount,
    'total_amount' => $total,
    'billing_address' => $address,
    'shipping_address' => $address,
    'delivery_option' => $deliveryOption,
    'wilaya' => $address['wilaya'],
    'daira' => $address['daira'],
    'commune' => $address['commune'],
    'delivery_notes' => $input['order_notes'] ?? null,
    'payment_method' => $input['payment_method'] ?? 'cod',
    'items' => $cartItems
];

try {
    $orderId = $orderModel->create($orderData);
} catch (DomainException | InvalidArgumentException $e) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>$e->getMessage(),'error_type'=>'stock_error']);
    exit;
}

if ($orderId && $couponId) {
    // Record coupon usage
    try {
        $usageSql = "INSERT INTO coupon_usage (coupon_id, order_id, user_id) VALUES (?, ?, ?)";
        $usageStmt = $pdo->prepare($usageSql);
        $usageStmt->execute([$couponId, $orderId, $userId]);

        // Update coupon used count
        $updateSql = "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$couponId]);
    } catch (Exception $e) {
        error_log("Error recording coupon usage: " . $e->getMessage());
        // Don't fail the order if coupon usage recording fails
    }
}

if ($orderId) {
    if (!$userId) $_SESSION['recent_guest_order_ids'][(int)$orderId] = true;
    $savedNumber = $pdo->prepare('SELECT order_number FROM orders WHERE id=?');
    $savedNumber->execute([$orderId]);
    $orderNumber = $savedNumber->fetchColumn();
    // Clear cart
    $cart->clear();
    echo json_encode(['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create order', 'error_type' => 'server_error']);
}
?>
