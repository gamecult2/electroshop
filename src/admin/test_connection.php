<?php
// Simple test file to check database connection and settings
session_start();

// Include necessary files
require_once '../includes/functions.php';
require_once '../db_connect.php';

echo "<h2>Testing Database Connection and Settings</h2>";

// Test database connection
try {
    if ($pdo) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>";
}

// Test get_setting function
try {
    $siteTitle = get_setting('site_title', 'QwenShop');
    echo "<p>Site Title: " . htmlspecialchars($siteTitle) . "</p>";
    
    $bootstrapTheme = get_setting('bootstrap_theme', 'online');
    echo "<p>Bootstrap Theme: " . htmlspecialchars($bootstrapTheme) . "</p>";
    
    $currencySymbol = get_setting('currency_symbol', 'DA');
    echo "<p>Currency Symbol: " . htmlspecialchars($currencySymbol) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ get_setting function error: " . $e->getMessage() . "</p>";
}

// Test if session is working
if (isset($_SESSION['admin_logged_in'])) {
    echo "<p>Admin logged in: " . ($_SESSION['admin_logged_in'] ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p>Admin logged in: No (session not set)</p>";
}

echo "<p><a href='login.php'>Go to Login</a></p>";
?>