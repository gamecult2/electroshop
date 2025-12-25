<?php
// helpers/MediaManager.php
// Advanced Product Media Management System

require_once __DIR__ . '/../db_connect.php';

class MediaManager {
    private $pdo;
    private $uploadBasePath = __DIR__ . '/../uploads/medias/products/';
    private $maxImageSize = 5242880; // 5MB
    private $maxVideoSize = 52428800; // 50MB
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private $allowedVideoTypes = ['mp4', 'webm', 'ogv'];
    
    public function __construct($database = null) {
        $this->pdo = $database ? $database->getConnection() : $GLOBALS['pdo'];
    }
    
    /**
     * Get product media directory path
     * Format: uploads/medias/products/[product_id]/
     */
    public function getProductMediaDir($productId, $createIfNotExists = true) {
        $dir = $this->uploadBasePath . $productId . '/';
        
        if ($createIfNotExists && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return $dir;
    }
    
    /**
     * Get relative path for database storage
     */
    public function getRelativePath($productId, $filename) {
        return 'uploads/medias/products/' . $productId . '/' . $filename;
    }
    
    /**
     * Upload mixed media (images and videos) for a product
     */
    public function uploadMedia($productId, $files, $setPrimaryIfFirst = true) {
        $uploadedImages = [];
        $uploadedVideos = [];
        $errors = [];
        
        // Check if product exists
        if (!$this->productExists($productId)) {
            throw new Exception("Product not found");
        }
        
        // Get product directory
        $uploadDir = $this->getProductMediaDir($productId, true);
        
        // Check if this is the first image (for primary setting)
        $existingImages = $this->getProductImages($productId);
        $isFirstImage = empty($existingImages);
        $firstImageProcessed = false;
        
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                try {
                    $tmpName = $files['tmp_name'][$index];
                    $fileSize = $files['size'][$index];
                    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    // Determine if it's an image or video
                    if (in_array($extension, $this->allowedImageTypes)) {
                        // Process as image
                        if ($fileSize > $this->maxImageSize) {
                            $errors[] = "Image $name exceeds maximum size of " . ($this->maxImageSize / 1048576) . "MB";
                            continue;
                        }
                        
                        if (!$this->isValidImage($tmpName)) {
                            $errors[] = "File $name is not a valid image";
                            continue;
                        }
                        
                        $newName = uniqid('img_' . time() . '_') . '.' . $extension;
                        $destination = $uploadDir . $newName;
                        
                        if ($this->optimizeAndMoveImage($tmpName, $destination, $extension)) {
                            $relativePath = $this->getRelativePath($productId, $newName);
                            $isPrimary = ($setPrimaryIfFirst && $isFirstImage && !$firstImageProcessed) ? 1 : 0;
                            
                            $imageId = $this->addImageToDatabase($productId, $relativePath, $fileSize, $isPrimary);
                            
                            $uploadedImages[] = [
                                'id' => $imageId,
                                'url' => $relativePath,
                                'is_primary' => $isPrimary
                            ];
                            
                            $firstImageProcessed = true;
                            $this->logMediaUpload($productId, $newName, $relativePath, $fileSize, 'image', 'success');
                        } else {
                            $errors[] = "Failed to upload image $name";
                        }
                        
                    } elseif (in_array($extension, $this->allowedVideoTypes)) {
                        // Process as video
                        if ($fileSize > $this->maxVideoSize) {
                            $errors[] = "Video $name exceeds maximum size of " . ($this->maxVideoSize / 1048576) . "MB";
                            continue;
                        }
                        
                        // Check if a video already exists for this product
                        $sql = "SELECT video_url FROM products WHERE id = ?";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$productId]);
                        $existingVideo = $stmt->fetchColumn();
                        
                        if ($existingVideo && !empty($uploadedVideos)) {
                            $errors[] = "Only one video per product is allowed. Skipping $name";
                            continue;
                        }
                        
                        $newName = uniqid('vid_' . time() . '_') . '.' . $extension;
                        $destination = $uploadDir . $newName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $relativePath = $this->getRelativePath($productId, $newName);
                            
                            // Update product video_url
                            $sql = "UPDATE products SET video_url = ?, updated_at = NOW() WHERE id = ?";
                            $stmt = $this->pdo->prepare($sql);
                            $stmt->execute([$relativePath, $productId]);
                            
                            $uploadedVideos[] = [
                                'url' => $relativePath
                            ];
                            
                            $this->logMediaUpload($productId, $newName, $relativePath, $fileSize, 'video', 'success');
                        } else {
                            $errors[] = "Failed to upload video $name";
                        }
                        
                    } else {
                        $errors[] = "Invalid file type for $name. Allowed: " . 
                                   implode(', ', array_merge($this->allowedImageTypes, $this->allowedVideoTypes));
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "Error uploading $name: " . $e->getMessage();
                }
            } elseif ($files['error'][$index] !== UPLOAD_ERR_NO_FILE) {
                $errors[] = "Upload error for $name: " . $this->getUploadErrorMessage($files['error'][$index]);
            }
        }
        
        return [
            'success' => [
                'images' => $uploadedImages,
                'videos' => $uploadedVideos
            ],
            'errors' => $errors
        ];
    }

    /**
     * Upload multiple images for a product
     */
    public function uploadImages($productId, $files, $setPrimaryIfFirst = true) {
        $uploadedImages = [];
        $errors = [];
        
        // Check if product exists
        if (!$this->productExists($productId)) {
            throw new Exception("Product not found");
        }
        
        // Get product directory
        $uploadDir = $this->getProductMediaDir($productId, true);
        
        // Check if this is the first image (for primary setting)
        $existingImages = $this->getProductImages($productId);
        $isFirstImage = empty($existingImages);
        
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                try {
                    $tmpName = $files['tmp_name'][$index];
                    $fileSize = $files['size'][$index];
                    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    // Validate extension
                    if (!in_array($extension, $this->allowedImageTypes)) {
                        $errors[] = "Invalid file type for $name. Allowed: " . implode(', ', $this->allowedImageTypes);
                        continue;
                    }
                    
                    // Validate size
                    if ($fileSize > $this->maxImageSize) {
                        $errors[] = "File $name exceeds maximum size of " . ($this->maxImageSize / 1048576) . "MB";
                        continue;
                    }
                    
                    // Validate image
                    if (!$this->isValidImage($tmpName)) {
                        $errors[] = "File $name is not a valid image";
                        continue;
                    }
                    
                    // Generate unique filename
                    $newName = uniqid('img_' . time() . '_') . '.' . $extension;
                    $destination = $uploadDir . $newName;
                    
                    // Optimize and move image
                    if ($this->optimizeAndMoveImage($tmpName, $destination, $extension)) {
                        $relativePath = $this->getRelativePath($productId, $newName);
                        $isPrimary = ($setPrimaryIfFirst && $isFirstImage && $index === 0) ? 1 : 0;
                        
                        // Add to database
                        $imageId = $this->addImageToDatabase($productId, $relativePath, $fileSize, $isPrimary);
                        
                        $uploadedImages[] = [
                            'id' => $imageId,
                            'url' => $relativePath,
                            'is_primary' => $isPrimary
                        ];
                        
                        // Log success
                        $this->logMediaUpload($productId, $newName, $relativePath, $fileSize, 'image', 'success');
                        
                    } else {
                        $errors[] = "Failed to upload $name";
                        $this->logMediaUpload($productId, $name, '', $fileSize, 'image', 'failed', 'Upload failed');
                    }
                    
                } catch (Exception $e) {
                    $errors[] = "Error uploading $name: " . $e->getMessage();
                    $this->logMediaUpload($productId, $name, '', 0, 'image', 'failed', $e->getMessage());
                }
            } elseif ($files['error'][$index] !== UPLOAD_ERR_NO_FILE) {
                $errors[] = "Upload error for $name: " . $this->getUploadErrorMessage($files['error'][$index]);
            }
        }
        
        return [
            'success' => $uploadedImages,
            'errors' => $errors
        ];
    }
    
    /**
     * Upload a video for a product
     */
    public function uploadVideo($productId, $file) {
        // Check if product exists
        if (!$this->productExists($productId)) {
            throw new Exception("Product not found");
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($file['error']));
        }
        
        $tmpName = $file['tmp_name'];
        $name = $file['name'];
        $fileSize = $file['size'];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        // Validate extension
        if (!in_array($extension, $this->allowedVideoTypes)) {
            throw new Exception("Invalid video type. Allowed: " . implode(', ', $this->allowedVideoTypes));
        }
        
        // Validate size
        if ($fileSize > $this->maxVideoSize) {
            throw new Exception("Video exceeds maximum size of " . ($this->maxVideoSize / 1048576) . "MB");
        }
        
        // Get product directory
        $uploadDir = $this->getProductMediaDir($productId, true);
        
        // Generate unique filename
        $newName = uniqid('vid_' . time() . '_') . '.' . $extension;
        $destination = $uploadDir . $newName;
        
        // Move video
        if (move_uploaded_file($tmpName, $destination)) {
            $relativePath = $this->getRelativePath($productId, $newName);
            
            // Update product video_url
            $sql = "UPDATE products SET video_url = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$relativePath, $productId]);
            
            // Log success
            $this->logMediaUpload($productId, $newName, $relativePath, $fileSize, 'video', 'success');
            
            return $relativePath;
        } else {
            $this->logMediaUpload($productId, $name, '', $fileSize, 'video', 'failed', 'Move failed');
            throw new Exception("Failed to upload video");
        }
    }
    
    /**
     * Delete an image
     */
    public function deleteImage($imageId) {
        // Get image info
        $sql = "SELECT * FROM product_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if (!$image) {
            throw new Exception("Image not found");
        }
        
        // Delete file
        $filePath = __DIR__ . '/../' . $image['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        $sql = "DELETE FROM product_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$imageId]);
    }
    
    /**
     * Set primary image
     */
    public function setPrimaryImage($imageId) {
        // Get image info
        $sql = "SELECT product_id FROM product_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$imageId]);
        $productId = $stmt->fetchColumn();
        
        if (!$productId) {
            throw new Exception("Image not found");
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Remove primary from all images of this product
            $sql = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$productId]);
            
            // Set new primary
            $sql = "UPDATE product_images SET is_primary = 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$imageId]);

            // ALSO: Sync sort order. Prepend this image to the list.
            $sql = "SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$productId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $ids = array_values(array_diff($ids, [$imageId]));
            array_unshift($ids, $imageId);
            
            $sql = "UPDATE product_images SET sort_order = ? WHERE id = ?";
            $updateStmt = $this->pdo->prepare($sql);
            foreach ($ids as $index => $id) {
                $updateStmt->execute([$index, $id]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete a video for a product
     */
    public function deleteVideo($productId) {
        // Get product video info
        $sql = "SELECT video_url FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        $videoUrl = $stmt->fetchColumn();
        
        if (!$videoUrl) {
            throw new Exception("No video found for this product");
        }
        
        // Delete file
        $filePath = __DIR__ . '/../' . $videoUrl;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Update database to remove video URL
        $sql = "UPDATE products SET video_url = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$productId]);
    }
    
    /**
     * Reorder images
     */
    public function reorderImages($imageIds) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE product_images SET sort_order = ?, is_primary = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($imageIds as $order => $imageId) {
                $isPrimary = ($order === 0) ? 1 : 0;
                $stmt->execute([$order, $isPrimary, $imageId]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    /**
     * Get all images for a product
     */
    public function getProductImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Validate if file is a valid image
     */
    private function isValidImage($filePath) {
        $imageInfo = @getimagesize($filePath);
        return $imageInfo !== false;
    }
    
    /**
     * Optimize and move image
     */
    private function optimizeAndMoveImage($source, $destination, $extension) {
        // For now, just move the file
        // TODO: Add image optimization (resize, compress) using GD or ImageMagick
        return move_uploaded_file($source, $destination);
    }
    
    /**
     * Add image to database
     */
    private function addImageToDatabase($productId, $url, $fileSize, $isPrimary = 0) {
        $sql = "INSERT INTO product_images (product_id, image_url, is_primary, sort_order) 
                VALUES (?, ?, ?, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images pi WHERE pi.product_id = ?))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId, $url, $isPrimary, $productId]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Check if product exists
     */
    private function productExists($productId) {
        $sql = "SELECT COUNT(*) FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Log media upload
     */
    private function logMediaUpload($productId, $fileName, $filePath, $fileSize, $mediaType, $status, $errorMessage = null) {
        $sql = "INSERT INTO media_upload_log (product_id, file_name, file_path, file_size, media_type, upload_status, error_message) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId, $fileName, $filePath, $fileSize, $mediaType, $status, $errorMessage]);
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
}
?>
