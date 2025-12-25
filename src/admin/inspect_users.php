<?php
require_once '../db_connect.php';

$tables = ['users', 'admin_users', 'user_addresses', 'shopping_cart', 'orders', 'reviews', 'wishlists', 'search_history', 'user_notifications', 'order_status_history'];

foreach ($tables as $table) {
    echo "\n=== $table ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "{$col['Field']}: {$col['Type']} {$col['Key']}\n";
        }
    } catch (PDOException $e) {
        echo "Table $table does not exist or error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FOREIGN KEYS ===\n";
$stmt = $pdo->query("
    SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        REFERENCED_TABLE_NAME IN ('users', 'admin_users')
        AND TABLE_SCHEMA = DATABASE()
");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($fks as $fk) {
    echo "{$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']} (Constraint: {$fk['CONSTRAINT_NAME']})\n";
}
