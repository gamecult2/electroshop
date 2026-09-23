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
        
        require_once '../models/Product.php';
        try {
            $productId=(new Product())->create($input);
            echo json_encode(['success'=>true,'product_id'=>$productId]);
        } catch (InvalidArgumentException | DomainException $e) {
            http_response_code(422);
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(409);
            echo json_encode(['success'=>false,'error'=>'Unable to save product. Check the SKU and product values.']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
