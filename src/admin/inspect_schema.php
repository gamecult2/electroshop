<?php
// Temporary script to inspect database schema
require_once '../db_connect.php';

echo "=== CATEGORIES TABLE STRUCTURE ===\n";
$stmt = $pdo->query("DESCRIBE categories");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "{$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']}\n";
}

echo "\n=== CURRENT CATEGORIES ===\n";
$stmt = $pdo->query("SELECT id, name_en, slug, parent_id, is_active FROM categories ORDER BY id");
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $cat) {
    echo "ID: {$cat['id']} | Name: {$cat['name_en']} | Slug: {$cat['slug']} | Parent: " . ($cat['parent_id'] ?? 'NULL') . "\n";
}

echo "\n=== PRODUCTS TABLE STRUCTURE ===\n";
$stmt = $pdo->query("DESCRIBE products");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "{$col['Field']}: {$col['Type']}\n";
}

echo "\n=== PRODUCT COUNT BY CATEGORY ===\n";
$stmt = $pdo->query("SELECT category_id, COUNT(*) as count FROM products GROUP BY category_id ORDER BY category_id");
$counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($counts as $count) {
    echo "Category {$count['category_id']}: {$count['count']} products\n";
}

echo "\n=== PRODUCT_VARIANTS TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE product_variants");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== VARIANT_ATTRIBUTES TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE variant_attributes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== ORDERS TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== SHOPPING_CART TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE shopping_cart");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== ORDER_ITEMS TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE order_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== CONVERSATIONS TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE conversations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }

echo "\n=== MESSAGES TABLE STRUCTURE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']}\n";
    }
} catch (Exception $e) { echo "Table not found: " . $e->getMessage() . "\n"; }


