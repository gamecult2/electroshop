<?php
require_once __DIR__ . '/../db_connect.php';

class Message {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Send a new message
     */
    public function send($conversationId, $senderType, $senderId, $message, $attachments = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Insert message
            $stmt = $this->pdo->prepare("
                INSERT INTO messages (conversation_id, sender_type, sender_id, message, attachments)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $attachmentsJson = $attachments ? json_encode($attachments) : null;
            $stmt->execute([$conversationId, $senderType, $senderId, $message, $attachmentsJson]);
            $messageId = $this->pdo->lastInsertId();
            
            // Update conversation last_message_at
            $this->pdo->prepare("
                UPDATE conversations SET last_message_at = NOW() WHERE id = ?
            ")->execute([$conversationId]);
            
            // Increment unread count for recipient
            $unreadField = $senderType === 'admin' ? 'customer_unread_count' : 'admin_unread_count';
            $this->pdo->prepare("
                UPDATE conversations SET $unreadField = $unreadField + 1 WHERE id = ?
            ")->execute([$conversationId]);
            
            $this->pdo->commit();
            return $messageId;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Get all messages for a conversation
     */
    public function getConversationMessages($conversationId, $limit = 50, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT m.*,
                   CASE 
                       WHEN m.sender_type = 'customer' THEN u.first_name
                       WHEN m.sender_type = 'admin' THEN admin.first_name
                   END as sender_first_name,
                   CASE 
                       WHEN m.sender_type = 'customer' THEN u.last_name
                       WHEN m.sender_type = 'admin' THEN admin.last_name
                   END as sender_last_name,
                   CASE 
                       WHEN m.sender_type = 'customer' THEN u.email
                       WHEN m.sender_type = 'admin' THEN admin.email
                   END as sender_email,
                   sm.id as is_starred
            FROM messages m
            LEFT JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN customers u ON m.sender_type = 'customer' AND m.sender_id = c.user_id
            LEFT JOIN users admin ON m.sender_type = 'admin' AND m.sender_id = admin.id
            LEFT JOIN starred_messages sm ON sm.message_id = m.id AND sm.user_id = c.user_id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$conversationId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single message by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead($id) {
        $stmt = $this->pdo->prepare("
            UPDATE messages SET is_read = 1, read_at = NOW() WHERE id = ? AND is_read = 0
        ");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete a message
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Star/bookmark a message
     */
    public function star($messageId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO starred_messages (user_id, message_id)
                VALUES (?, ?)
            ");
            return $stmt->execute([$userId, $messageId]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Unstar a message
     */
    public function unstar($messageId, $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM starred_messages WHERE user_id = ? AND message_id = ?
        ");
        return $stmt->execute([$userId, $messageId]);
    }
    
    /**
     * Get starred messages for a user
     */
    public function getStarredMessages($userId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*,
                   sm.created_at as starred_at,
                   c.id as conversation_id,
                   p.name_en as product_name
            FROM starred_messages sm
            JOIN messages m ON sm.message_id = m.id
            JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN products p ON c.product_id = p.id
            WHERE sm.user_id = ?
            ORDER BY sm.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Upload attachment
     */
    public function uploadAttachment($file, $conversationId, $slug = null) {
        $uploadDir = __DIR__ . '/../uploads/messages/' . $conversationId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Prepare SEO friendly name
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $baseName = $slug ? create_slug($slug) : 'message-attachment';
        $uniqueId = substr(md5(uniqid()), 0, 8);
        
        $fileName = $baseName . '_' . $uniqueId . '.' . $extension;
        
        // Max 100 chars check
        if (strlen($fileName) > 100) {
            $allowedBaseLen = 100 - strlen($extension) - 10; // -1 for dot, -8 for hash, -1 for underscore
            $baseName = substr($baseName, 0, $allowedBaseLen);
            $fileName = $baseName . '_' . $uniqueId . '.' . $extension;
        }

        $targetPath = $uploadDir . $fileName;
        $relativePath = 'uploads/messages/' . $conversationId . '/' . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        // Using file extension for validation if type is not reliable
        $mimeType = $file['type'];
        
        if (!in_array($mimeType, $allowedTypes)) {
            // Secondary check by extension
            $extToMime = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 
                'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf'
            ];
            if (!isset($extToMime[$extension])) {
                throw new Exception('Invalid file type');
            }
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large');
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $relativePath;
        }
        
        throw new Exception('Failed to upload file');
    }
    
    /**
     * Search messages
     */
    public function search($query, $userId = null, $isAdmin = false) {
        $sql = "
            SELECT m.*,
                   c.id as conversation_id,
                   c.user_id,
                   p.name_en as product_name
            FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN products p ON c.product_id = p.id
            WHERE m.message LIKE ?
        ";
        
        $params = ['%' . $query . '%'];
        
        if (!$isAdmin && $userId) {
            $sql .= " AND c.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY m.created_at DESC LIMIT 50";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Set typing indicator
     */
    public function setTypingIndicator($conversationId, $userType, $userId) {
        // Delete expired indicators
        $this->pdo->prepare("DELETE FROM typing_indicators WHERE expires_at < NOW()")->execute();
        
        // Insert or update typing indicator (expires in 5 seconds)
        $stmt = $this->pdo->prepare("
            INSERT INTO typing_indicators (conversation_id, user_type, user_id, expires_at)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 SECOND))
            ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL 5 SECOND)
        ");
        return $stmt->execute([$conversationId, $userType, $userId]);
    }
    
    /**
     * Get typing indicator status for a conversation
     */
    public function getTypingIndicator($conversationId, $excludeUserType = null) {
        // Clean up expired indicators first
        $this->pdo->prepare("DELETE FROM typing_indicators WHERE expires_at < NOW()")->execute();
        
        $sql = "
            SELECT user_type, user_id, expires_at
            FROM typing_indicators
            WHERE conversation_id = ? AND expires_at > NOW()
        ";
        
        $params = [$conversationId];
        
        if ($excludeUserType) {
            $sql .= " AND user_type != ?";
            $params[] = $excludeUserType;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
