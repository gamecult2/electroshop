<?php
// models/Cart.php
require_once __DIR__ . '/../db_connect.php';

class Cart {
    private $pdo;
    private $customerId;
    private $sessionId;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
        $this->customerId = get_current_user_id();
        $this->sessionId = session_id();
    }
    
    public function add($productId, $quantity = 1, $variantId = null, $selectedOptions = null) {
        // Check if product exists
        $productSql = "SELECT id, stock_quantity, final_price FROM products WHERE id = ? AND is_active = 1";
        $productStmt = $this->pdo->prepare($productSql);
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();
        
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $price = $product['final_price'];
        $stock = $product['stock_quantity'];

        // If variant selected, check variant stock and price
        if ($variantId) {
            $variantSql = "SELECT price, stock_quantity FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1";
            $variantStmt = $this->pdo->prepare($variantSql);
            $variantStmt->execute([$variantId, $productId]);
            $variant = $variantStmt->fetch();

            if (!$variant) {
                return ['success' => false, 'message' => 'Variant not found'];
            }
            $price = $variant['price'];
            $stock = $variant['stock_quantity'];
        }
        
        if ($stock < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }
        
        // Check if item already exists in cart
        $checkSql = "SELECT id, quantity FROM shopping_cart WHERE product_id = ? AND " . 
                   ($variantId ? "variant_id = ?" : "variant_id IS NULL") . " AND " . 
                   ($this->customerId ? "customer_id = ?" : "session_id = ?");
        
        $checkParams = [$productId];
        if ($variantId) {
            $checkParams[] = $variantId;
        }
        $checkParams[] = $this->customerId ?: $this->sessionId;
        
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute($checkParams);
        $existingItem = $checkStmt->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($stock < $newQuantity) {
                return ['success' => false, 'message' => 'Insufficient stock for requested quantity'];
            }
            
            $updateSql = "UPDATE shopping_cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $result = $updateStmt->execute([$newQuantity, $existingItem['id']]);
            
            return $result ? 
                ['success' => true, 'message' => t('cart_updated'), 'item_id' => $existingItem['id']] : 
                ['success' => false, 'message' => 'Failed to update cart'];
        } else {
            // Insert new item
            $insertSql = "INSERT INTO shopping_cart (customer_id, session_id, product_id, variant_id, quantity, price_at_time) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = $this->pdo->prepare($insertSql);
            $result = $insertStmt->execute([
                $this->customerId,
                $this->customerId ? null : $this->sessionId,
                $productId,
                $variantId,
                $quantity,
                $price
            ]);
            
            return $result ? 
                ['success' => true, 'message' => t('item_added_to_cart'), 'item_id' => $this->pdo->lastInsertId()] : 
                ['success' => false, 'message' => 'Failed to add item to cart'];
        }
    }
    
    public function update($itemId, $quantity) {
        if ($quantity <= 0) {
            return $this->remove($itemId);
        }
        
        // Get current cart item with product and variant stock
        $getItemSql = "SELECT sc.*, p.stock_quantity as product_stock, v.stock_quantity as variant_stock 
                       FROM shopping_cart sc 
                       JOIN products p ON sc.product_id = p.id 
                       LEFT JOIN product_variants v ON sc.variant_id = v.id
                       WHERE sc.id = ?";
        $getItemStmt = $this->pdo->prepare($getItemSql);
        $getItemStmt->execute([$itemId]);
        $item = $getItemStmt->fetch();
        
        if (!$item) {
            return ['success' => false, 'message' => 'Cart item not found'];
        }
        
        $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['product_stock'];
        
        if ($availableStock < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock (Available: ' . $availableStock . ')'];
        }
        
        $updateSql = "UPDATE shopping_cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $this->pdo->prepare($updateSql);
        $result = $updateStmt->execute([$quantity, $itemId]);
        
        return $result ? 
            ['success' => true, 'message' => t('cart_updated')] : 
            ['success' => false, 'message' => 'Failed to update cart'];
    }
    
    public function remove($itemId) {
        $deleteSql = "DELETE FROM shopping_cart WHERE id = ? AND (customer_id = ? OR session_id = ?)";
        $deleteStmt = $this->pdo->prepare($deleteSql);
        $result = $deleteStmt->execute([$itemId, $this->customerId, $this->sessionId]);
        
        return $result ? 
            ['success' => true, 'message' => t('item_removed_from_cart')] : 
            ['success' => false, 'message' => 'Failed to remove item from cart'];
    }
    
    public function clear() {
        $params = [];
        $where = [];
        
        if ($this->customerId) {
            $where[] = "customer_id = ?";
            $params[] = $this->customerId;
        }
        
        if ($this->sessionId) {
            $where[] = "session_id = ?";
            $params[] = $this->sessionId;
        }
        
        if (empty($where)) return ['success' => true];

        $deleteSql = "DELETE FROM shopping_cart WHERE (" . implode(" OR ", $where) . ")";
        $deleteStmt = $this->pdo->prepare($deleteSql);
        $result = $deleteStmt->execute($params);
        
        return $result ? 
            ['success' => true, 'message' => 'Cart cleared'] : 
            ['success' => false, 'message' => 'Failed to clear cart'];
    }
    
    public function getCount() {
        $params = [];
        $where = [];
        
        if ($this->customerId) {
            $where[] = "customer_id = ?";
            $params[] = $this->customerId;
        }
        
        if ($this->sessionId) {
            $where[] = "session_id = ?";
            $params[] = $this->sessionId;
        }
        
        if (empty($where)) return 0;

        $sql = "SELECT SUM(quantity) as count FROM shopping_cart WHERE (" . implode(" OR ", $where) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int)($result['count'] ?? 0);
    }
    
    public function getItems() {
        $params = [];
        $where = [];
        
        if ($this->customerId) {
            $where[] = "sc.customer_id = ?";
            $params[] = $this->customerId;
        }
        
        if ($this->sessionId) {
            $where[] = "sc.session_id = ?";
            $params[] = $this->sessionId;
        }
        
        if (empty($where)) return [];
        
        $sql = "SELECT sc.*, p.name_en as product_name, p.final_price, p.stock_quantity as product_stock, 
                       pi.image_url as product_image,
                       v.stock_quantity as variant_stock
                FROM shopping_cart sc
                JOIN products p ON sc.product_id = p.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN product_variants v ON sc.variant_id = v.id
                WHERE (" . implode(" OR ", $where) . ")
                ORDER BY sc.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getItemCount() {
        $params = [];
        $where = [];
        
        if ($this->customerId) {
            $where[] = "customer_id = ?";
            $params[] = $this->customerId;
        }
        
        if ($this->sessionId) {
            $where[] = "session_id = ?";
            $params[] = $this->sessionId;
        }
        
        if (empty($where)) return 0;

        $sql = "SELECT COUNT(*) FROM shopping_cart WHERE (" . implode(" OR ", $where) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        error_log("Cart::getItemCount: customerId=" . ($this->customerId ?? 'NULL') . ", sessionId=" . $this->sessionId . ", count=" . $count);
        return $count;
    }
    
    public function getSubtotal() {
        $params = [];
        $where = [];
        
        if ($this->customerId) {
            $where[] = "sc.customer_id = ?";
            $params[] = $this->customerId;
        }
        
        if ($this->sessionId) {
            $where[] = "sc.session_id = ?";
            $params[] = $this->sessionId;
        }
        
        if (empty($where)) return 0;

        $sql = "SELECT SUM(sc.quantity * sc.price_at_time) as subtotal
                FROM shopping_cart sc
                JOIN products p ON sc.product_id = p.id
                WHERE (" . implode(" OR ", $where) . ")
                AND p.is_active = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['subtotal'] ? $result['subtotal'] : 0;
    }
    
    public function isEmpty() {
        return $this->getItemCount() == 0;
    }
}
?>
