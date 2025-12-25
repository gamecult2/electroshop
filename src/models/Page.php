<?php
// models/Page.php - Static page management model

require_once 'db_connect.php';

class Page {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT * FROM pages WHERE is_published = 1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title_en LIKE ? OR content_en LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getBySlug($slug) {
        $sql = "SELECT * FROM pages WHERE slug = ? AND is_published = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM pages WHERE id = ? AND is_published = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $sql = "INSERT INTO pages (
                    title_en, slug, content_en,
                    meta_title_en, meta_description_en,
                    meta_keywords, is_published, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title_en'],
            $data['slug'],
            $data['content_en'],
            $data['meta_title_en'] ?? null,
            $data['meta_description_en'] ?? null,
            $data['meta_keywords'] ?? null,
            $data['is_published'] ?? 0
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE pages SET
                    title_en = ?, slug = ?, content_en = ?,
                    meta_title_en = ?, meta_description_en = ?,
                    meta_keywords = ?, is_published = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title_en'],
            $data['slug'],
            $data['content_en'],
            $data['meta_title_en'] ?? null,
            $data['meta_description_en'] ?? null,
            $data['meta_keywords'] ?? null,
            $data['is_published'] ?? 0,
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "UPDATE pages SET is_published = 0 WHERE id = ?"; // Soft delete
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getPublishedPages() {
        $sql = "SELECT id, title_en, slug FROM pages WHERE is_published = 1 ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
