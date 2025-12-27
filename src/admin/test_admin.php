<?php
// Simple test page to verify admin header works
session_start();

// Check if admin is logged in (for testing, we'll simulate it)
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true; // For testing purposes only
}

require_once '../includes/functions.php';
require_once '../db_connect.php';

// Set page title and heading variables for the template
$page_title = 'Test Page';
$page_heading = 'Test Page - Admin Panel';

// Include the shared header template
include 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4">
                <h3 class="text-success">Success!</h3>
                <p>The admin header is now loading correctly.</p>
                <p>If you see this page, the issue with the missing time_elapsed_string function has been fixed.</p>
                <a href="login.php" class="btn btn-primary">Go to Login</a>
                <a href="dashboard.php" class="btn btn-success">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>