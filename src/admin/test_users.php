<?php
require_once '../db_connect.php';

echo "=== USERS TABLE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE users");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Total Rows in users: $count\n\n";
} catch(Exception $e) { echo "Error users: " . $e->getMessage() . "\n"; }

echo "=== ADMIN_USERS TABLE ===\n";
try {
    $stmt = $pdo->query("DESCRIBE admin_users");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
    $count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    echo "Total Rows in admin_users: $count\n\n";
    
    $roles = $pdo->query("SELECT role, COUNT(*) as count FROM admin_users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
    foreach($roles as $r) {
        echo "Role: {$r['role']} | Count: {$r['count']}\n";
    }
} catch(Exception $e) { echo "Error admin_users: " . $e->getMessage() . "\n"; }
