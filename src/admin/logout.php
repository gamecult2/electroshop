<?php
// admin/logout.php
// Start session only if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables related to admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

// Redirect to admin login
header('Location: login.php');
exit();
?>

