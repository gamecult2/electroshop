<?php
// api/reviews.php - Reviews API endpoints

header('Content-Type: application/json');
require_once '../includes/init.php';
require_once '../models/Review.php';

$method = $_SERVER['REQUEST_METHOD'];
$reviewModel = new Review();

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
        
        $reviews = $reviewModel->getForProduct($productId, $limit, $offset);
        $ratingData = $reviewModel->getAverageRating($productId);
        
        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => (float)($ratingData['average_rating'] ?? 0),
            'total_reviews' => (int)($ratingData['total_reviews'] ?? 0)
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
        $title = $input['title'] ?? '';
        $reviewText = $input['review_text'] ?? '';
        
        if (!$productId || $rating < 1 || $rating > 5 || empty($reviewText)) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID, rating (1-5), and review text are required']);
            exit;
        }
        
        $customerId = get_current_user_id();
        
        // Check if user already reviewed this product
        $existing = $reviewModel->getUserReview($customerId, $productId);
        if ($existing) {
            http_response_code(400);
            echo json_encode(['error' => 'You have already reviewed this product']);
            exit;
        }

        // Check if user purchased this product
        $isVerifiedPurchase = $reviewModel->hasPurchasedProduct($customerId, $productId);
        
        // Use the Review model to add
        try {
            $reviewId = $reviewModel->addReview([
                'product_id' => $productId,
                'customer_id' => $customerId,
                'rating' => $rating,
                'title' => $title,
                'review_text' => $reviewText,
                'is_verified_purchase' => $isVerifiedPurchase ? 1 : 0,
                'is_approved' => 0 // Needs admin approval by default
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Your review has been submitted and is awaiting approval.',
                'review_id' => $reviewId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to submit review: ' . $e->getMessage()]);
        }
        break;
    
    case 'PUT':
        // Edit an existing review
        if (!is_logged_in()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $reviewId = $input['review_id'] ?? null;
        $rating = (int)($input['rating'] ?? 0);
        $reviewText = $input['review_text'] ?? '';

        if (!$reviewId || $rating < 1 || $rating > 5 || empty($reviewText)) {
            http_response_code(400);
            echo json_encode(['error' => 'Review ID, rating, and text are required']);
            exit;
        }

        $customerId = get_current_user_id();

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE id = ? AND customer_id = ?");
        $stmt->execute([$reviewId, $customerId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized to edit this review']);
            exit;
        }

        try {
            // Update and reset approval status
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, review_text = ?, is_approved = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$rating, $reviewText, $reviewId]);

            echo json_encode([
                'success' => true,
                'message' => 'Your review has been updated and is awaiting re-approval.'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update review: ' . $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>