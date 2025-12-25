<?php
// models/Review.php - Product reviews model

require_once 'db_connect.php';

class Review {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function addReview($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Add the main review
            $sql = "INSERT INTO reviews (
                        product_id, customer_id, rating, title, review_text, is_verified_purchase, is_approved
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['product_id'],
                $data['customer_id'], 
                $data['rating'],
                $data['title'] ?? null,
                $data['review_text'],
                $data['is_verified_purchase'] ?? 0,
                $data['is_approved'] ?? 0  // Usually needs admin approval
            ]);
            
            $reviewId = $this->pdo->lastInsertId();
            
            // Add review images if provided
            if (!empty($data['images'])) {
                foreach ($data['images'] as $image) {
                    $imageSql = "INSERT INTO review_images (review_id, image_url) VALUES (?, ?)";
                    $imageStmt = $this->pdo->prepare($imageSql);
                    $imageStmt->execute([$reviewId, $image]);
                }
            }
            
            $this->pdo->commit();
            return $reviewId;
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    public function getForProduct($productId, $limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT r.*, c.first_name, c.last_name, 
                       (SELECT COUNT(*) FROM review_votes rv WHERE rv.review_id = r.id AND rv.vote = 'helpful') as helpful_count,
                       (SELECT COUNT(*) FROM review_votes rv WHERE rv.review_id = r.id AND rv.vote = 'not_helpful') as not_helpful_count
                FROM reviews r
                JOIN customers c ON r.customer_id = c.id
                WHERE r.product_id = ? AND r.is_approved = 1";
        
        $params = [$productId];
        
        if (!empty($filters['min_rating'])) {
            $sql .= " AND r.rating >= ?";
            $params[] = $filters['min_rating'];
        }
        
        if (!empty($filters['verified_only'])) {
            $sql .= " AND r.is_verified_purchase = 1";
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAverageRating($productId) {
        $sql = "SELECT 
                    AVG(rating) as average_rating, 
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews 
                WHERE product_id = ? AND is_approved = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserReview($customerId, $productId) {
        $sql = "SELECT * FROM reviews WHERE customer_id = ? AND product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId, $productId]);
        return $stmt->fetch();
    }
    
    public function updateReview($reviewId, $data) {
        $sql = "UPDATE reviews SET 
                    rating = ?, 
                    title = ?, 
                    review_text = ?, 
                    updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['rating'],
            $data['title'] ?? null,
            $data['review_text'],
            $reviewId
        ]);
    }
    
    public function deleteReview($reviewId) {
        // Delete review and associated images
        $this->pdo->beginTransaction();
        
        try {
            // Delete images first
            $imageSql = "DELETE FROM review_images WHERE review_id = ?";
            $imageStmt = $this->pdo->prepare($imageSql);
            $imageResult = $imageStmt->execute([$reviewId]);
            
            // Delete votes
            $voteSql = "DELETE FROM review_votes WHERE review_id = ?";
            $voteStmt = $this->pdo->prepare($voteSql);
            $voteResult = $voteStmt->execute([$reviewId]);
            
            // Delete review
            $reviewSql = "DELETE FROM reviews WHERE id = ?";
            $reviewStmt = $this->pdo->prepare($reviewSql);
            $reviewResult = $reviewStmt->execute([$reviewId]);
            
            $this->pdo->commit();
            return $reviewResult;
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    public function approveReview($reviewId) {
        $sql = "UPDATE reviews SET is_approved = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$reviewId]);
    }
    
    public function rejectReview($reviewId) {
        $sql = "UPDATE reviews SET is_approved = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$reviewId]);
    }
    
    public function addVote($reviewId, $customerId, $session_id, $vote) {
        if (!in_array($vote, ['helpful', 'not_helpful'])) {
            return false;
        }
        
        $sql = "INSERT INTO review_votes (review_id, customer_id, session_id, vote, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE vote = VALUES(vote), created_at = NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$reviewId, $customerId, $session_id, $vote]);
    }
    
    public function getReviewImages($reviewId) {
        $sql = "SELECT * FROM review_images WHERE review_id = ? ORDER BY sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$reviewId]);
        return $stmt->fetchAll();
    }
    
    public function getRecentReviews($limit = 10) {
        $sql = "SELECT r.*, c.first_name, c.last_name, p.name_en as product_name
                FROM reviews r
                JOIN customers c ON r.customer_id = c.id
                JOIN products p ON r.product_id = p.id
                WHERE r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function hasPurchasedProduct($customerId, $productId) {
        $sql = "SELECT COUNT(*) 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.customer_id = ? 
                AND oi.product_id = ? 
                AND o.status = 'delivered'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId, $productId]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
