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
        require_once __DIR__ . '/Cart.php';
        if ((int)$customerId !== (int)get_current_user_id()) return false;
        try {
            $this->pdo->beginTransaction();
            $cart=new Cart();
            $result=$cart->add($productId,1);
            if (!$result['success']) { $this->pdo->rollBack(); return false; }
            $this->pdo->prepare('DELETE FROM wishlists WHERE customer_id=? AND product_id=?')->execute([$customerId,$productId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
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
