<?php
// models/Product.php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../helpers/CatalogRules.php';

class Product {
    private $pdo;
    private $skuGenerator;
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
        require_once __DIR__ . '/../helpers/SKUGenerator.php';
        $this->skuGenerator = new SKUGenerator($database);
    }
    
    public function getAll($limit = 20, $offset = 0, $filters = []) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE 1=1";
        
        // Default to showing only active products unless specified
        if (empty($filters['include_inactive'])) {
            $sql .= " AND p.is_active = 1";
        }
        
        $params = [];
        
        // Add filters
        if (!empty($filters['category_id'])) {
            if (!empty($filters['include_subcategories'])) {
                // Include products from subcategories as well
                $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
                $params[] = $filters['category_id'];
                $params[] = $filters['category_id'];
            } else {
                $sql .= " AND p.category_id = ?";
                $params[] = $filters['category_id'];
            }
        }

        // Support for multiple category IDs (e.g., all subcategories of a main category)
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $placeholders = str_repeat('?,', count($filters['category_ids']) - 1) . '?';
            $sql .= " AND p.category_id IN ($placeholders)";
            $params = array_merge($params, $filters['category_ids']);
        }
        
        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (p.name_en LIKE ? OR p.description_en LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.final_price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.final_price <= ?";
            $params[] = $filters['max_price'];
        }

        if (isset($filters['min_stock']) && $filters['min_stock'] !== '') {
            $sql .= " AND p.stock_quantity >= ?";
            $params[] = $filters['min_stock'];
        }

        if (isset($filters['max_stock']) && $filters['max_stock'] !== '') {
            $sql .= " AND p.stock_quantity <= ?";
            $params[] = $filters['max_stock'];
        }
        
        $sql .= " ORDER BY ";
        
        // Sorting
        switch ($filters['sort'] ?? 'default') {
            case 'price_low':
                $sql .= "p.final_price ASC";
                break;
            case 'price_high':
                $sql .= "p.final_price DESC";
                break;
            case 'newest':
                $sql .= "p.created_at DESC";
                break;
            case 'best_selling':
                $sql .= "p.is_best_seller DESC, p.created_at DESC";
                break;
            case 'rating':
                $sql .= "p.rating DESC";
                break;
            default:
                $sql .= "p.created_at DESC"; // Default to newest
        }
        
        // Use integer casting for LIMIT and OFFSET to avoid SQL syntax errors
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.id = ? AND p.is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByIdIncludeInactive($id) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByCategoryId($categoryId, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.category_id = ? AND p.is_active = 1 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getFeatured() {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.is_active = 1 AND p.is_featured = 1 
                ORDER BY p.created_at DESC 
                LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getBestSellers($limit = 10) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.is_active = 1 AND p.is_best_seller = 1 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getNewArrivals($limit = 10) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.is_active = 1 AND p.is_new_arrival = 1 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getRelated($productId, $categoryId, $limit = 4) {
        $sql = "SELECT p.*, c.name_en as category_name, b.name as brand_name, pi.image_url as primary_image
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.id != ? AND p.category_id = ? AND p.is_active = 1 
                ORDER BY RAND() 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId, $categoryId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getProductsCount($filters = []) {
        $sql = "SELECT COUNT(*)
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";

        // Default to showing only active products unless specified
        if (empty($filters['include_inactive'])) {
            $sql .= " AND p.is_active = 1";
        }
        
        $params = [];
        
        // Add filters
        if (!empty($filters['category_id'])) {
            if (!empty($filters['include_subcategories'])) {
                // Include products from subcategories as well
                $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
                $params[] = $filters['category_id'];
                $params[] = $filters['category_id'];
            } else {
                $sql .= " AND p.category_id = ?";
                $params[] = $filters['category_id'];
            }
        }

        // Support for multiple category IDs
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $placeholders = str_repeat('?,', count($filters['category_ids']) - 1) . '?';
            $sql .= " AND p.category_id IN ($placeholders)";
            $params = array_merge($params, $filters['category_ids']);
        }
        
        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (p.name_en LIKE ? OR p.description_en LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }

        if (isset($filters['min_stock']) && $filters['min_stock'] !== '') {
            $sql .= " AND p.stock_quantity >= ?";
            $params[] = $filters['min_stock'];
        }

        if (isset($filters['max_stock']) && $filters['max_stock'] !== '') {
            $sql .= " AND p.stock_quantity <= ?";
            $params[] = $filters['max_stock'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function create($data, $attempt = 0) {
        $originalData=$data;
        try {
            $this->pdo->beginTransaction();
            $data = $this->validateProduct($data);
            if (empty($data['sku'])) {
                $category=$this->pdo->prepare('SELECT parent_id FROM categories WHERE id=?');
                $category->execute([$data['category_id']]); $parent=$category->fetchColumn();
                $data['sku']=$this->skuGenerator->generateSKU($parent ?: $data['category_id'], $parent ? $data['category_id'] : null);
            }

            $sql = "INSERT INTO products (
                        name_en, name_fr, description_en,
                        technical_specs_en, short_description_en,
                        category_id, brand_id, price, discount_percentage,
                        stock_quantity, sku, weight, dimensions,
                        is_active, is_featured, is_new_arrival, is_best_seller, video_url
                    ) VALUES (
                        ?, ?, ?,
                        ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?, ?
                    )";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['name_en'],
                $data['name_fr'] ?? $data['name_en'],
                $data['description_en'],
                json_encode($data['technical_specs_en'] ?? []),
                $data['short_description_en'] ?? '',
                $data['category_id'], $data['brand_id'], $data['price'], $data['discount_percentage'],
                $data['stock_quantity'], $data['sku'] ?? null,
                $data['weight'] ?? null, $data['dimensions'] ?? null,
                $data['is_active'] ?? 1, $data['is_featured'] ?? 0, $data['is_new_arrival'] ?? 0, $data['is_best_seller'] ?? 0,
                $data['video_url'] ?? null
            ]);

            $productId = $this->pdo->lastInsertId();
            $this->skuGenerator->claim($data['sku'], 'product', $productId);

            // Handle product images if provided
            if (!empty($data['images'])) {
                $this->addProductImages($productId, $data['images']);
            }

            // Handle product variants if provided
            if (!empty($data['variants'])) {
                $this->updateProductVariants($productId, $data['variants']);
            }

            $this->pdo->commit();
            return $productId;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollback();
            if (CatalogRules::retryable($e) && $attempt<2) return $this->create($originalData,$attempt+1);
            throw $e;
        }
    }
    
    public function update($id, $data, $attempt = 0) {
        $originalData=$data;
        try {
            $this->pdo->beginTransaction();
            $lock=$this->pdo->prepare('SELECT * FROM products WHERE id=? FOR UPDATE');
            $lock->execute([$id]); $existing=$lock->fetch();
            if (!$existing) throw new InvalidArgumentException('Product not found.');
            if (!array_key_exists('technical_specs_en',$data)) $existing['technical_specs_en']=json_decode($existing['technical_specs_en'] ?? '{}',true) ?: [];
            $data=$this->validateProduct(array_replace($existing,$data));
            if (empty($data['sku'])) {
                $category=$this->pdo->prepare('SELECT parent_id FROM categories WHERE id=?');
                $category->execute([$data['category_id']]); $parent=$category->fetchColumn();
                $data['sku']=$this->skuGenerator->generateSKU($parent ?: $data['category_id'], $parent ? $data['category_id'] : null);
            }
            $this->skuGenerator->claim($data['sku'],'product',$id);

            $sql = "UPDATE products SET
                        name_en = ?, description_en = ?,
                        technical_specs_en = ?, short_description_en = ?,
                        category_id = ?, brand_id = ?, price = ?, discount_percentage = ?,
                        stock_quantity = ?, sku = ?, weight = ?, dimensions = ?,
                        is_active = ?, is_featured = ?, is_new_arrival = ?, is_best_seller = ?,
                        video_url = ?, updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['name_en'],
                $data['description_en'],
                json_encode($data['technical_specs_en'] ?? []),
                $data['short_description_en'] ?? '',
                $data['category_id'], $data['brand_id'], $data['price'], $data['discount_percentage'],
                $data['stock_quantity'], $data['sku'] ?? null,
                $data['weight'] ?? null, $data['dimensions'] ?? null,
                $data['is_active'] ?? 1, $data['is_featured'] ?? 0, $data['is_new_arrival'] ?? 0, $data['is_best_seller'] ?? 0,
                $data['video_url'] ?? null,
                $id
            ]);

            // Handle product images (Full Replace)
            if (isset($data['images'])) {
                $this->updateProductImages($id, $data['images']);
            }

            // Handle NEW product images (Append)
            if (isset($data['new_images']) && !empty($data['new_images'])) {
                $this->addProductImages($id, $data['new_images']);
            }

            // Handle product variants if provided
            if (isset($data['variants'])) {
                $this->updateProductVariants($id, $data['variants']);
            }
            CatalogRules::syncStock($this->pdo,$id);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollback();
            if (CatalogRules::retryable($e) && $attempt<2) return $this->update($id,$originalData,$attempt+1);
            throw $e;
        }
    }
    
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            $references=$this->pdo->prepare('SELECT (SELECT COUNT(*) FROM product_variants WHERE product_id=?) + (SELECT COUNT(*) FROM order_items WHERE product_id=?)');
            $references->execute([$id,$id]);
            if ($references->fetchColumn()>0) throw new DomainException('This product has SKU or order history. Deactivate it instead of deleting it.');
            
            // 1. Get product images to delete files
            $images = $this->getProductImages($id);
            
            // 2. Get product video to delete file
            $sql = "SELECT video_url FROM products WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $videoUrl = $stmt->fetchColumn();
            
            // 3. Delete image files from filesystem
            foreach ($images as $image) {
                $filePath = __DIR__ . '/../' . $image['image_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 4. Delete video file from filesystem
            if ($videoUrl) {
                $filePath = __DIR__ . '/../' . $videoUrl;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 5. Delete product directory if empty or remove all files
            $productDir = __DIR__ . '/../uploads/medias/products/' . $id . '/';
            if (is_dir($productDir)) {
                // Remove any remaining files
                $files = glob($productDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Remove directory
                @rmdir($productDir);
            }
            
            // 6. Delete from database (CASCADE will handle product_images and product_variants)
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            $this->pdo->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Product deletion error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function addProductImages($productId, $images) {
        $sql = "INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($images as $index => $image) {
            $stmt->execute([
                $productId,
                $image['url'],
                $image['alt'] ?? '',
                $image['is_primary'] ?? 0,
                $index
            ]);
        }
    }
    
    private function updateProductImages($productId, $images) {
        // Remove existing images
        $sql = "DELETE FROM product_images WHERE product_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        
        // Add new images
        $this->addProductImages($productId, $images);
    }
    

    private function validateProduct(array $data) {
        if (trim($data['name_en'] ?? '') === '' || empty($data['category_id'])) throw new InvalidArgumentException('Product name and category are required.');
        $data['price']=CatalogRules::money($data['price'] ?? 0);
        $data['stock_quantity']=CatalogRules::quantity($data['stock_quantity'] ?? 0,true);
        $data['discount_percentage']=$data['discount_percentage'] ?? 0;
        CatalogRules::price($data['price'],$data['discount_percentage']);
        $data['sku']=strtoupper(trim($data['sku'] ?? ''));
        if ($data['sku']!=='' && !$this->skuGenerator->validateSKU($data['sku'])) throw new InvalidArgumentException('Invalid SKU format.');
        $data['description_en']=$data['description_en'] ?? '';
        $data['brand_id']=$data['brand_id'] ?? null;
        return $data;
    }

    private function updateProductVariants($productId, $variants) {
        $stmt=$this->pdo->prepare('SELECT * FROM product_variants WHERE product_id=? FOR UPDATE');
        $stmt->execute([$productId]); $existing=[];
        foreach($stmt->fetchAll() as $row) $existing[$row['id']]=$row;
        $parent=$this->pdo->prepare('SELECT sku FROM products WHERE id=?'); $parent->execute([$productId]); $parentSku=$parent->fetchColumn();
        $seenIds=[]; $combinations=[]; $optionNames=null;
        foreach($variants as $variant) {
            $id=empty($variant['id']) ? null : CatalogRules::quantity($variant['id']);
            if ($id && (!isset($existing[$id]) || isset($seenIds[$id]))) throw new InvalidArgumentException('Invalid or duplicate variant ID.');
            $attributes=[];
            foreach($variant['attributes'] ?? [] as $name=>$value) {
                $name=mb_strtolower(trim($name)); $value=trim($value);
                if ($name==='' || $value==='' || mb_strlen($name)>100 || mb_strlen($value)>191 || isset($attributes[$name])) throw new InvalidArgumentException('Invalid or duplicate variant attribute.');
                $attributes[$name]=$value;
            }
            if (!$attributes) throw new InvalidArgumentException('Each variant needs at least one option.');
            ksort($attributes); $keys=array_keys($attributes);
            if ($optionNames!==null && $keys!==$optionNames) throw new InvalidArgumentException('All variants must use the same option names.');
            $optionNames=$keys;
            $signature=json_encode(array_map('mb_strtolower',$attributes),JSON_UNESCAPED_UNICODE);
            if (isset($combinations[$signature])) throw new InvalidArgumentException('Duplicate variant combination.');
            $combinations[$signature]=true;
            $price=CatalogRules::money($variant['price']); $stock=CatalogRules::quantity($variant['stock_quantity'],true);
            $name=trim($variant['variant_name'] ?? '') ?: implode(' / ',$attributes);
            if (mb_strlen($name)>255) throw new InvalidArgumentException('Variant name is too long.');
            if ($id) {
                $sku=$existing[$id]['sku']; // Identity survives edits and reordering.
                $this->pdo->prepare('UPDATE product_variants SET variant_name=?,price=?,stock_quantity=?,is_active=1 WHERE id=?')->execute([$name,$price,$stock,$id]);
                $this->pdo->prepare('DELETE FROM variant_attributes WHERE product_variant_id=?')->execute([$id]);
            } else {
                $sku=trim($variant['sku'] ?? '') ?: $this->skuGenerator->generateVariantSKU($parentSku);
                $sku=strtoupper($sku);
                $this->pdo->prepare('INSERT INTO product_variants(product_id,sku,variant_name,price,stock_quantity) VALUES(?,?,?,?,?)')->execute([$productId,$sku,$name,$price,$stock]);
                $id=(int)$this->pdo->lastInsertId();
                $this->skuGenerator->claim($sku,'variant',$id);
            }
            $seenIds[$id]=true;
            foreach($attributes as $key=>$value) $this->pdo->prepare('INSERT INTO variant_attributes(product_variant_id,attribute_name,attribute_value) VALUES(?,?,?)')->execute([$id,$key,$value]);
        }
        foreach($existing as $id=>$row) if (!isset($seenIds[$id])) $this->pdo->prepare('UPDATE product_variants SET is_active=0 WHERE id=?')->execute([$id]);
        CatalogRules::syncStock($this->pdo,$productId);
    }
    
    public function getProductVariants($productId) {
        // Fetch all variants for the product
        $sql = "SELECT v.*, va.attribute_name, va.attribute_value 
                FROM product_variants v
                LEFT JOIN variant_attributes va ON v.id = va.product_variant_id
                WHERE v.product_id = ? AND v.is_active = 1
                ORDER BY v.id, va.attribute_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group attributes by variant
        $variants = [];
        foreach ($rows as $row) {
            $variantId = $row['id'];
            if (!isset($variants[$variantId])) {
                $variants[$variantId] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'sku' => $row['sku'],
                    'variant_name' => $row['variant_name'],
                    'price' => $row['price'],
                    'stock_quantity' => $row['stock_quantity'],
                    'weight' => $row['weight'],
                    'attributes' => []
                ];
            }
            if ($row['attribute_name']) {
                $variants[$variantId]['attributes'][$row['attribute_name']] = $row['attribute_value'];
            }
        }
        return array_values($variants);
    }

    public function getVariantById($variantId) {
        $sql = "SELECT v.*, va.attribute_name, va.attribute_value 
                FROM product_variants v
                LEFT JOIN variant_attributes va ON v.id = va.product_variant_id
                WHERE v.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$variantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return null;

        $variant = [
            'id' => $rows[0]['id'],
            'product_id' => $rows[0]['product_id'],
            'sku' => $rows[0]['sku'],
            'variant_name' => $rows[0]['variant_name'],
            'price' => $rows[0]['price'],
            'stock_quantity' => $rows[0]['stock_quantity'],
            'attributes' => []
        ];

        foreach ($rows as $row) {
            if ($row['attribute_name']) {
                $variant['attributes'][$row['attribute_name']] = $row['attribute_value'];
            }
        }
        return $variant;
    }
    
    public function getProductImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getAllWithStock() {
        $sql = "SELECT id, name_en, sku, stock_quantity FROM products ORDER BY stock_quantity ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStock($id, $quantity) {
        try {
            $quantity=CatalogRules::quantity($quantity,true);
            $this->pdo->beginTransaction();
            $lock=$this->pdo->prepare('SELECT id FROM products WHERE id=? FOR UPDATE'); $lock->execute([$id]);
            if (!$lock->fetchColumn() || CatalogRules::hasVariants($this->pdo,$id)) { $this->pdo->rollBack(); return false; }
            $stmt=$this->pdo->prepare('UPDATE products SET stock_quantity=?,updated_at=NOW() WHERE id=?');
            $stmt->execute([$quantity,$id]); $this->pdo->commit(); return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

    public function getFlashSales($limit = 12, $offset = 0) {
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT p.*, fs.discount_percentage, fs.start_date, fs.end_date,
                       (p.price * (1 - fs.discount_percentage / 100)) AS final_price
                FROM products p
                INNER JOIN flash_sales fs ON p.id = fs.product_id
                WHERE p.is_active = 1
                AND fs.is_active = 1
                AND fs.start_date <= ?
                AND fs.end_date >= ?
                ORDER BY fs.start_date ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$now, $now, $limit, $offset]);
        return $stmt->fetchAll();
    }
}
?>
