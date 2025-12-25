# Admin Panel Template Updates

## Overview
This update introduces a shared header and footer template system for all admin pages. This ensures consistency across all admin pages and makes it easier to maintain the sidebar navigation.

## Files Added
- `header.php` - Shared header and sidebar template
- `footer.php` - Shared footer template

## Changes Made
The following files have been updated to use the new template system:
- `dashboard.php`
- `products.php` 
- `categories.php`
- `orders.php`

## How to Update Remaining Admin Pages

To update other admin pages to use the shared template:

1. **At the top of the PHP file (after PHP logic but before HTML):**
   ```php
   // Set page title and heading variables for the template
   $page_title = 'Page Title - QwenShop Admin';
   $page_heading = 'Page Heading';
   
   // Include the shared header template
   include 'header.php';
   ```

2. **Remove the old HTML structure:**
   - Remove the entire HTML structure from `<!DOCTYPE html>` to the start of the main content
   - This includes `<html>`, `<head>`, and the sidebar HTML

3. **At the end of the file, before the closing tags:**
   ```php
   <!-- Include the shared footer template -->
   <?php include 'footer.php'; ?>
   ```

4. **Remove the ending HTML tags:**
   - Remove `</div>` tags for admin-content and admin-container
   - Remove `</body>` and `</html>` tags

## Features
- Dynamic active menu highlighting based on current page
- Consistent styling across all admin pages
- Centralized header and footer management
- Maintains all existing functionality

## Testing
After updating any admin page, make sure to test:
- Navigation between pages
- Active menu highlighting
- All page functionality
- Logout functionality