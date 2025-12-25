<?php
// helpers/SKUGenerator.php
// Automatic SKU Generation System

require_once __DIR__ . '/../db_connect.php';

class SKUGenerator {
    private $pdo;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    /**
     * Generate a unique SKU for a product
     * Format: [CATEGORY_ABBR]-[SUBCATEGORY_ABBR]-[NNNN]
     * Example: ELEC-PHON-0042
     * 
     * @param int $categoryId The category ID
     * @param int|null $subcategoryId The subcategory ID (optional)
     * @return string The generated unique SKU
     */
    public function generateSKU($categoryId, $subcategoryId = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Get category abbreviation
            $categoryAbbr = $this->getCategoryAbbreviation($categoryId);
            
            // Get subcategory abbreviation if provided
            $subcategoryAbbr = null;
            if ($subcategoryId && $subcategoryId != $categoryId) {
                $subcategoryAbbr = $this->getCategoryAbbreviation($subcategoryId);
            }
            
            // Get the next available number
            $number = $this->getNextNumber($categoryId, $subcategoryId);
            
            // Format the SKU
            if ($subcategoryAbbr) {
                $sku = sprintf("%s-%s-%04d", $categoryAbbr, $subcategoryAbbr, $number);
            } else {
                $sku = sprintf("%s-%04d", $categoryAbbr, $number);
            }
            
            // Ensure uniqueness (in case of race conditions)
            $attempts = 0;
            while ($this->skuExists($sku) && $attempts < 100) {
                $number++;
                if ($subcategoryAbbr) {
                    $sku = sprintf("%s-%s-%04d", $categoryAbbr, $subcategoryAbbr, $number);
                } else {
                    $sku = sprintf("%s-%04d", $categoryAbbr, $number);
                }
                $attempts++;
            }
            
            if ($attempts >= 100) {
                throw new Exception("Failed to generate unique SKU after 100 attempts");
            }
            
            // Update the counter
            $this->updateCounter($categoryId, $subcategoryId, $number + 1);
            
            $this->pdo->commit();
            return $sku;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("SKU Generation Error: " . $e->getMessage());
            throw new Exception("Failed to generate SKU: " . $e->getMessage());
        }
    }
    
    /**
     * Get category abbreviation from database
     */
    private function getCategoryAbbreviation($categoryId) {
        $sql = "SELECT ca.abbreviation 
                FROM category_abbreviations ca 
                WHERE ca.category_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        $result = $stmt->fetchColumn();
        
        if (!$result) {
            // If no abbreviation exists, create one from category name
            $sql = "SELECT name_en FROM categories WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId]);
            $categoryName = $stmt->fetchColumn();
            
            if ($categoryName) {
                $abbreviation = $this->createAbbreviation($categoryName);
                
                // Store it for future use
                $insertSql = "INSERT INTO category_abbreviations (category_id, abbreviation) 
                             VALUES (?, ?) 
                             ON DUPLICATE KEY UPDATE abbreviation = VALUES(abbreviation)";
                $insertStmt = $this->pdo->prepare($insertSql);
                $insertStmt->execute([$categoryId, $abbreviation]);
                
                return $abbreviation;
            }
            
            throw new Exception("Category not found: " . $categoryId);
        }
        
        return $result;
    }
    
    /**
     * Create an abbreviation from a category name
     */
    private function createAbbreviation($name) {
        // Remove special characters and spaces
        $clean = preg_replace('/[^A-Za-z0-9\s]/', '', $name);
        
        // Split into words
        $words = preg_split('/\s+/', $clean);
        
        if (count($words) > 1) {
            // Take first letter of each word (max 4 letters)
            $abbr = '';
            foreach (array_slice($words, 0, 4) as $word) {
                $abbr .= strtoupper(substr($word, 0, 1));
            }
            return $abbr;
        } else {
            // Take first 4 letters of single word
            return strtoupper(substr($clean, 0, 4));
        }
    }
    
    /**
     * Get the next available number for a category combination
     */
    private function getNextNumber($categoryId, $subcategoryId) {
        $sql = "SELECT next_number FROM sku_counters 
                WHERE category_id = ? AND (subcategory_id = ? OR (subcategory_id IS NULL AND ? IS NULL))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, $subcategoryId, $subcategoryId]);
        $result = $stmt->fetchColumn();
        
        if ($result !== false) {
            return (int)$result;
        }
        
        // Initialize counter if it doesn't exist
        $insertSql = "INSERT INTO sku_counters (category_id, subcategory_id, next_number) 
                     VALUES (?, ?, 1)";
        $insertStmt = $this->pdo->prepare($insertSql);
        $insertStmt->execute([$categoryId, $subcategoryId]);
        
        return 1;
    }
    
    /**
     * Update the counter for the next SKU
     */
    private function updateCounter($categoryId, $subcategoryId, $nextNumber) {
        $sql = "UPDATE sku_counters 
                SET next_number = ?, updated_at = NOW() 
                WHERE category_id = ? AND (subcategory_id = ? OR (subcategory_id IS NULL AND ? IS NULL))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nextNumber, $categoryId, $subcategoryId, $subcategoryId]);
    }
    
    /**
     * Check if a SKU already exists
     */
    private function skuExists($sku) {
        $sql = "SELECT COUNT(*) FROM products WHERE sku = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sku]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Validate SKU format
     */
    public function validateSKU($sku) {
        // Format: XXXX-XXXX-0000 or XXXX-0000
        return preg_match('/^[A-Z]{2,4}(-[A-Z]{2,4})?-\d{4}$/', $sku);
    }
    
    /**
     * Get all category abbreviations
     */
    public function getAllAbbreviations() {
        $sql = "SELECT ca.*, c.name_en 
                FROM category_abbreviations ca 
                JOIN categories c ON ca.category_id = c.id 
                ORDER BY c.name_en";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update category abbreviation
     */
    public function updateAbbreviation($categoryId, $abbreviation) {
        // Validate abbreviation format (2-4 uppercase letters, no special chars)
        $abbreviation = strtoupper(trim($abbreviation));
        if (!preg_match('/^[A-Z]{2,4}$/', $abbreviation)) {
            throw new Exception("Invalid abbreviation format. Use 2-4 uppercase letters only.");
        }
        
        $sql = "INSERT INTO category_abbreviations (category_id, abbreviation) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE abbreviation = VALUES(abbreviation)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$categoryId, $abbreviation]);
    }
}
?>
