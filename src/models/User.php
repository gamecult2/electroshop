<?php
// models/User.php
require_once __DIR__ . '/../db_connect.php';

class User {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $updateSql = "UPDATE users SET last_login_at = NOW() WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([$user['id']]);
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function create($userData) {
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $userData['email'],
            $hashedPassword,
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'] ?? 'moderator',
            $userData['is_active'] ?? 1
        ]);
    }

    public function update($id, $userData) {
        $fields = [];
        $params = [];
        
        $updatableFields = ['email', 'first_name', 'last_name', 'role', 'is_active'];
        foreach ($updatableFields as $field) {
            if (isset($userData[$field])) {
                $fields[] = "$field = ?";
                $params[] = $userData[$field];
            }
        }
        
        if (!empty($userData['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) return false;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
