<?php
// api/reviews.php - Reviews API endpoints

header('Content-Type: application/json');
require_once '../includes/init.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get product reviews
        $productId = $_GET['product_id'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        $offset = ($page - 1) * $limit;
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId, $limit, $offset]);
        $reviews = $stmt->fetchAll();
        
        // Get average rating
        $ratingSql = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews
                      FROM reviews
                      WHERE product_id = ? AND is_approved = 1";
        $ratingStmt = $pdo->prepare($ratingSql);
        $ratingStmt->execute([$productId]);
        $ratingData = $ratingStmt->fetch();
        
        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => (float)$ratingData['average_rating'],
            'total_reviews' => $ratingData['total_reviews']
        ]);
        break;
    
    case 'POST':
        // Submit a review
        if (!is_logged_in()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        $rating = (int)($input['rating'] ?? 0);
        $title = sanitize_input($input['title'] ?? '');
        $reviewText = sanitize_input($input['review_text'] ?? '');
        
        if (!$productId || $rating < 1 || $rating > 5 || empty($reviewText)) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID, rating (1-5), and review text are required']);
            exit;
        }
        
        $userId = get_current_user_id();
        
        // Check if user purchased this product
        $purchaseSql = "SELECT COUNT(*) 
                        FROM orders o 
                        JOIN order_items oi ON o.id = oi.order_id 
                        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'";
        $purchaseStmt = $pdo->prepare($purchaseSql);
        $purchaseStmt->execute([$userId, $productId]);
        $isVerifiedPurchase = $purchaseStmt->fetchColumn() > 0;
        
        // Insert review
        $sql = "INSERT INTO reviews (product_id, user_id, rating, title, review_text, is_verified_purchase, is_approved, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$productId, $userId, $rating, $title, $reviewText, $isVerifiedPurchase]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => t('review_submitted_for_approval')]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to submit review']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
