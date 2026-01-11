<?php
// models/Order.php - Order management model

require_once __DIR__ . '/../db_connect.php';

class Order {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function getAll($limit = 20, $offset = 0, $filters = []) {
        $sql = "SELECT o.*, c.first_name, c.last_name, c.email 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id";
        $params = [];
        $where = ["o.is_archived = ?"];
        $params[] = isset($filters['is_archived']) ? (int)$filters['is_archived'] : 0;
        
        if (!empty($filters['status'])) {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM orders o LEFT JOIN customers c ON o.customer_id = c.id";
        $params = [];
        $where = ["o.is_archived = ?"];
        $params[] = isset($filters['is_archived']) ? (int)$filters['is_archived'] : 0;
        
        if (!empty($filters['status'])) {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function archive($id, $isArchived = 1) {
        $sql = "UPDATE orders SET is_archived = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([(int)$isArchived, $id]);
    }

    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Delete order items
            $this->pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);
            
            // 2. Delete order history
            $this->pdo->prepare("DELETE FROM order_status_history WHERE order_id = ?")->execute([$id]);
            
            // 3. Delete order record
            $result = $this->pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
            
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Order::delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        $sql = "SELECT o.*, c.first_name as user_first_name, c.last_name as user_last_name, c.email as user_email 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                WHERE o.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status, $adminId = null, $notes = '') {
        try {
            $this->pdo->beginTransaction();
            
            // Update order status
            $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$status, $id]);

            // Add to history (using correct column names: status, admin_user_id, note)
            $historySql = "INSERT INTO order_status_history (order_id, status, admin_user_id, note) VALUES (?, ?, ?, ?)";
            $historyStmt = $this->pdo->prepare($historySql);
            $historyStmt->execute([$id, $status, $adminId, $notes]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order::updateStatus Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateNotes($id, $adminNotes, $internalNotes) {
        $sql = "UPDATE orders SET admin_notes = ?, internal_notes = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$adminNotes, $internalNotes, $id]);
    }

    public function updatePaymentStatus($id, $status) {
        $sql = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    public function updatePaymentDetails($id, $status, $transactionId, $gatewayResponse = null) {
        $responseJson = $gatewayResponse ? json_encode($gatewayResponse) : null;
        $sql = "UPDATE orders SET payment_status = ?, transaction_id = ?, payment_gateway_response = ?, payment_method = 'chargily', updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $transactionId, $responseJson, $id]);
    }

    public function updateTracking($id, $trackingNumber, $estimatedDelivery = null) {
        $sql = "UPDATE orders SET tracking_number = ?, estimated_delivery = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$trackingNumber, $estimatedDelivery, $id]);
    }

    public function cancel($id, $reason = '') {
        try {
            $this->pdo->beginTransaction();

            // 1. Update order status
            $sql = "UPDATE orders SET status = 'cancelled', cancelled_at = NOW(), cancelled_reason = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reason, $id]);

            // 2. Get order items to restore inventory
            $items = $this->getItems($id);
            
            // 3. Restore inventory for each item
            $restoreSql = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
            $restoreStmt = $this->pdo->prepare($restoreSql);

            foreach ($items as $item) {
                $restoreStmt->execute([$item['quantity'], $item['product_id']]);
                
                // If there's a variant, we should also restore variant stock if it exists in DB
                if (!empty($item['variant_id'])) {
                    $vRestoreSql = "UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE id = ?";
                    $vRestoreStmt = $this->pdo->prepare($vRestoreSql);
                    $vRestoreStmt->execute([$item['quantity'], $item['variant_id']]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order::cancel Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getItems($orderId) {
        $sql = "SELECT oi.*, 
                       (SELECT image_url FROM product_images WHERE product_id = oi.product_id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as product_image,
                       COALESCE(oi.variant_name, pv.variant_name) as variant_name,
                       oi.attributes_json
                FROM order_items oi 
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getAllOrdersWithUserDetails($filters = []) {
        $sql = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone,
                       COALESCE(o.customer_name, CONCAT(c.first_name, ' ', c.last_name)) AS username
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id";
        
        $where = [];
        $params = [];

        // Always filter by archived status
        $where[] = "o.is_archived = ?";
        $params[] = isset($filters['is_archived']) ? (int)$filters['is_archived'] : 0;

        // Filter by status if provided
        if (!empty($filters['status'])) {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
        }

        // Filter by search if provided
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            // Add parameter 5 times for the 5 OR conditions
            for($i=0; $i<5; $i++) $params[] = $searchTerm;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function getUserOrders($customerId, $limit = null) {
        $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
        $params = [$customerId];
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function getOrderStatusHistory($orderId) {
        $sql = "SELECT osh.*, u.first_name, u.last_name 
                FROM order_status_history osh
                LEFT JOIN users u ON osh.admin_user_id = u.id
                WHERE osh.order_id = ?
                ORDER BY osh.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        $rows = $stmt->fetchAll();
        
        // Map 'status' to 'status_to' if needed by view, or just use 'status'
        return array_map(function($row) {
            $row['status_to'] = $row['status'];
            $row['notes'] = $row['note'];
            return $row;
        }, $rows);
    }

    public function create($data) {
        try {
            $this->pdo->beginTransaction();

            // 1. Create order record
            $sql = "INSERT INTO orders (
                        order_number, customer_id, customer_name, customer_email,
                        subtotal, shipping_cost, 
                        tax_amount, discount_amount, total_amount, 
                        billing_address, shipping_address, delivery_option,
                        wilaya, daira, commune, delivery_notes, payment_method, status, payment_status
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, ?, 
                        ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, 'pending', 'pending'
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['order_number'],
                $data['customer_id'],
                $data['customer_name'] ?? null,
                $data['customer_email'] ?? null,
                $data['subtotal'],
                $data['shipping_cost'],
                $data['tax_amount'] ?? 0,
                $data['discount_amount'] ?? 0,
                $data['total_amount'],
                json_encode($data['billing_address']),
                json_encode($data['shipping_address']),
                $data['delivery_option'],
                $data['wilaya'],
                $data['daira'],
                $data['commune'],
                $data['delivery_notes'] ?? null,
                $data['payment_method']
            ]);

            $orderId = $this->pdo->lastInsertId();

            // 2. Add order items
            $itemSql = "INSERT INTO order_items (
                            order_id, product_id, variant_id, variant_name, attributes_json, product_name, 
                            product_sku, quantity, price_at_purchase, total_price
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $itemStmt = $this->pdo->prepare($itemSql);

            foreach ($data['items'] as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'] ?? null,
                    $item['variant_name'] ?? null,
                    $item['attributes_json'] ?? null,
                    $item['product_name'],
                    $item['product_sku'] ?? null,
                    $item['quantity'],
                    $item['price_at_purchase'] ?? $item['price_at_time'],
                    $item['quantity'] * ($item['price_at_purchase'] ?? $item['price_at_time'])
                ]);

                // 3. Update product stock
                $stockSql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $stockStmt = $this->pdo->prepare($stockSql);
                $stockStmt->execute([$item['quantity'], $item['product_id']]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }
}
?>
