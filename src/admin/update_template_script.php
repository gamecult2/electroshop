<?php
/**
 * Admin Template Updater Script
 * 
 * This script demonstrates how to update remaining admin pages to use the shared template.
 * Run this script to generate the code snippets needed for each page.
 */

$admin_pages = [
    'brands.php',
    'reviews.php',
    'flash_sales.php',
    'returns.php',
    'users.php',
    'messages.php',
    'coupons.php',
    'banners.php',
    'pages.php',
    'inventory.php',
    'shipping_rates.php',
    'couriers.php',
    'settings.php',
    'add_page.php',
    'add_product.php',
    'admin_order_details.php',
    'check_products.php',
    'edit_banner.php',
    'edit_brand.php',
    'edit_category.php',
    'edit_coupon.php',
    'edit_courier.php',
    'edit_page.php',
    'edit_product.php',
    'generate_sample_products.php',
    'inspect_schema.php',
    'newsletter.php',
    'reports.php',
    'setup_settings.php'
];

echo "Admin Template Integration Instructions\n";
echo "=====================================\n\n";

foreach ($admin_pages as $page) {
    echo "File: {$page}\n";
    echo "1. Add at the top (after session and requirements, before HTML output):\n";
    echo "   ```php\n";
    echo "   // Set page title and heading variables for the template\n";
    echo "   \$page_title = 'Page Title';\n";
    echo "   \$page_heading = 'Page Heading';\n";
    echo "   \n";
    echo "   // Include the shared header template\n";
    echo "   include 'header.php';\n";
    echo "   ```\n\n";
    
    echo "2. Remove the old HTML structure (from <!DOCTYPE html> to main content)\n\n";
    
    echo "3. Add at the end before closing tags:\n";
    echo "   ```php\n";
    echo "   <!-- Include the shared footer template -->\n";
    echo "   <?php include 'footer.php'; ?>\n";
    echo "   ```\n\n";
    
    echo "4. Remove old closing tags (</div>, </body>, </html>)\n\n";
    echo "---\n\n";
}
