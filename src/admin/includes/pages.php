<?php
// Navigation and page identity share one source of truth.
$menu_items = [
    'dashboard.php' => ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
    'products.php' => ['icon' => 'fas fa-box', 'label' => 'Products'],
    'categories.php' => ['icon' => 'fas fa-layer-group', 'label' => 'Categories'],
    'brands.php' => ['icon' => 'fas fa-tag', 'label' => 'Brands'],
    'reviews.php' => ['icon' => 'fas fa-star', 'label' => 'Product Reviews'],
    'flash_sales.php' => ['icon' => 'fas fa-bolt', 'label' => 'Flash Sales'],
    'orders.php' => ['icon' => 'fas fa-shopping-cart', 'label' => 'Orders'],
    'returns.php' => ['icon' => 'fas fa-undo', 'label' => 'Return Requests'],
    'customers.php' => ['icon' => 'fas fa-user-tag', 'label' => 'Customers'],
    'users.php' => ['icon' => 'fas fa-users-cog', 'label' => 'Staff'],
    'messages.php' => ['icon' => 'fas fa-comments', 'label' => 'Customer Chat'],
    'contact_messages.php' => ['icon' => 'fas fa-envelope', 'label' => 'Contact Inbox'],
    'newsletter.php' => ['icon' => 'fas fa-paper-plane', 'label' => 'Newsletter'],
    'coupons.php' => ['icon' => 'fas fa-gift', 'label' => 'Coupons'],
    'chargily_payments.php' => ['icon' => 'fas fa-file-invoice-dollar', 'label' => 'Chargily Transactions'],
    'banners.php' => ['icon' => 'fas fa-ad', 'label' => 'Banners'],
    'pages.php' => ['icon' => 'fas fa-home', 'label' => 'Homepage'],
    'inventory.php' => ['icon' => 'fas fa-warehouse', 'label' => 'Inventory'],
    'reports.php' => ['icon' => 'fas fa-chart-line', 'label' => 'Reports & Analytics'],
    'shipping_rates.php' => ['icon' => 'fas fa-truck', 'label' => 'Shipping Rates'],
    'couriers.php' => ['icon' => 'fas fa-shipping-fast', 'label' => 'Couriers'],
    'settings.php' => ['icon' => 'fas fa-cog', 'label' => 'Settings'],
];
$admin_detail_pages = [
    'add_product.php' => ['Add Product', 'products.php'],
    'edit_product.php' => ['Edit Product', 'products.php'],
    'product_details.php' => ['Product Details', 'products.php'],
    'edit_category.php' => ['Edit Category', 'categories.php'],
    'edit_brand.php' => ['Edit Brand', 'brands.php'],
    'edit_banner.php' => ['Edit Banner', 'banners.php'],
    'edit_coupon.php' => ['Edit Coupon', 'coupons.php'],
    'edit_courier.php' => ['Edit Courier', 'couriers.php'],
    'edit_customer.php' => ['Edit Customer', 'customers.php'],
    'customer_details.php' => ['Customer Details', 'customers.php'],
    'add_user.php' => ['Add Staff Member', 'users.php'],
    'edit_user.php' => ['Edit Staff Member', 'users.php'],
    'admin_order_details.php' => ['Order Details', 'orders.php'],
];
$current_page = basename($_SERVER['PHP_SELF']);
$active_page = $admin_detail_pages[$current_page][1] ?? $current_page;
$page_heading = $menu_items[$current_page]['label'] ?? $admin_detail_pages[$current_page][0] ?? 'Admin Panel';
$page_title = $page_heading;
if ($current_page === 'admin_order_details.php' && isset($orderId)) $page_title .= ' #' . (int)$orderId;
