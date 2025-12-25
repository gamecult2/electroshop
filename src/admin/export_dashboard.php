<?php
// src/admin/export_dashboard.php - Export Sales Reports
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die('Unauthorized access.');
}

$type = $_GET['type'] ?? 'csv';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="QwenShop_Sales_Report_' . $startDate . '_to_' . $endDate . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header Row
    fputcsv($output, ['Date', 'Order Number', 'Customer Email', 'Status', 'Payment Method', 'Subtotal', 'Tax', 'Shipping', 'Discount', 'Total Amount']);
    
    // Fetch Data
    $stmt = $pdo->prepare("
        SELECT o.created_at, o.order_number, c.email, o.status, o.payment_method, o.subtotal, o.tax_amount, o.shipping_cost, o.discount_amount, o.total_amount 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Optional: Handle PDF export if a library is available, 
// but for now CSV is the standard requested export.
?>
