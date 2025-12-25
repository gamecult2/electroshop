<?php
// models/Search.php - Advanced Search Model

require_once __DIR__ . '/../db_connect.php';

class Search {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    /**
     * Search products with advanced filters
     */
    public function searchProducts($query, $filters = [], $sort = 'relevance', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Build SQL with filters
        $sql = "SELECT DISTINCT p.*, 
                       c.name_en as category_name, 
                       b.name as brand_name,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as rating_count
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN reviews r ON p.id = r.product_id AND r.is_approved = 1
                WHERE p.is_active = 1 AND (";
        
        // Full-text search condition
        $searchParams = [];
        if (!empty($query)) {
            $sql .= "(MATCH(p.name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                      MATCH(p.description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                      MATCH(p.short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))";
            $searchParams[] = $query;
            $searchParams[] = $query;
            $searchParams[] = $query;
        } else {
            $sql .= "1=1";
        }
        $sql .= ")";
        
        // Apply filters
        $filterParams = [];
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $filterParams[] = $filters['category_id'];
        }
        
        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $filterParams[] = $filters['brand_id'];
        }
        
        if (!empty($filters['price_min'])) {
            $sql .= " AND p.final_price >= ?";
            $filterParams[] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $sql .= " AND p.final_price <= ?";
            $filterParams[] = $filters['price_max'];
        }
        
        if (!empty($filters['min_rating'])) {
            $sql .= " AND p.rating >= ?";
            $filterParams[] = $filters['min_rating'];
        }
        
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $sql .= " AND p.stock_quantity > 0";
        }
        
        $sql .= " GROUP BY p.id";
        
        // Apply sorting
        $sql .= $this->getSortClause($sort);
        
        $sql .= " LIMIT ? OFFSET ?";
        
        $allParams = array_merge($searchParams, $filterParams, [$limit, $offset]);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($allParams);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count total results
        $countSql = "SELECT COUNT(DISTINCT p.id) as total
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN brands b ON p.brand_id = b.id
                     LEFT JOIN reviews r ON p.id = r.product_id AND r.is_approved = 1
                     WHERE p.is_active = 1 AND (";
        
        if (!empty($query)) {
            $countSql .= "(MATCH(p.name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                           MATCH(p.description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                           MATCH(p.short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))";
        } else {
            $countSql .= "1=1";
        }
        $countSql .= ")";
        
        // Apply same filters to count query
        $countFilterParams = [];
        if (!empty($filters['category_id'])) {
            $countSql .= " AND p.category_id = ?";
            $countFilterParams[] = $filters['category_id'];
        }
        
        if (!empty($filters['brand_id'])) {
            $countSql .= " AND p.brand_id = ?";
            $countFilterParams[] = $filters['brand_id'];
        }
        
        if (!empty($filters['price_min'])) {
            $countSql .= " AND p.final_price >= ?";
            $countFilterParams[] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $countSql .= " AND p.final_price <= ?";
            $countFilterParams[] = $filters['price_max'];
        }
        
        if (!empty($filters['min_rating'])) {
            $countSql .= " AND p.rating >= ?";
            $countFilterParams[] = $filters['min_rating'];
        }
        
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $countSql .= " AND p.stock_quantity > 0";
        }
        
        $countStmt = $this->pdo->prepare($countSql);
        $countParams = array_merge(empty($query) ? [] : [$query, $query, $query], $countFilterParams);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get SQL clause for sorting
     */
    private function getSortClause($sort) {
        switch ($sort) {
            case 'price_low':
                return " ORDER BY p.final_price ASC";
            case 'price_high':
                return " ORDER BY p.final_price DESC";
            case 'newest':
                return " ORDER BY p.created_at DESC";
            case 'rating':
                return " ORDER BY p.rating DESC";
            case 'popularity':
                return " ORDER BY p.views_count DESC";
            case 'name_asc':
                return " ORDER BY p.name_en ASC";
            case 'name_desc':
                return " ORDER BY p.name_en DESC";
            default:
                return " ORDER BY p.created_at DESC";
        }
    }
    
    /**
     * Save search history for a user
     */
    public function saveSearchHistory($userId, $searchTerm, $resultCount) {
        $sql = "INSERT INTO search_history (customer_id, search_term, result_count) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $searchTerm, $resultCount]);
    }
    
    /**
     * Get search history for a user
     */
    public function getSearchHistory($userId, $limit = 5) {
        $sql = "SELECT search_term, search_date, result_count 
                FROM search_history 
                WHERE customer_id = ? 
                ORDER BY search_date DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get autocomplete suggestions
     */
    public function getAutocompleteSuggestions($query, $limit = 10) {
        $sql = "SELECT DISTINCT name_en as name
                FROM products 
                WHERE is_active = 1 
                AND (MATCH(name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                     MATCH(description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                     MATCH(short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$query, $query, $query, $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get available filters for search
     */
    public function getFilters($query = '') {
        // Get available brands for filters
        $brandSql = "SELECT DISTINCT b.id, b.name
                     FROM products p
                     JOIN brands b ON p.brand_id = b.id
                     WHERE p.is_active = 1";
        if (!empty($query)) {
            $brandSql .= " AND (MATCH(p.name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                MATCH(p.description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                MATCH(p.short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))";
        }
        $brandSql .= " ORDER BY b.name";
        
        $brandStmt = $this->pdo->prepare($brandSql);
        if (!empty($query)) {
            $brandStmt->execute([$query, $query, $query]);
        } else {
            $brandStmt->execute();
        }
        $brands = $brandStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get available categories for filters
        $categorySql = "SELECT DISTINCT c.id, c.name_en
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.is_active = 1";
        if (!empty($query)) {
            $categorySql .= " AND (MATCH(p.name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                   MATCH(p.description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                   MATCH(p.short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))";
        }
        $categorySql .= " ORDER BY c.name_en";
        
        $categoryStmt = $this->pdo->prepare($categorySql);
        if (!empty($query)) {
            $categoryStmt->execute([$query, $query, $query]);
        } else {
            $categoryStmt->execute();
        }
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get price range
        $priceSql = "SELECT MIN(final_price) as min_price, MAX(final_price) as max_price
                     FROM products
                     WHERE is_active = 1";
        if (!empty($query)) {
            $priceSql .= " AND (MATCH(name_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                MATCH(description_en) AGAINST(? IN NATURAL LANGUAGE MODE) OR 
                                MATCH(short_description_en) AGAINST(? IN NATURAL LANGUAGE MODE))";
        }
        
        $priceStmt = $this->pdo->prepare($priceSql);
        if (!empty($query)) {
            $priceStmt->execute([$query, $query, $query]);
        } else {
            $priceStmt->execute();
        }
        $priceRange = $priceStmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'brands' => $brands,
            'categories' => $categories,
            'price_range' => $priceRange
        ];
    }
}
?>
