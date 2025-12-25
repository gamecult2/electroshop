<?php
// order_details.php - Redirect to consolidated order history details view
require_once 'includes/init.php';
$orderId = $_GET['id'] ?? null;
if ($orderId) {
    header("Location: order_history.php?view=details&order_id=" . urlencode($orderId));
} else {
    header("Location: order_history.php");
}
exit;
?>
