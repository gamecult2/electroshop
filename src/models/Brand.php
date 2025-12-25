<?php
// models/Brand.php
require_once __DIR__ . '/../db_connect.php';

class Brand {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function getAll() {
        $sql = "SELECT * FROM brands WHERE is_active = 1 ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM brands WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $sql = "INSERT INTO brands (name, description, logo_url, website_url, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['logo_url'] ?? null,
            $data['website_url'] ?? null,
            $data['is_active'] ?? 1
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE brands SET name = ?, description = ?, logo_url = ?, website_url = ?, is_active = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['logo_url'] ?? null,
            $data['website_url'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "UPDATE brands SET is_active = 0 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
