<?php
/**
 * Categories API Endpoint
 * 
 * GET /api/categories - Returns hierarchical category structure
 * GET /api/categories/{id} - Returns specific category with metadata
 * GET /api/categories/slug/{slug} - Returns category by slug
 * 
 * Response includes:
 * - Full hierarchy (main categories with subcategories)
 * - Product counts
 * - SEO-friendly slugs
 */

header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../models/Category.php';

$categoryModel = new Category();
$method = $_SERVER['REQUEST_METHOD'];

// Parse request
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = array_filter(explode('/', $path));

try {
    if ($method === 'GET') {
        // Determine endpoint
        if (isset($segments[4]) && $segments[4] === 'slug' && isset($segments[5])) {
            // GET /api/categories/slug/{slug}
            $slug = $segments[5];
            $parentSlug = $segments[6] ?? null;
            
            $category = $categoryModel->getBySlug($slug, $parentSlug);
            
            if (!$category) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                exit;
            }
            
            // Add additional metadata
            $category['product_count'] = $categoryModel->getProductCount($category['id']);
            $category['breadcrumb'] = $categoryModel->getBreadcrumb($category['id']);
            
            if ($category['parent_id'] === null) {
                // Main category - include subcategories
                $category['subcategories'] = $categoryModel->getAll($category['id']);
            }
            
            echo json_encode($category);
            
        } elseif (isset($segments[3]) && is_numeric($segments[3])) {
            // GET /api/categories/{id}
            $id = (int)$segments[3];
            $category = $categoryModel->getById($id);
            
            if (!$category) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                exit;
            }
            
            // Add metadata
            $category['product_count'] = $categoryModel->getProductCount($category['id']);
            $category['breadcrumb'] = $categoryModel->getBreadcrumb($category['id']);
            
            if ($category['parent_id'] === null) {
                $category['subcategories'] = $categoryModel->getAll($category['id']);
            } else {
                $category['parent'] = $categoryModel->getById($category['parent_id']);
            }
            
            echo json_encode($category);
            
        } else {
            // GET /api/categories - Return full hierarchy
            $hierarchy = $categoryModel->getAllWithHierarchy();
            
            echo json_encode([
                'success' => true,
                'categories' => $hierarchy,
                'total_main_categories' => count($hierarchy),
                'total_subcategories' => array_sum(array_map(function($cat) {
                    return count($cat['subcategories'] ?? []);
                }, $hierarchy))
            ]);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
