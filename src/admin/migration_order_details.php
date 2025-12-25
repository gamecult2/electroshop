<?php
require_once '../db_connect.php';

try {
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('admin_notes', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN admin_notes TEXT AFTER cancelled_reason");
        echo "Column 'admin_notes' added.<br>";
    }
    
    if (!in_array('internal_notes', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN internal_notes TEXT AFTER admin_notes");
        echo "Column 'internal_notes' added.<br>";
    }
    
    // Ensure order_status_history table exists and has notes column
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        admin_user_id INT,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");
    
    echo "Migration successful.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}