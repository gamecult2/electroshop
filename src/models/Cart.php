<?php
// models/Cart.php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../helpers/CatalogRules.php';

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
        $ownsTransaction = !$this->pdo->inTransaction();
        try {
            $quantity = CatalogRules::quantity($quantity);
            $productId = CatalogRules::quantity($productId);
            if ($variantId !== null) $variantId = CatalogRules::quantity($variantId);
            if ($ownsTransaction) $this->pdo->beginTransaction();
            $item = CatalogRules::sellable($this->pdo, $productId, $variantId, true);
            $owner = $this->customerId ? 'customer_id = ?' : 'session_id = ? AND customer_id IS NULL';
            $stmt=$this->pdo->prepare("SELECT id, quantity FROM shopping_cart WHERE product_id=? AND variant_id <=> ? AND ($owner) FOR UPDATE");
            $stmt->execute([$productId,$variantId,$this->customerId ?: $this->sessionId]);
            $existing=$stmt->fetch();
            $total=$quantity + (int)($existing['quantity'] ?? 0);
            if ($total > $item['stock_quantity']) throw new DomainException('Insufficient stock for the requested quantity.');
            if ($existing) {
                $this->pdo->prepare('UPDATE shopping_cart SET quantity=?,price_at_time=?,updated_at=NOW() WHERE id=?')->execute([$total,$item['price_at_time'],$existing['id']]);
                $id=$existing['id'];
            } else {
                $this->pdo->prepare('INSERT INTO shopping_cart(customer_id,session_id,product_id,variant_id,quantity,price_at_time) VALUES(?,?,?,?,?,?)')->execute([$this->customerId,$this->customerId ? null : $this->sessionId,$productId,$variantId,$quantity,$item['price_at_time']]);
                $id=$this->pdo->lastInsertId();
            }
            if ($ownsTransaction) $this->pdo->commit();
            return ['success'=>true,'message'=>t('item_added_to_cart'),'item_id'=>$id];
        } catch (DomainException | InvalidArgumentException $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success'=>false,'message'=>$e->getMessage(),'choose_options'=>!$variantId && CatalogRules::hasVariants($this->pdo,$productId)];
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update($itemId, $quantity) {
        try {
            $quantity=CatalogRules::quantity($quantity,true);
            if ($quantity===0) return $this->remove($itemId);
            $owner=$this->customerId ? 'customer_id=?' : 'session_id=? AND customer_id IS NULL';
            $this->pdo->beginTransaction();
            // Acquire catalog locks before cart locks, as add and checkout do.
            $lookup=$this->pdo->prepare("SELECT product_id,variant_id FROM shopping_cart WHERE id=? AND ($owner)");
            $lookup->execute([$itemId,$this->customerId ?: $this->sessionId]);
            $identity=$lookup->fetch();
            if (!$identity) throw new DomainException('Cart item not found.');
            $item=CatalogRules::sellable($this->pdo,$identity['product_id'],$identity['variant_id'],true);
            $stmt=$this->pdo->prepare("SELECT * FROM shopping_cart WHERE id=? AND ($owner) FOR UPDATE");
            $stmt->execute([$itemId,$this->customerId ?: $this->sessionId]);
            $row=$stmt->fetch();
            if (!$row) throw new DomainException('Cart item not found.');
            if (!empty($row['unavailable_reason'])) throw new DomainException($row['unavailable_reason']);
            if ($row['product_id'] != $identity['product_id'] || $row['variant_id'] != $identity['variant_id']) throw new DomainException('Cart changed. Please refresh and try again.');
            if ($quantity > $item['stock_quantity']) throw new DomainException('Insufficient stock.');
            $this->pdo->prepare('UPDATE shopping_cart SET quantity=?,price_at_time=?,updated_at=NOW() WHERE id=?')->execute([$quantity,$item['price_at_time'],$itemId]);
            $this->pdo->commit();
            return ['success'=>true,'message'=>t('cart_updated')];
        } catch (DomainException | InvalidArgumentException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success'=>false,'message'=>$e->getMessage()];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
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
        
        $sql = "SELECT sc.*, ROUND(COALESCE(v.price,p.price) * (1 - p.discount_percentage / 100),2) AS price_at_time, p.name_en as product_name, p.final_price, p.stock_quantity as product_stock,
                       pi.image_url as product_image,
                       v.stock_quantity as variant_stock,
                       COALESCE(sc.unavailable_reason, CASE WHEN p.is_active=0 THEN 'This product is no longer available.' WHEN sc.variant_id IS NOT NULL AND (v.id IS NULL OR v.is_active=0) THEN 'This variant is no longer available.' WHEN sc.variant_id IS NULL AND EXISTS(SELECT 1 FROM product_variants x WHERE x.product_id=p.id) THEN 'Choose product options before checkout.' END) AS unavailable_reason,
                       v.variant_name,
                       COALESCE(v.sku, p.sku) as product_sku,
                       (SELECT JSON_OBJECTAGG(va.attribute_name, va.attribute_value) 
                        FROM variant_attributes va 
                        WHERE va.product_variant_id = v.id) as attributes_json
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

        $sql = "SELECT SUM(sc.quantity * ROUND(COALESCE(v.price,p.price) * (1 - p.discount_percentage / 100),2)) as subtotal
                FROM shopping_cart sc
                JOIN products p ON sc.product_id = p.id
                LEFT JOIN product_variants v ON sc.variant_id=v.id
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
