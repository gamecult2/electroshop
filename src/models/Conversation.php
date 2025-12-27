<?php
require_once __DIR__ . '/../db_connect.php';

class Conversation {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new conversation
     */
    public function create($userId, $productId = null, $initialMessage = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Check if there's an existing open conversation for this product
            if ($productId) {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM conversations 
                    WHERE user_id = ? AND product_id = ? AND status != 'closed'
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$userId, $productId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $this->pdo->commit();
                    return $existing['id'];
                }
            }
            
            // Create new conversation
            $stmt = $this->pdo->prepare("
                INSERT INTO conversations (user_id, product_id, last_message_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $productId]);
            $conversationId = $this->pdo->lastInsertId();
            
            // Add initial message if provided
            if ($initialMessage) {
                $msgStmt = $this->pdo->prepare("
                    INSERT INTO messages (conversation_id, sender_type, sender_id, message)
                    VALUES (?, 'customer', ?, ?)
                ");
                $msgStmt->execute([$conversationId, $userId, $initialMessage]);
                
                // Update admin unread count
                $this->pdo->prepare("
                    UPDATE conversations SET admin_unread_count = 1 WHERE id = ?
                ")->execute([$conversationId]);
            }
            
            $this->pdo->commit();
            return $conversationId;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Get conversation by ID with full details
     */
    public function getById($id, $userId = null) {
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   u.first_name as user_first_name,
                   u.last_name as user_last_name,
                   u.email as user_email,
                   p.name_en as product_name,
                   p.id as product_id,
                   p.price as product_price,
                   p.sku as product_sku,
                   admin.first_name as assigned_admin_first_name,
                   admin.last_name as assigned_admin_last_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order LIMIT 1) as product_image
            FROM conversations c
            JOIN customers u ON c.user_id = u.id
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN users admin ON c.assigned_to = admin.id
            WHERE c.id = ?
            " . ($userId ? " AND c.user_id = ?" : "")
        );
        
        $params = [$id];
        if ($userId) $params[] = $userId;
        
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Get all conversations for a user
     */
    public function getUserConversations($userId, $status = null) {
        $sql = "
            SELECT c.*,
                   p.name_en as product_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order LIMIT 1) as product_image,
                   (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT sender_type FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_sender_type
            FROM conversations c
            LEFT JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ";

        $params = [$userId];

        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.last_message_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all conversations for a user with pagination
     */
    public function getUserConversationsWithPagination($userId, $limit, $offset, $status = null) {
        $sql = "
            SELECT c.*,
                   p.name_en as product_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order LIMIT 1) as product_image,
                   (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT sender_type FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_sender_type
            FROM conversations c
            LEFT JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ";

        $params = [$userId];

        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.last_message_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind parameters manually to ensure integer types for LIMIT/OFFSET
        $idx = 1;
        $stmt->bindValue($idx++, $userId, PDO::PARAM_INT);
        if ($status) {
            $stmt->bindValue($idx++, $status, PDO::PARAM_STR);
        }
        $stmt->bindValue($idx++, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue($idx++, (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get total count of conversations for a user
     */
    public function getUserConversationsCount($userId, $status = null) {
        $sql = "SELECT COUNT(*) FROM conversations c WHERE c.user_id = :user_id";

        if ($status) {
            $sql .= " AND c.status = :status";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($status) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    /**
     * Get all conversations for admin dashboard
     */
    public function getAllConversations($filters = []) {
        $sql = "
            SELECT c.*,
                   u.first_name as user_first_name,
                   u.last_name as user_last_name,
                   u.email as user_email,
                   p.name_en as product_name,
                   p.sku as product_sku,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order LIMIT 1) as product_image,
                   (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                   admin.first_name as assigned_admin_first_name,
                   admin.last_name as assigned_admin_last_name
            FROM conversations c
            JOIN customers u ON c.user_id = u.id
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN users admin ON c.assigned_to = admin.id
            WHERE 1=1
        ";
        
        $params = [];

        // Always filter by archived status unless requested otherwise
        $sql .= " AND c.is_archived = ?";
        $params[] = isset($filters['is_archived']) ? (int)$filters['is_archived'] : 0;
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND c.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND c.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['product_id'])) {
            $sql .= " AND c.product_id = ?";
            $params[] = $filters['product_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND c.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.name_en LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY c.last_message_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Update conversation status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE conversations 
            SET status = ?, 
                closed_at = CASE WHEN ? = 'closed' THEN NOW() ELSE NULL END
            WHERE id = ?
        ");
        return $stmt->execute([$status, $status, $id]);
    }
    
    /**
     * Update conversation priority
     */
    public function updatePriority($id, $priority) {
        $stmt = $this->pdo->prepare("UPDATE conversations SET priority = ? WHERE id = ?");
        return $stmt->execute([$priority, $id]);
    }
    
    /**
     * Assign conversation to admin
     */
    public function assignTo($id, $adminId) {
        $stmt = $this->pdo->prepare("UPDATE conversations SET assigned_to = ? WHERE id = ?");
        return $stmt->execute([$adminId, $id]);
    }
    
    /**
     * Update tags
     */
    public function updateTags($id, $tags) {
        $tagsJson = json_encode($tags);
        $stmt = $this->pdo->prepare("UPDATE conversations SET tags = ? WHERE id = ?");
        return $stmt->execute([$tagsJson, $id]);
    }
    
    /**
     * Mark messages as read
     */
    public function markAsRead($conversationId, $readerType) {
        $unreadField = $readerType === 'admin' ? 'admin_unread_count' : 'customer_unread_count';
        
        $stmt = $this->pdo->prepare("
            UPDATE conversations SET $unreadField = 0 WHERE id = ?
        ");
        $stmt->execute([$conversationId]);
        
        // Also mark individual messages as read
        $senderType = $readerType === 'admin' ? 'customer' : 'admin';
        $stmt = $this->pdo->prepare("
            UPDATE messages 
            SET is_read = 1, read_at = NOW()
            WHERE conversation_id = ? AND sender_type = ? AND is_read = 0
        ");
        return $stmt->execute([$conversationId, $senderType]);
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(customer_unread_count) as total
            FROM conversations
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get admin unread count (all conversations)
     */
    public function getAdminUnreadCount($adminId = null) {
        $sql = "SELECT SUM(admin_unread_count) as total FROM conversations WHERE 1=1";
        $params = [];
        
        if ($adminId) {
            $sql .= " AND (assigned_to = ? OR assigned_to IS NULL)";
            $params[] = $adminId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Reopen a closed conversation
     */
    public function reopen($id) {
        $stmt = $this->pdo->prepare("
            UPDATE conversations 
            SET status = 'open', closed_at = NULL, last_message_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Archive or restore a conversation
     */
    public function archive($id, $isArchived = 1) {
        $stmt = $this->pdo->prepare("UPDATE conversations SET is_archived = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([(int)$isArchived, $id]);
    }
}
