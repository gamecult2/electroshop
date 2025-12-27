<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID required']);
    exit;
}

$productId = (int)$_GET['product_id'];
$imageDir = "../uploads/medias/products/{$productId}";

// Check if directory exists
if (!is_dir($imageDir)) {
    echo json_encode(['image_url' => null]);
    exit;
}

// Get all image files in the directory
$files = scandir($imageDir);
$imageFiles = [];

foreach ($files as $file) {
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
        $imageFiles[] = $file;
    }
}

// Return the first image found or null if none
if (!empty($imageFiles)) {
    $firstImage = $imageFiles[0];
    echo json_encode(['image_url' => "../uploads/medias/products/{$productId}/{$firstImage}"]);
} else {
    echo json_encode(['image_url' => null]);
}