<?php
// helpers/SKUGenerator.php
// Automatic SKU Generation System with Variant Support and Validation

require_once __DIR__ . '/../db_connect.php';

class SKUGenerator {
    private $pdo;
    const MAX_LENGTH = 100;
    const RESERVED_PREFIXES = ['TEST', 'ADMIN', 'SYS', 'ROOT'];
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    /**
     * Generate a unique SKU for a main product
     * Format: [CATEGORY_ABBR]-[SUBCATEGORY_ABBR]-[NNNN]
     */
    public function generateSKU($categoryId, $subcategoryId = null) {
        $ownsTransaction = !$this->pdo->inTransaction();
        try {
            if ($ownsTransaction) $this->pdo->beginTransaction();
            $this->allocate('category-abbreviations');
            
            // Get abbreviations
            $categoryAbbr = $this->getCategoryAbbreviation($categoryId);
            $subcategoryAbbr = null;
            if ($subcategoryId && $subcategoryId != $categoryId) {
                $subcategoryAbbr = $this->getCategoryAbbreviation($subcategoryId);
            }
            
            // Get next sequence number
            $namespace = 'product:' . $categoryId . ':' . ($subcategoryId ?? 0);
            $skuPrefix = $categoryAbbr . ($subcategoryAbbr ? '-' . $subcategoryAbbr : '') . '-';
            $number = $this->allocate($namespace, $this->nextImportedSequence($skuPrefix));
            
            // Format logic
            $sku = $subcategoryAbbr 
                ? sprintf("%s-%s-%04d", $categoryAbbr, $subcategoryAbbr, $number)
                : sprintf("%s-%04d", $categoryAbbr, $number);
            
            // Collision prevention loop
            $attempts = 0;
            while ($this->skuExists($sku) && $attempts < 50) {
                $number = $this->allocate($namespace);
                $sku = $subcategoryAbbr 
                    ? sprintf("%s-%s-%04d", $categoryAbbr, $subcategoryAbbr, $number)
                    : sprintf("%s-%04d", $categoryAbbr, $number);
                $attempts++;
            }
            
            if ($attempts >= 50) {
                throw new Exception("Failed to generate unique SKU after 50 attempts");
            }
            
            // Update counter for next time
            if (!$this->validateSKU($sku)) throw new InvalidArgumentException('Generated SKU is invalid. Review category abbreviation.');
            if ($ownsTransaction) $this->pdo->commit();
            return $sku;
            
        } catch (Exception $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * Generate a sequential Variant SKU
     * Format: [PARENT_SKU]-V[NN] (e.g., ELEC-0042-V01)
     */
    public function generateVariantSKU($parentSku, $sequenceOffset = 0) {
        if (!$this->pdo->inTransaction()) throw new LogicException('Variant allocation requires a catalog transaction.');
        // Keep enough space for the suffix, even for a maximum-length parent SKU.
        $prefix = strlen($parentSku) > 80 ? substr($parentSku,0,65) . '-' . substr(hash('sha256',$parentSku),0,12) : $parentSku;
        do {
            $nextNum = $this->allocate('variant:' . $prefix, $this->nextImportedSequence($prefix . '-V'));
            $sku = sprintf('%s-V%02d', $prefix, $nextNum);
        } while ($this->skuExists($sku));
        if (!$this->validateSKU($sku)) throw new InvalidArgumentException('Generated variant SKU is invalid.');
        return $sku;
    }

    private function nextImportedSequence($prefix) {
        $stmt = $this->pdo->prepare('SELECT sku FROM sku_registry WHERE sku LIKE ?');
        $stmt->execute([$prefix . '%']);
        $next = 1;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $sku) {
            $suffix = substr($sku, strlen($prefix));
            if (ctype_digit($suffix) && strlen($suffix) <= 9) $next = max($next, (int)$suffix + 1);
        }
        return $next;
    }

    private function allocate($namespace, $floor = 1) {
        $this->pdo->prepare('INSERT INTO sku_sequences(namespace,next_number) VALUES(?,?) ON DUPLICATE KEY UPDATE next_number=GREATEST(next_number,VALUES(next_number))')->execute([$namespace,$floor]);
        $stmt=$this->pdo->prepare('SELECT next_number FROM sku_sequences WHERE namespace=? FOR UPDATE');
        $stmt->execute([$namespace]);
        $number=(int)$stmt->fetchColumn();
        $this->pdo->prepare('UPDATE sku_sequences SET next_number=next_number+1 WHERE namespace=?')->execute([$namespace]);
        return $number;
    }

    public function claim($sku, $type, $id) {
        $sku=strtoupper(trim($sku));
        if (!$this->validateSKU($sku)) throw new InvalidArgumentException('SKU must use letters, numbers and hyphens, up to 100 characters, without a reserved prefix.');
        $stmt=$this->pdo->prepare('SELECT owner_type,owner_id FROM sku_registry WHERE sku=?');
        $stmt->execute([$sku]); $owner=$stmt->fetch();
        if ($owner) {
            if ($owner['owner_type']!==$type || (int)$owner['owner_id']!==(int)$id) throw new InvalidArgumentException('SKU is already assigned or reserved: '.$sku);
        } else {
            // The primary key resolves concurrent cross-table allocations safely.
            $this->pdo->prepare('INSERT INTO sku_registry(sku,owner_type,owner_id) VALUES(?,?,?)')->execute([$sku,$type,$id]);
        }
        return $sku;
    }

    /**
     * Finds the highest existing 'V' number for a parent product
     */
    private function getMaxVariantSequence($parentSku) {
        $sql = "SELECT sku FROM product_variants WHERE sku LIKE :pattern ORDER BY LENGTH(sku) DESC, sku DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pattern' => $parentSku . '-V%']);
        $lastSku = $stmt->fetchColumn();
        
        if ($lastSku && preg_match('/-V(\d+)$/', $lastSku, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }
    
    /**
     * Enhanced Category Abbreviation with Collision Detection
     */
    private function getCategoryAbbreviation($categoryId) {
        $sql = "SELECT abbreviation FROM category_abbreviations WHERE category_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        $result = $stmt->fetchColumn();
        
        if ($result) return $result;

        // Generate new if not exists
        $sqlName = "SELECT name_en FROM categories WHERE id = ?";
        $stmtName = $this->pdo->prepare($sqlName);
        $stmtName->execute([$categoryId]);
        $name = $stmtName->fetchColumn();
        
        if (!$name) throw new Exception("Category ID $categoryId not found");

        $baseAbbr = $this->createAbbreviation($name);
        $finalAbbr = $baseAbbr;
        $counter = 1;

        // Check for abbreviation collision with OTHER categories
        while ($this->abbreviationExists($finalAbbr, $categoryId)) {
            $finalAbbr = substr($baseAbbr, 0, 3) . $counter;
            $counter++;
        }

        // Store it
        $this->pdo->prepare("INSERT INTO category_abbreviations (category_id, abbreviation) VALUES (?, ?)")
                  ->execute([$categoryId, $finalAbbr]);
        
        return $finalAbbr;
    }

    private function abbreviationExists($abbr, $excludeId) {
        $sql = "SELECT COUNT(*) FROM category_abbreviations WHERE abbreviation = ? AND category_id != ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$abbr, $excludeId]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function createAbbreviation($name) {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9\s]/', '', $name));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($words) > 1) {
            $abbr = '';
            foreach (array_slice($words, 0, 4) as $word) {
                $abbr .= substr($word, 0, 1);
            }
            return str_pad($abbr, 2, 'X');
        }
        return substr(str_pad($clean, 4, '0'), 0, 4);
    }
    
    private function getNextNumber($categoryId, $subcategoryId) {
        $sql = "SELECT next_number FROM sku_counters 
                WHERE category_id = ? AND (subcategory_id = ? OR (subcategory_id IS NULL AND ? IS NULL))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, $subcategoryId, $subcategoryId]);
        $result = $stmt->fetchColumn();
        
        if ($result !== false) return (int)$result;
        
        $this->pdo->prepare("INSERT INTO sku_counters (category_id, subcategory_id, next_number) VALUES (?, ?, 1)")
                  ->execute([$categoryId, $subcategoryId]);
        return 1;
    }
    
    private function updateCounter($categoryId, $subcategoryId, $nextNumber) {
        $sql = "UPDATE sku_counters SET next_number = ?, updated_at = NOW() 
                WHERE category_id = ? AND (subcategory_id = ? OR (subcategory_id IS NULL AND ? IS NULL))";
        $this->pdo->prepare($sql)->execute([$nextNumber, $categoryId, $subcategoryId, $subcategoryId]);
    }
    
    public function skuExists($sku) {
        $registry = $this->pdo->prepare('SELECT COUNT(*) FROM sku_registry WHERE sku=?');
        $registry->execute([$sku]);
        if ($registry->fetchColumn()) return true;
        // Check Products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetchColumn() > 0) return true;

        // Check Variants
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM product_variants WHERE sku = ?");
        $stmt->execute([$sku]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function validateSKU($sku) {
        if (empty($sku)) return false;
        if (strlen($sku) > self::MAX_LENGTH) return false;
        
        // Alphanumeric and hyphens only
        if (!preg_match('/^[A-Z0-9\-]+$/i', $sku)) return false;

        // Check Reserved Prefixes
        foreach (self::RESERVED_PREFIXES as $prefix) {
            if (stripos($sku, $prefix) === 0) return false;
        }

        return true;
    }
}
?>
