<?php
/**
 * Router for handling SEO-friendly URLs
 * Converts clean URLs like /Products/Electronics/Televisions/Samsung-55-QLED-4K-Smart-TV
 * to internal PHP file calls like product.php?id=X
 */

// Initialize database connection globally to ensure it's available to all included files
require_once 'src/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

class Router {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Load a page by including the appropriate PHP file
     */
    private function loadPage($page) {
        $pagePath = 'src/' . $page;
        if (file_exists($pagePath)) {
            require_once $pagePath;
        } else {
            $this->show404();
        }
    }

    /**
     * Show 404 page
     */
    private function show404() {
        http_response_code(404);
        require_once 'src/404.php';
    }

    /**
     * Handle traditional routing with SEO-friendly URLs
     */
    private function handleTraditionalRouting($request_uri) {
        // Check if it's a Products/* URL (SEO-friendly format)
        if (strpos($request_uri, '/Products/') === 0) {
            // Extract the slug (last part of the URL)
            $parts = explode('/', trim($request_uri, '/'));
            $slug = end($parts);

            // Query database for product by slug
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // Set the product ID and load product.php
                $_GET['id'] = $product['id'];
                $_GET['slug'] = $slug; // Optional: keep slug available too
                $this->loadPage('product.php');
                return;
            } else {
                // Product not found
                $this->show404();
                return;
            }
        }

        // Map URL paths to corresponding PHP files
        $file_map = [
            '/' => 'index.php',
            '/index.php' => 'index.php',
            '/products' => 'products.php',
            '/product/{id}' => 'product.php',
            '/category/{id}' => 'products.php',
            '/search' => 'search.php',
            '/cart' => 'cart.php',
            '/checkout' => 'checkout.php',
            '/account' => 'account.php',
            '/orders' => 'orders.php',
            '/order/{id}' => 'order_details.php',
            '/wishlist' => 'wishlist.php',
            '/compare' => 'compare.php',
            '/login' => 'login.php',
            '/register' => 'register.php',
            '/logout' => 'logout.php',
            '/forgot-password' => 'forgot_password.php',
            '/reset-password' => 'reset_password.php',
            '/contact' => 'contact.php',
            '/about' => 'about.php',
            '/privacy' => 'privacy.php',
            '/terms' => 'terms.php',
            '/returns' => 'returns.php',
            '/shipping' => 'shipping.php',
            '/faqs' => 'faqs.php',
            '/support' => 'support.php',
            '/maintenance.php' => 'maintenance.php'
        ];

        // Check for exact matches first
        if (isset($file_map[$request_uri])) {
            $file = $file_map[$request_uri];
            $this->loadPage($file);
            return;
        }

        // Check for pattern matches with parameters
        foreach ($file_map as $pattern => $file) {
            if (strpos($pattern, '{id}') !== false) {
                $regex_pattern = str_replace('{id}', '(\d+)', preg_quote($pattern, '#'));
                if (preg_match('#^' . $regex_pattern . '$#', $request_uri, $matches)) {
                    // Extract the ID from the match
                    $id = $matches[1];
                    $_GET['id'] = $id;

                    // For product URLs, redirect to SEO-friendly version
                    if ($file === 'product.php') {
                        $stmt = $this->pdo->prepare("SELECT category_path, slug FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($product) {
                            $newUrl = SITE_BASE_PATH . '/Products/' . $product['category_path'] . '/' . $product['slug'];
                            header("Location: $newUrl", true, 301);
                            exit;
                        }
                    }

                    $this->loadPage($file);
                    return;
                }
            }
        }

        // If no route matches, show 404
        $this->show404();
    }

    /**
     * Run the router
     */
    public function run() {
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remove query parameters from URI for routing
        $path = parse_url($requestUri, PHP_URL_PATH);

        // Remove the base path if needed (adjust based on your setup)
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/') {
            $path = substr($path, strlen($basePath));
        }

        // Normalize path
        $path = trim($path, '/');
        if ($path) {
            $path = '/' . $path;
        } else {
            $path = '/';
        }

        // Handle the request
        $this->handleTraditionalRouting($path);
    }
}

// Initialize and run the router if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === 'router.php') {
    $router = new Router();
    $router->run();
}