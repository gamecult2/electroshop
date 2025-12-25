<?php
// includes/init.php - Initialize session and common includes

// Prevent multiple initializations
if (!defined('INIT_PHP_INCLUDED')) {
    define('INIT_PHP_INCLUDED', true);

    // Ensure session directory is writable
    $session_dir = sys_get_temp_dir() . '/shop_sessions';
    if (!is_dir($session_dir)) {
        mkdir($session_dir, 0755, true);
    }

    // Set session save path
    session_save_path($session_dir);

    // Configure session name to avoid conflicts
    if (session_status() == PHP_SESSION_NONE) {
        session_name('SHOP_SESSION'); // Custom session name

        // Set session cookie parameters before starting session
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/',       // Available throughout the entire domain
            'domain' => '',      // Current domain (blank to use current host)
            'secure' => false,   // Set to true if using HTTPS
            'httponly' => true,  // Prevent XSS
            'samesite' => 'Lax'  // Recommended for login forms
        ]);

        session_start();

        // Ensure session is properly initialized
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = true;
        }
    } else {
        // If session is already started elsewhere, ensure it has the required values
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = true;
        }
    }

    // Additional session validation
    if (isset($_SESSION['customer_id'])) {
        // Customer is logged in, ensure all required session values are present
        // Use array_key_exists because values might be null but keys should exist
        if (!array_key_exists('user_email', $_SESSION) ||
            !array_key_exists('user_first_name', $_SESSION) ||
            !array_key_exists('user_last_name', $_SESSION)) {
            // If partial session data, destroy and start fresh
            session_destroy();
            session_start();
        }
    }
}

if (!isset($pdo)) {
    require_once __DIR__ . '/../db_connect.php';
}

// Include functions and database only once
require_once __DIR__ . '/functions.php';

$lang = DEFAULT_LANGUAGE;
