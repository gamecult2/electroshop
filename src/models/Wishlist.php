<?php
// models/Wishlist.php - Wishlist model

require_once 'db_connect.php';

class Wishlist {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function add($customerId, $productId) {
        $sql = "INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = NOW()";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$customerId, $productId]);
    }
    
    public function remove($customerId, $productId) {
        $sql = "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$customerId, $productId]);
    }
    
    public function isWishlisted($customerId, $productId) {
        $sql = "SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId, $productId]);
        return $stmt->fetch() !== false;
    }
    
    public function getByUser($customerId, $limit = null, $offset = 0) {
        $sql = "SELECT w.*, p.*, c.name_en as category_name, b.name as brand_name
                FROM wishlists w
                JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE w.customer_id = ? AND p.is_active = 1
                ORDER BY w.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params = [$customerId, $limit, $offset];
        } else {
            $params = [$customerId];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getCount($customerId) {
        $sql = "SELECT COUNT(*) FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.customer_id = ? AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchColumn();
    }
    
    public function moveItemToCart($customerId, $productId) {
        // Remove from wishlist and add to cart in a transaction
        try {
            $this->pdo->beginTransaction();
            
            // Remove from wishlist
            $removeSql = "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?";
            $removeStmt = $this->pdo->prepare($removeSql);
            $removeResult = $removeStmt->execute([$customerId, $productId]);
            
            // Add to cart
            $productSql = "SELECT price FROM products WHERE id = ?";
            $productStmt = $this->pdo->prepare($productSql);
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();
            
            if ($product) {
                $addToCartSql = "INSERT INTO shopping_cart (customer_id, product_id, quantity, price_at_time, created_at) VALUES (?, ?, 1, ?, NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + 1";
                $addToCartStmt = $this->pdo->prepare($addToCartSql);
                $addResult = $addToCartStmt->execute([$customerId, $productId, $product['price']]);
                
                if ($removeResult && $addResult) {
                    $this->pdo->commit();
                    return true;
                } else {
                    $this->pdo->rollback();
                    return false;
                }
            } else {
                $this->pdo->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function clearForUser($customerId) {
        $sql = "DELETE FROM wishlists WHERE customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$customerId]);
    }
}
?>
