<?php
// admin/api/update_product_quick.php
session_start();
require_once '../../db_connect.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$field = isset($_POST['field']) ? $_POST['field'] : '';
$value = isset($_POST['value']) ? $_POST['value'] : '';

if (!$id || !$field) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Allowable fields to update
$allowedFields = ['price', 'stock_quantity', 'is_active', 'is_featured', 'is_new_arrival', 'is_best_seller'];

if (!in_array($field, $allowedFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid field']);
    exit;
}

// Validate value based on field
if ($field === 'price') {
    $value = (float)$value;
    if ($value < 0) {
        echo json_encode(['success' => false, 'message' => 'Price cannot be negative']);
        exit;
    }
} elseif ($field === 'stock_quantity') {
    $value = (int)$value;
    if ($value < 0) {
        echo json_encode(['success' => false, 'message' => 'Stock cannot be negative']);
        exit;
    }
} else {
    // Boolean fields (active, featured, etc.)
    $value = ($value === '1' || $value === 'true' || $value === 1) ? 1 : 0;
}

try {
    $sql = "UPDATE products SET {$field} = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$value, $id]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
