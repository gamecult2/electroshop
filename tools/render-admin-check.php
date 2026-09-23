<?php
// Read-only CLI renderer for local UI checks. Never expose this through HTTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$target = $argv[1] ?? '';
$adminRoot = realpath(__DIR__ . '/../src/admin');
$_SERVER['PHP_SELF'] = '/admin/' . $target;
require $adminRoot . '/includes/pages.php';
if (!isset($menu_items[$target]) && !isset($admin_detail_pages[$target]) && $target !== 'login.php') exit(2);
chdir($adminRoot);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/admin/' . $target;
$_GET = [];
$_POST = [];
// Synthetic session is confined to this CLI process and closed without persistence.
session_start();
$_SESSION = $target === 'login.php' ? [] : ['admin_logged_in' => true, 'admin_role' => 'admin', 'admin_id' => 1, 'admin_name' => 'UI Test'];
require_once '../db_connect.php';
$tables = [
    'edit_product.php' => 'products', 'product_details.php' => 'products',
    'edit_category.php' => 'categories', 'edit_brand.php' => 'brands',
    'edit_banner.php' => 'banners', 'edit_coupon.php' => 'coupons',
    'edit_courier.php' => 'couriers', 'edit_customer.php' => 'customers',
    'customer_details.php' => 'customers', 'edit_user.php' => 'users',
    'admin_order_details.php' => 'orders'
];
if (isset($tables[$target])) {
    $_GET['id'] = $pdo->query('SELECT id FROM ' . $tables[$target] . ' LIMIT 1')->fetchColumn();
    if (!$_GET['id']) { session_abort(); echo json_encode(['skip' => 'No fixture record']); exit; }
}
$renderErrors = [];
set_error_handler(static function ($severity, $message, $file, $line) use (&$renderErrors) {
    if (str_contains($message, 'session is already active')) return true;
    // Third-party PHP 8.5 deprecations are outside this UI validation scope.
    if ($severity === E_DEPRECATED && str_contains($file, 'vendor')) return true;
    $renderErrors[] = basename($file) . ':' . $line . ' ' . $message;
    return true;
});
ob_start();
try { require $target; } catch (Throwable $error) { $renderErrors[] = get_class($error) . ': ' . $error->getMessage(); }
$html = ob_get_clean();
session_abort();
echo json_encode(['html' => $html, 'errors' => $renderErrors], JSON_INVALID_UTF8_SUBSTITUTE);
