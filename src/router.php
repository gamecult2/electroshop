<?php
// router.php - Basic routing system for QwenShop

require_once 'config.php';
require_once 'includes/init.php';

class Router {
    private $routes = [];
    
    public function add($method, $pattern, $callback) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    public function dispatch() {
        $request_method = $_SERVER['REQUEST_METHOD'];
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove the base path (assuming it's in a subdirectory)
        $base_path = SITE_BASE_PATH;
        if (strpos($request_uri, $base_path) === 0) {
            $request_uri = substr($request_uri, strlen($base_path));
        }
        
        // Normalize URI
        $request_uri = rtrim($request_uri, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request_method) {
                continue;
            }

            // Convert route pattern to regex
            $pattern = $route['pattern'];
            $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $request_uri, $matches)) {
                array_shift($matches); // Remove the full match
                return call_user_func_array($route['callback'], $matches);
            }
        }

        // If no route matches, handle the request with traditional PHP files
        $this->handleTraditionalRouting($request_uri);
    }

    private function handleTraditionalRouting($request_uri) {
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
            '/support' => 'support.php'
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

                    // For now, just set it as a GET parameter
                    $_GET['id'] = $id;

                    $this->loadPage($file);
                    return;
                }
            }
        }

        // If no route matches, show 404
        $this->show404();
    }
    
    private function loadPage($file) {
        $page_path = __DIR__ . '/' . $file;
        
        if (file_exists($page_path)) {
            // Make essential global variables available to the included page
            global $pdo, $lang;
            require_once $page_path;
        } else {
            $this->show404();
        }
    }
    
    private function show404() {
        http_response_code(404);
        global $pdo, $lang;
        include __DIR__ . '/404.php';
    }
}

// Initialize router
$router = new Router();

// Dispatch the request
$router->dispatch();
?>
