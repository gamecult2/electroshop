<?php
// Extend the existing order payment enum without changing historical values.
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

require_once __DIR__ . '/../src/db_connect.php';

$column = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'")->fetch();
if (!$column) {
    throw new RuntimeException('orders.payment_method is missing.');
}

$currentType = $column['Type'];
if (str_contains($currentType, "'chargily'")) {
    echo "Chargily payment method is already supported.\n";
    exit;
}

$expectedType = "enum('cod','bank_transfer','baridimob','edahabia','cib')";
if (strtolower($currentType) !== $expectedType) {
    throw new RuntimeException('Unexpected payment_method definition; review this migration before proceeding: ' . $currentType);
}

$backupDir = __DIR__ . '/private-backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true)) {
    throw new RuntimeException('Could not create backup directory.');
}
$backupPath = $backupDir . '/chargily-payment-method-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.json';
$snapshot = [
    'orders_ddl' => $pdo->query('SHOW CREATE TABLE orders')->fetch(PDO::FETCH_NUM)[1],
    'payment_methods' => $pdo->query('SELECT id, payment_method FROM orders ORDER BY id')->fetchAll(),
];
if (file_put_contents($backupPath, json_encode($snapshot, JSON_THROW_ON_ERROR)) === false) {
    throw new RuntimeException('Could not save the pre-migration snapshot.');
}

$pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod', 'bank_transfer', 'baridimob', 'edahabia', 'cib', 'chargily') NULL DEFAULT NULL");
echo "Added chargily to orders.payment_method. Pre-migration snapshot: $backupPath\n";
