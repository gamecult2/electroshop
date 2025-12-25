<?php
// models/Customer.php
require_once __DIR__ . '/../db_connect.php';

class Customer {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    public function register($customerData) {
        try {
            // Check if customer already exists
            $checkSql = "SELECT id FROM customers WHERE email = ?";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$customerData['email']]);
            
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => t('email_already_exists')];
            }
            
            // Hash password
            $hashedPassword = password_hash($customerData['password'], PASSWORD_DEFAULT);
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            $sql = "INSERT INTO customers (
                        email, password, first_name, last_name, phone, 
                        date_of_birth, gender, verification_token
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $customerData['email'], 
                $hashedPassword, 
                $customerData['first_name'], 
                $customerData['last_name'], 
                $customerData['phone'] ?? null,
                $customerData['date_of_birth'] ?? null,
                $customerData['gender'] ?? null,
                $verificationToken
            ]);
            
            if ($result) {
                return [
                    'success' => true, 
                    'message' => t('account_created') . ' ' . t('verification_needed'),
                    'customer_id' => $this->pdo->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => t('registration_failed')];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => t('registration_failed')];
        }
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM customers WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            if (defined('EMAIL_VERIFICATION_REQUIRED') && EMAIL_VERIFICATION_REQUIRED && !$customer['email_verified']) {
                return ['success' => false, 'message' => t('email_not_verified')];
            }

            if ($customer['is_banned']) {
                return ['success' => false, 'message' => t('account_banned')];
            }
            
            try {
                $updateSql = "UPDATE customers SET last_login_at = NOW() WHERE id = ?";
                $updateStmt = $this->pdo->prepare($updateSql);
                $updateStmt->execute([$customer['id']]);
            } catch (PDOException $e) {
                // Ignore if column doesn't exist
            }
            
            return [
                'success' => true,
                'customer_id' => $customer['id'],
                'email' => $customer['email'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name']
            ];
        } else {
            return ['success' => false, 'message' => t('invalid_credentials')];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM customers WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $customerData) {
        $sql = "UPDATE customers SET ";
        $params = [];
        $fields = [];
        
        $updatableFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender', 'is_active', 'is_banned'];
        foreach ($updatableFields as $field) {
            if (isset($customerData[$field])) {
                $fields[] = "$field = ?";
                $params[] = $customerData[$field];
            }
        }
        
        if (isset($customerData['password']) && !empty($customerData['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($customerData['password'], PASSWORD_DEFAULT);
        }
        
        $fields[] = "updated_at = NOW()";
        
        if (!empty($fields)) {
            $sql .= implode(', ', $fields) . " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        }
        
        return false;
    }
    
    public function getAll() {
        $sql = "SELECT id, first_name, last_name, CONCAT(first_name, ' ', last_name) AS username, email, phone, created_at, is_active, is_banned FROM customers ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCustomerAddresses($customerId) {
        $sql = "SELECT * FROM user_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    public function addAddress($customerId, $addressData) {
        // If this is the first address, make it default
        $countSql = "SELECT COUNT(*) FROM user_addresses WHERE customer_id = ?";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute([$customerId]);
        $isFirst = $countStmt->fetchColumn() == 0;
        
        $isDefault = $isFirst ? 1 : ($addressData['is_default'] ?? 0);
        
        // If setting as default, unset others
        if ($isDefault) {
            $this->unsetOtherDefaults($customerId);
        }
        
        $sql = "INSERT INTO user_addresses (
                    customer_id, wilaya, daira, commune, postal_code, 
                    street_address, apartment_suite, phone_number, is_default, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $customerId,
            $addressData['wilaya'],
            $addressData['daira'],
            $addressData['commune'],
            $addressData['postal_code'] ?? '',
            $addressData['street_address'],
            $addressData['apartment_suite'] ?? '',
            $addressData['phone_number'],
            $isDefault
        ]);
    }
    
    public function updateAddress($addressId, $customerId, $addressData) {
        // If setting as default, unset others
        if (!empty($addressData['is_default'])) {
            $this->unsetOtherDefaults($customerId);
        }
        
        $fields = [];
        $params = [];
        
        $updatableFields = ['wilaya', 'daira', 'commune', 'postal_code', 'street_address', 'apartment_suite', 'phone_number', 'is_default'];
        foreach ($updatableFields as $field) {
            if (isset($addressData[$field])) {
                $fields[] = "$field = ?";
                $params[] = $addressData[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE user_addresses SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND customer_id = ?";
        $params[] = $addressId;
        $params[] = $customerId;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteAddress($addressId, $customerId) {
        $sql = "DELETE FROM user_addresses WHERE id = ? AND customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$addressId, $customerId]);
    }
    
    public function getAddressById($addressId, $customerId) {
        $sql = "SELECT * FROM user_addresses WHERE id = ? AND customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$addressId, $customerId]);
        return $stmt->fetch();
    }
    
    public function setDefaultAddress($addressId, $customerId) {
        $this->unsetOtherDefaults($customerId);
        
        $sql = "UPDATE user_addresses SET is_default = 1 WHERE id = ? AND customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$addressId, $customerId]);
    }
    
    private function unsetOtherDefaults($customerId) {
        $sql = "UPDATE user_addresses SET is_default = 0 WHERE customer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId]);
    }
    
    // ... other methods as needed, following the same pattern
}
?>
