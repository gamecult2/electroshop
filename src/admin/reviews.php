<?php
// admin/reviews.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $reviewId = (int)$_GET['id'];
    $action = $_GET['action'];
    $is_approved = null;

    if ($action === 'approve') {
        $is_approved = 1;
    } elseif ($action === 'unapprove') {
        $is_approved = 0;
    }

    if (is_numeric($is_approved)) {
        $stmt = $pdo->prepare("UPDATE reviews SET is_approved = ? WHERE id = ?");
        if ($stmt->execute([$is_approved, $reviewId])) {
            $message = 'Review status updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update review status.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        if ($stmt->execute([$reviewId])) {
            $message = 'Review deleted successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete review.';
            $messageType = 'error';
        }
    }
}

// Fetch all reviews with product and user info
$stmt = $pdo->query("
    SELECT r.id, r.rating, r.title, r.review_text, r.is_approved, r.created_at, p.name_en AS product_name, CONCAT(c.first_name, ' ', c.last_name) AS username  
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    JOIN customers c ON r.customer_id = c.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Reviews';
$page_heading = 'Product Reviews';

// Include the shared header template
include 'header.php';

?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-comment-dots me-2 text-primary"></i> Customer Feedback</h5>
                    <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                        <?php echo count($reviews); ?> Total
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 250px;">Product</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Customer</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Rating</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Content</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Date</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Status</th>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted fst-italic">No reviews found in the database.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td class="px-4">
                                                <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($review['product_name']); ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light text-muted p-2 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <i class="fas fa-user-circle x-small"></i>
                                                    </div>
                                                    <div class="fw-bold text-dark small"><?php echo htmlspecialchars($review['username']); ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="text-warning x-small text-nowrap">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i class="fa<?php echo $i < $review['rating'] ? 's' : 'r'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="x-small fw-bold text-muted"><?php echo $review['rating']; ?>/5</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($review['title']); ?></div>
                                                <div class="text-muted small italic" style="max-width: 300px; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="x-small fw-bold text-dark"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></div>
                                                <div class="x-small text-muted italic"><?php echo date('H:i', strtotime($review['created_at'])); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $review['is_approved'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> rounded-pill px-3 py-1 fw-bold x-small text-uppercase">
                                                    <?php echo $review['is_approved'] ? 'Approved' : 'Pending'; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <?php if (!$review['is_approved']): ?>
                                                        <a href="reviews.php?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-success" title="Approve Review">
                                                            <i class="fas fa-check me-1"></i> Approve
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="reviews.php?action=unapprove&id=<?php echo $review['id']; ?>" class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-warning" title="Unapprove Review">
                                                            <i class="fas fa-times me-1"></i> Unapprove
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-danger" onclick="return confirm('Are you sure you want to delete this review?');" title="Delete Review">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>



