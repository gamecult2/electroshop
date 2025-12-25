<?php
// admin/messages.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// This feature requires a 'contact_messages' table
// CREATE TABLE contact_messages (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(255) NOT NULL,
//     email VARCHAR(255) NOT NULL,
//     subject VARCHAR(255) NOT NULL,
//     message TEXT NOT NULL,
//     is_read TINYINT(1) DEFAULT 0,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

if (isset($_GET['action']) && isset($_GET['id'])) {
    $messageId = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$messageId]);
    } elseif ($action === 'mark_unread') {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
        $stmt->execute([$messageId]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $_SESSION['message'] = 'Message deleted.';
        $_SESSION['message_type'] = 'success';
        header('Location: messages.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$contact_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title and heading variables for the template
$page_title = 'Manage Messages';
$page_heading = 'Manage Messages';

// Include the shared header template
include 'header.php';

?>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($_SESSION['message_type'] === 'error') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($_SESSION['message']); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-envelope-open-text me-2 text-primary"></i> Customer Messages</h5>
                    <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                        <?php echo count($contact_messages); ?> Total
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($contact_messages)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                <p class="mb-0 small">No messages found in your inbox.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($contact_messages as $msg): ?>
                                <div class="list-group-item list-group-item-action py-3 px-4 border-light-subtle position-relative <?php echo !$msg['is_read'] ? 'bg-light-subtle' : ''; ?>" id="msg-row-<?php echo $msg['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="badge bg-primary rounded-circle p-1" style="width: 8px; height: 8px;" title="Unread"></span>
                                            <?php endif; ?>
                                            <h6 class="mb-0 fw-bold <?php echo !$msg['is_read'] ? 'text-dark' : 'text-muted'; ?> small">
                                                <?php echo htmlspecialchars($msg['name']); ?>
                                            </h6>
                                            <span class="text-muted x-small">&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</span>
                                        </div>
                                        <span class="text-muted x-small fw-medium"><i class="far fa-clock me-1"></i><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 clickable pe-4" onclick="toggleMessage(<?php echo $msg['id']; ?>)" style="cursor: pointer;">
                                            <div class="fw-bold <?php echo !$msg['is_read'] ? 'text-primary' : 'text-secondary'; ?> small mb-1"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="text-muted x-small text-truncate" style="max-width: 90%;" id="preview-<?php echo $msg['id']; ?>">
                                                <?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-primary" onclick="toggleMessage(<?php echo $msg['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i> View
                                            </button>
                                            <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-danger" onclick="return confirm('Delete this message?');">
                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="collapse mt-3" id="collapse-<?php echo $msg['id']; ?>">
                                        <div class="bg-light p-4 rounded-3 border border-light-subtle small text-dark shadow-xs mb-2">
                                            <div class="mb-3 pb-2 border-bottom border-light-subtle fw-bold text-muted text-uppercase x-small" style="letter-spacing: 1px;"><i class="fas fa-align-left me-1"></i> Message Body</div>
                                            <p class="mb-0 lh-lg font-monospace text-secondary"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                            <div class="mt-4 pt-3 border-top border-light-subtle text-end">
                                                <a href="mailto:<?php echo $msg['email']; ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow">
                                                    <i class="fas fa-reply me-2"></i> Reply to Customer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
function toggleMessage(id) {
    const collapseEl = document.getElementById('collapse-' + id);
    const row = document.getElementById('msg-row-' + id);
    const badge = row.querySelector('.badge.bg-primary.rounded-circle');
    const header = row.querySelector('h6');
    const preview = document.getElementById('preview-' + id);
    
    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
    bsCollapse.toggle();

    if (row.classList.contains('bg-light-subtle')) {
        // Mark as read via AJAX
        fetch('messages.php?action=mark_read&id=' + id)
            .then(() => {
                row.classList.remove('bg-light-subtle');
                if (badge) badge.remove();
                if (header) header.classList.replace('text-dark', 'text-muted');
            });
    }
}
</script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


