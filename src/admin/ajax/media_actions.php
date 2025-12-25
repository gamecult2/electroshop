<?php
// admin/ajax/media_actions.php
// AJAX endpoints for product media management

session_start();
require_once '../../db_connect.php';
require_once '../../helpers/MediaManager.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$mediaManager = new MediaManager();

try {
    switch ($action) {
        case 'delete_image':
            $imageId = (int)($_POST['image_id'] ?? 0);
            if (!$imageId) {
                throw new Exception('Image ID is required');
            }
            
            $result = $mediaManager->deleteImage($imageId);
            echo json_encode([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
            break;
            
        case 'set_primary':
            $imageId = (int)($_POST['image_id'] ?? 0);
            if (!$imageId) {
                throw new Exception('Image ID is required');
            }
            
            $result = $mediaManager->setPrimaryImage($imageId);
            echo json_encode([
                'success' => true,
                'message' => 'Primary image updated successfully'
            ]);
            break;
            
        case 'reorder_images':
            $imageIds = json_decode($_POST['image_ids'] ?? '[]', true);
            if (empty($imageIds) || !is_array($imageIds)) {
                throw new Exception('Image IDs array is required');
            }
            
            $result = $mediaManager->reorderImages($imageIds);
            echo json_encode([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);
            break;
            
        case 'get_images':
            $productId = (int)($_GET['product_id'] ?? 0);
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            
            $images = $mediaManager->getProductImages($productId);
            echo json_encode([
                'success' => true,
                'images' => $images
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
