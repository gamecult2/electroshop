<?php
// models/Category.php
require_once __DIR__ . '/../db_connect.php';

class Category {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function getAll($parentId = null) {
        $sql = "SELECT * FROM categories WHERE is_active = 1";
        $params = [];
        
        if ($parentId !== null) {
            $sql .= " AND parent_id = ?";
            $params[] = $parentId;
        } else {
            $sql .= " AND parent_id IS NULL";
        }
        
        $sql .= " ORDER BY sort_order, name_en";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getWithSubcategories() {
        $categories = $this->getAll();
        
        foreach ($categories as &$category) {
            $category['subcategories'] = $this->getAll($category['id']);
        }
        
        return $categories;
    }

    public function getAllMainCategories() {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name_en";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllFlat() {
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name_en ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get category by slug (supports both main categories and subcategories)
     */
    public function getBySlug($slug, $parentSlug = null) {
        if ($parentSlug) {
            // Looking for subcategory: join with parent
            $sql = "SELECT c.*, p.slug as parent_slug, p.name_en as parent_name_en, p.id as parent_id
                    FROM categories c
                    INNER JOIN categories p ON c.parent_id = p.id
                    WHERE c.slug = ? AND p.slug = ? AND c.is_active = 1 AND p.is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$slug, $parentSlug]);
        } else {
            // Looking for main category
            $sql = "SELECT * FROM categories WHERE slug = ? AND parent_id IS NULL AND is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$slug]);
        }
        return $stmt->fetch();
    }
    
    /**
     * Get the main (parent) category for a given subcategory
     */
    public function getMainCategoryForSubcategory($subcategoryId) {
        $sql = "SELECT p.* FROM categories p
                INNER JOIN categories c ON c.parent_id = p.id
                WHERE c.id = ? AND c.is_active = 1 AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$subcategoryId]);
        return $stmt->fetch();
    }
    
    /**
     * Get breadcrumb trail for a category
     * Returns array: [['id' => 1, 'name_en' => 'Electronics', ...], ['id' => 10, 'name_en' => 'Televisions', ...]]
     */
    public function getBreadcrumb($categoryId) {
        $breadcrumb = [];
        $category = $this->getById($categoryId);
        
        if (!$category) {
            return $breadcrumb;
        }
        
        // Add current category
        $breadcrumb[] = $category;
        
        // If has parent, add parent
        if ($category['parent_id']) {
            $parent = $this->getById($category['parent_id']);
            if ($parent) {
                array_unshift($breadcrumb, $parent);
            }
        }
        
        return $breadcrumb;
    }
    
    /**
     * Get all categories with full hierarchy information
     * Returns nested structure for navigation menus
     */
    public function getAllWithHierarchy() {
        $mainCategories = $this->getAllMainCategories();

        foreach ($mainCategories as &$mainCat) {
            $subcategories = $this->getAll($mainCat['id']);
            // Add product count to each subcategory
            foreach ($subcategories as &$subcategory) {
                $subcategory['product_count'] = $this->getProductCount($subcategory['id'], false); // Direct products only
            }
            $mainCat['subcategories'] = $subcategories;
            $mainCat['product_count'] = $this->getProductCount($mainCat['id'], true); // Include subcategories
        }

        return $mainCategories;
    }
    
    /**
     * Get product count for a category (optionally including subcategories)
     */
    public function getProductCount($categoryId, $includeSubcategories = false) {
        if ($includeSubcategories) {
            // Count products in this category and all its subcategories
            $sql = "SELECT COUNT(DISTINCT p.id) FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE (p.category_id = ? OR c.parent_id = ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId, $categoryId]);
        } else {
            // Count only direct products
            $sql = "SELECT COUNT(*) FROM products WHERE category_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId]);
        }
        return $stmt->fetchColumn();
    }
    
    /**
     * Validate category hierarchy (prevent circular references)
     */
    public function validateHierarchy($categoryId, $newParentId) {
        if ($categoryId == $newParentId) {
            return false; // Cannot be own parent
        }
        
        if (!$newParentId) {
            return true; // Can be root category
        }
        
        // Check if new parent is a descendant (would create cycle)
        $parent = $this->getById($newParentId);
        if ($parent && $parent['parent_id'] == $categoryId) {
            return false; // Would create circular reference
        }
        
        return true;
    }
    
    public function create($data) {
        $sql = "INSERT INTO categories (
                    name_en, description_en,
                    parent_id, image_url, icon_class, slug, is_active, sort_order
                ) VALUES (
                    ?, ?,
                    ?, ?, ?, ?, ?, ?
                )";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['name_en'],
            $data['description_en'] ?? '',
            $data['parent_id'] ?? null,
            $data['image_url'] ?? null,
            $data['icon_class'] ?? '',
            $data['slug'],
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0
        ]);

        return $result ? $this->pdo->lastInsertId() : false;
    }
    
    public function update($id, $data) {
        $sql = "UPDATE categories SET
                    name_en = ?, description_en = ?,
                    parent_id = ?, image_url = ?, icon_class = ?, slug = ?,
                    is_active = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name_en'],
            $data['description_en'] ?? '',
            $data['parent_id'] ?? null,
            $data['image_url'] ?? null,
            $data['icon_class'] ?? '',
            $data['slug'],
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0,
            $id
        ]);
    }
    
    public function delete($id) {
        // Check if category has subcategories or products
        $subcategories = $this->getAll($id);
        if (!empty($subcategories)) {
            return false; // Cannot delete category with subcategories
        }
        
        $sql = "SELECT COUNT(*) FROM products WHERE category_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Cannot delete category with products
        }
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
