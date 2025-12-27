<?php
// src/admin/ajax/upload_editor_image.php
session_start();
require_once '../../includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (isset($_FILES['file']['name'])) {
    if (!$_FILES['file']['error']) {
        $slug = isset($_POST['slug']) ? create_slug($_POST['slug']) : 'editor-image';
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $uniqueId = substr(md5(uniqid()), 0, 8);
        $filename = $slug . '-content-' . $uniqueId . '.' . $ext;
        
        // Determine upload directory
        // Default to editor folder
        $uploadBase = '../../uploads/medias/';
        $targetFolder = 'editor/'; // Default folder
        
        // If product_id is provided, save to product folder
        if (isset($_POST['product_id']) && is_numeric($_POST['product_id']) && $_POST['product_id'] > 0) {
            $productId = (int)$_POST['product_id'];
            $targetFolder = 'products/' . $productId . '/';
        }
        
        $uploadDir = $uploadBase . $targetFolder;
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $filename;
        $location = $_FILES["file"]["tmp_name"];
        
        if (move_uploaded_file($location, $destination)) {
            // Return path relative to src/
            // $uploadDir is "../../uploads/..."
            // We need to return "uploads/medias/..."
            
            echo 'uploads/medias/' . $targetFolder . $filename;
        } else {
            echo json_encode(['error' => 'Failed to move uploaded file.']);
        }
    } else {
        echo json_encode(['error' => 'File upload error: ' . $_FILES['file']['error']]);
    }
} else {
    echo json_encode(['error' => 'No file uploaded.']);
}
