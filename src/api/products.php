<?php
// api/products.php - Product API endpoints

header('Content-Type: application/json');
require_once '../includes/init.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List products with filters
        $category = $_GET['category'] ?? null;
        $brand = $_GET['brand'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $search = $_GET['search'] ?? null;
        $isBestSeller = $_GET['is_best_seller'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name_en as category_name, c.parent_id, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1";
        $params = [];

        if ($category) {
            $includeSubcategories = $_GET['include_subcategories'] ?? false;
            if ($includeSubcategories) {
                // Include products from subcategories as well
                $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
                $params[] = $category;
                $params[] = $category;
            } else {
                $sql .= " AND p.category_id = ?";
                $params[] = $category;
            }
        }

        if ($brand) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $brand;
        }

        if ($minPrice) {
            $sql .= " AND p.final_price >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice) {
            $sql .= " AND p.final_price <= ?";
            $params[] = $maxPrice;
        }

        if ($isBestSeller) {
            $sql .= " AND p.is_best_seller = 1";
        }

        if ($search) {
            $sql .= " AND (p.name_en LIKE ? OR p.description_en LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        // Add sorting
        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY p.final_price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.final_price DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY p.rating DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.name_en ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY p.name_en DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get user's wishlist IDs if logged in
        $userWishlistIds = [];
        if (is_logged_in()) {
            $customerId = get_current_user_id();
            $wStmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE customer_id = ?");
            $wStmt->execute([$customerId]);
            $userWishlistIds = $wStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // For each product, fetch associated images and check wishlist status
        foreach ($products as &$product) {
            $imageSql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order LIMIT 1";
            $imageStmt = $pdo->prepare($imageSql);
            $imageStmt->execute([$product['id']]);
            $product['images'] = $imageStmt->fetchAll();
            
            $product['is_wishlisted'] = in_array($product['id'], $userWishlistIds);
        }

        // Get total count
        $countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1";
        $countParams = [];

        if ($category) {
            $includeSubcategories = $_GET['include_subcategories'] ?? false;
            if ($includeSubcategories) {
                // Include products from subcategories as well
                $countSql .= " AND (p.category_id = ? OR c.parent_id = ?)";
                $countParams[] = $category;
                $countParams[] = $category;
            } else {
                $countSql .= " AND p.category_id = ?";
                $countParams[] = $category;
            }
        }

        if ($brand) {
            $countSql .= " AND p.brand_id = ?";
            $countParams[] = $brand;
        }

        if ($minPrice) {
            $countSql .= " AND p.final_price >= ?";
            $countParams[] = $minPrice;
        }

        if ($maxPrice) {
            $countSql .= " AND p.final_price <= ?";
            $countParams[] = $maxPrice;
        }

        if ($isBestSeller) {
            $countSql .= " AND p.is_best_seller = 1";
        }

        if ($search) {
            $countSql .= " AND (p.name_en LIKE ? OR p.description_en LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $countParams = array_merge($countParams, [$searchTerm, $searchTerm]);
        }
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_products' => $total,
                'per_page' => $limit
            ]
        ]);
        break;
    
    case 'POST':
        // Create product (admin only)
        if (!is_logged_in() || !is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validation for required fields
        if (empty($input['name_en']) || empty($input['category_id']) || empty($input['brand_id']) || !isset($input['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Required fields missing']);
            exit;
        }
        
        $sql = "INSERT INTO products (name_en, description_en, short_description_en, category_id, brand_id, price, discount_percentage, stock_quantity, sku, weight, dimensions, color, size, is_active, is_featured, is_new_arrival, is_best_seller, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $input['name_en'],
            $input['description_en'] ?? '',
            $input['short_description_en'] ?? '',
            $input['category_id'],
            $input['brand_id'],
            $input['price'],
            $input['discount_percentage'] ?? 0,
            $input['stock_quantity'] ?? 0,
            $input['sku'] ?? null,
            $input['weight'] ?? null,
            $input['dimensions'] ?? null,
            $input['color'] ?? null,
            $input['size'] ?? null,
            $input['is_active'] ?? 1,
            $input['is_featured'] ?? 0,
            $input['is_new_arrival'] ?? 0,
            $input['is_best_seller'] ?? 0
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'product_id' => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
