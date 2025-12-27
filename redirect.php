<?php
require_once 'src/config.php';

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$id = intval($_GET['id']);

// Get product details
$stmt = $pdo->prepare("SELECT category_path, slug FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // Build and redirect to new URL
    $newUrl = SITE_BASE_PATH . '/Products/' . $product['category_path'] . '/' . $product['slug'];
    header("Location: $newUrl", true, 301);
    exit;
} else {
    header("HTTP/1.0 404 Not Found");
    include 'src/404.php';
    exit;
}