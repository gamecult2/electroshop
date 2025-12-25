<?php
// models/Faq.php - FAQ management model

require_once __DIR__ . '/../db_connect.php';

class Faq {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT f.*, fc.name_en as category_name 
                FROM faqs f 
                LEFT JOIN faq_categories fc ON f.category_id = fc.id 
                WHERE f.is_active = 1 
                ORDER BY f.sort_order, f.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    public function getByCategory($categoryId, $limit = null, $offset = 0) {
        $sql = "SELECT f.*, fc.name_en as category_name 
                FROM faqs f 
                LEFT JOIN faq_categories fc ON f.category_id = fc.id 
                WHERE f.category_id = ? AND f.is_active = 1 
                ORDER BY f.sort_order, f.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId]);
        }
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT f.*, fc.name_en as category_name 
                FROM faqs f 
                LEFT JOIN faq_categories fc ON f.category_id = fc.id 
                WHERE f.id = ? AND f.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getCategories() {
        $sql = "SELECT fc.*, 
                      (SELECT COUNT(*) FROM faqs f WHERE f.category_id = fc.id AND f.is_active = 1) as faq_count
                FROM faq_categories fc 
                WHERE fc.is_active = 1 
                ORDER BY fc.sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $sql = "INSERT INTO faqs (category_id, question_en, answer_en, sort_order, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['question_en'],
            $data['answer_en'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE faqs SET
                    category_id = ?,
                    question_en = ?,
                    answer_en = ?,
                    sort_order = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['question_en'],
            $data['answer_en'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM faqs WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
