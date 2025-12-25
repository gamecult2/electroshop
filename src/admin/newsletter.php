<?php
// admin/newsletter.php - Newsletter management page

session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['admin_role'] !== 'admin' && $_SESSION['admin_role'] !== 'manager') {
    die('Unauthorized: Only administrators and managers can manage newsletters.');
}

$page = (int)($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

$subscribers = $pdo->prepare("
    SELECT * FROM newsletter_subscribers 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$subscribers->execute([$limit, $offset]);
$subscribersList = $subscribers->fetchAll();

$totalSubscribers = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$totalPages = ceil($totalSubscribers / $limit);

// Handle sending newsletter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
    $subject = sanitize_input($_POST['subject']);
    $content = $_POST['content'];
    $sendTo = $_POST['send_to'] ?? 'all';
    
    if (empty($subject) || empty($content)) {
        set_message('Subject and content are required', 'error');
    } else {
        // Get recipients
        $recipientSql = "SELECT email FROM newsletter_subscribers WHERE is_confirmed = 1";
        if ($sendTo === 'confirmed_only') {
            $recipientSql .= " AND unsubscribed_at IS NULL";
        }
        $recipientStmt = $pdo->query($recipientSql);
        $recipients = $recipientStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Actually sending emails would happen here in a real app
        // For demo, we'll just record the campaign
        
        $insertCampaignSql = "INSERT INTO newsletter_campaigns (subject, content, total_recipients) VALUES (?, ?, ?)";
        $insertCampaignStmt = $pdo->prepare($insertCampaignSql);
        $insertResult = $insertCampaignStmt->execute([$subject, $content, count($recipients)]);
        
        if ($insertResult) {
            set_message('Newsletter campaign created successfully (emails would be sent in real implementation)', 'success');
        } else {
            set_message('Failed to create newsletter campaign', 'error');
        }
    }
}
// Set page title and heading variables for the template
$page_title = 'Newsletter Management';
$page_heading = 'Newsletter Management';

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

            <div class="row g-4">
                <!-- Compose Newsletter -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i> Compose Newsletter</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="compose_subject" class="form-label small fw-bold text-muted text-uppercase">Email Subject</label>
                                    <input type="text" id="compose_subject" name="subject" class="form-control border-light-subtle shadow-none py-2" placeholder="e.g., Summer Sale is Here!" required>
                                </div>
                                <div class="mb-3">
                                    <label for="compose_content" class="form-label small fw-bold text-muted text-uppercase">Message Content</label>
                                    <textarea id="compose_content" name="content" class="form-control border-light-subtle shadow-none py-2" rows="8" placeholder="Write your newsletter message here..." required></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="compose_send_to" class="form-label small fw-bold text-muted text-uppercase">Recipients</label>
                                    <select id="compose_send_to" name="send_to" class="form-select border-light-subtle shadow-none py-2">
                                        <option value="all">All Subscribers</option>
                                        <option value="confirmed_only">Only Confirmed Subscribers</option>
                                    </select>
                                </div>
                                <button type="submit" name="send_newsletter" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> Launch Campaign
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Subscriber List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i> Newsletter Subscribers</h5>
                            <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                                <?php echo $totalSubscribers; ?> Subscribers
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase">Email Address</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Status</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Dates</th>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($subscribersList)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-info-circle me-1"></i> No subscribers found.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($subscribersList as $subscriber): ?>
                                                <tr>
                                                    <td class="px-4">
                                                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars($subscriber['email']); ?></div>
                                                        <div class="text-muted x-small italic mt-1">Joined: <?php echo date('M d, Y', strtotime($subscriber['created_at'])); ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($subscriber['is_confirmed'] && !$subscriber['unsubscribed_at']): ?>
                                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold x-small text-uppercase">Active</span>
                                                        <?php elseif (!$subscriber['is_confirmed']): ?>
                                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 fw-bold x-small text-uppercase">Pending</span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($subscriber['unsubscribed_at']): ?>
                                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold x-small text-uppercase">Unsubscribed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="small text-muted x-small">
                                                            <?php if ($subscriber['confirmed_at']): ?>
                                                                <div><i class="fas fa-check-circle me-1 text-success"></i> Confirmed: <?php echo date('M d, Y', strtotime($subscriber['confirmed_at'])); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($subscriber['unsubscribed_at']): ?>
                                                                <div><i class="fas fa-times-circle me-1 text-danger"></i> Unsub: <?php echo date('M j, Y', strtotime($subscriber['unsubscribed_at'])); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <?php if (!$subscriber['is_confirmed']): ?>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="confirm_subscriber" value="<?php echo $subscriber['id']; ?>">
                                                                    <button type="submit" class="btn btn-light btn-xs rounded-pill px-3 border border-light-subtle shadow-xs text-success fw-bold">
                                                                        <i class="fas fa-check me-1"></i> Confirm
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove subscriber?');">
                                                                <input type="hidden" name="remove_subscriber" value="<?php echo $subscriber['id']; ?>">
                                                                <button type="submit" class="btn btn-light btn-xs rounded-pill px-3 border border-light-subtle shadow-xs text-danger fw-bold">
                                                                    <i class="fas fa-user-minus me-1"></i> Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="card-footer bg-white py-3 border-0">
                                    <nav aria-label="Subscriber navigation">
                                        <ul class="pagination pagination-sm justify-content-center mb-0">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link border-light-subtle rounded-start-pill px-3 shadow-none" href="?page=<?php echo $page - 1; ?>">
                                                    <i class="fas fa-chevron-left x-small"></i>
                                                </a>
                                            </li>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link border-light-subtle shadow-none <?php echo $i === $page ? 'bg-primary border-primary' : 'text-muted'; ?>" href="?page=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link border-light-subtle rounded-end-pill px-3 shadow-none" href="?page=<?php echo $page + 1; ?>">
                                                    <i class="fas fa-chevron-right x-small"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

