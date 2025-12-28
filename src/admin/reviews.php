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

// Handle Admin Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_reply'])) {
    $reviewId = (int)$_POST['review_id'];
    $replyText = sanitize_input($_POST['reply_text']);

    $stmt = $pdo->prepare("UPDATE reviews SET reply_text = ?, replied_at = NOW() WHERE id = ?");
    if ($stmt->execute([$replyText, $reviewId])) {
        $message = 'Reply saved successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to save reply.';
        $messageType = 'error';
    }
}

// Fetch all reviews with product and user info
$stmt = $pdo->query("
    SELECT r.id, r.product_id, r.customer_id, r.rating, r.title, r.review_text, r.reply_text, r.replied_at, r.is_approved, r.created_at, p.name_en AS product_name, CONCAT(c.first_name, ' ', c.last_name) AS username  
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    JOIN customers c ON r.customer_id = c.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Reviews';
$page_heading = 'Product Reviews';

include 'header.php';
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <div><?php echo htmlspecialchars($message); ?></div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-comment-dots me-2 text-primary"></i> Customer Feedback</h5>
            <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                <?php echo count($reviews); ?> Total Reviews
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 200px;">Product</th>
                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase" style="width: 150px;">Customer</th>
                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center" style="width: 100px;">Rating</th>
                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Review Content</th>
                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center" style="width: 120px;">Status</th>
                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted fst-italic">No reviews found in the database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark small text-wrap" style="max-width: 180px;">
                                            <a href="product_details.php?id=<?php echo $review['product_id']; ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($review['product_name']); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.65rem;"><?php echo date('M j, Y H:i', strtotime($review['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">
                                            <a href="customer_details.php?id=<?php echo $review['customer_id']; ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($review['username']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-warning small text-nowrap mb-1">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="fa<?php echo $i < $review['rating'] ? 's' : 'r'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="fw-bold text-muted" style="font-size: 0.7rem;"><?php echo $review['rating']; ?>/5</span>
                                    </td>
                                    <td>
                                        <div class="p-2 rounded bg-light border border-light-subtle mb-2" style="max-height: 120px; overflow-y: auto;">
                                            <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($review['title'] ?: 'Review'); ?></div>
                                            <div class="text-muted small" style="line-height: 1.4;">
                                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                            </div>
                                        </div>
                                        <?php if ($review['reply_text']): ?>
                                            <div class="ms-3 p-2 border-start border-3 border-primary bg-primary-subtle bg-opacity-10 rounded-end">
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <i class="fas fa-reply fa-flip-horizontal" style="font-size: 0.6rem; color: #0d6efd;"></i>
                                                    <span class="fw-bold text-primary text-uppercase" style="font-size: 0.6rem;">Admin Response</span>
                                                    <span class="ms-auto text-muted" style="font-size: 0.6rem;"><?php echo date('M j', strtotime($review['replied_at'])); ?></span>
                                                </div>
                                                <div class="small text-dark fst-italic" style="font-size: 0.75rem;"><?php echo nl2br(htmlspecialchars($review['reply_text'])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $review['is_approved'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> rounded-pill px-3 py-1 fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                            <?php echo $review['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="btn-group shadow-none border rounded-pill overflow-hidden bg-white">
                                            <button type="button" onclick='openReplyModal(<?php echo json_encode($review); ?>)' class="btn btn-white btn-sm px-3 border-0 text-primary" title="Reply">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <?php if (!$review['is_approved']): ?>
                                                <a href="reviews.php?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-white btn-sm px-3 border-start border-end text-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="reviews.php?action=unapprove&id=<?php echo $review['id']; ?>" class="btn btn-white btn-sm px-3 border-start border-end text-warning" title="Unapprove">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" class="btn btn-white btn-sm px-3 border-0 text-danger" onclick="return confirm('Delete this review?');" title="Delete">
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

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-reply me-2 text-primary"></i>Reply to Review</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="reviews.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="review_id" id="replyReviewId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Customer Review</label>
                        <div id="replyReviewText" class="p-3 bg-light rounded-3 small fst-italic border border-light-subtle" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>
                    <div class="mb-0">
                        <label for="reply_text" class="form-label small fw-bold text-muted text-uppercase">Your Response</label>
                        <textarea class="form-control border-light-subtle shadow-none rounded-3" name="reply_text" id="replyInput" rows="5" placeholder="Thank the customer or address their concerns..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold small border shadow-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_reply" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Save Response</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let replyModal;
document.addEventListener('DOMContentLoaded', function() {
    replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
});

function openReplyModal(review) {
    document.getElementById('replyReviewId').value = review.id;
    document.getElementById('replyReviewText').innerHTML = `<strong>${review.title || 'Review'}:</strong><br>${review.review_text}`;
    document.getElementById('replyInput').value = review.reply_text || '';
    replyModal.show();
}
</script>

<?php include 'footer.php'; ?>