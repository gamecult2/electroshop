<?php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Conversation.php';
require_once '../models/Message.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$conversationModel = new Conversation();
$messageModel = new Message();

// Handle archive/restore actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $convId = (int)($_POST['conversation_id'] ?? 0);
    if ($convId > 0) {
        if ($_POST['action'] === 'archive') {
            $conversationModel->archive($convId, 1);
            $_SESSION['message'] = 'Conversation archived';
        } elseif ($_POST['action'] === 'restore') {
            $conversationModel->archive($convId, 0);
            $_SESSION['message'] = 'Conversation restored';
        }
        header('Location: messages.php' . (isset($_GET['view']) ? '?view='.$_GET['view'] : ''));
        exit;
    }
}

//Get filter parameters
$view = $_GET['view'] ?? 'active';
$filters = [
    'status' => $_GET['status'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'assigned_to' => $_GET['assigned_to'] ?? '',
    'search' => $_GET['search'] ?? '',
    'user_id' => $_GET['customer_id'] ?? '', // Map customer_id url param to user_id filter
    'is_archived' => ($view === 'archived' ? 1 : 0)
];

// Get conversations
$conversations = $conversationModel->getAllConversations($filters);
$unreadCount = $conversationModel->getAdminUnreadCount($_SESSION['admin_id'] ?? null);

// Get canned responses
$cannedStmt = $pdo->query("SELECT * FROM canned_responses WHERE is_active = 1 ORDER BY usage_count DESC");
$cannedResponses = $cannedStmt->fetchAll();

$page_title = 'Customer Messages';
$page_heading = 'Customer Messages';

require_once 'header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="h4 mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i>Customer Messages</h3>
            <p class="text-muted mb-0">Manage all customer communications</p>
        </div>
        <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
            <i class="fas fa-sync-alt me-1"></i>Refresh
        </button>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-1"><?php echo $unreadCount; ?></h5>
                            <p class="card-text small mb-0">Unread</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">
                                <?php echo count(array_filter($conversations, fn($c) => $c['status'] === 'open')); ?>
                            </h5>
                            <p class="card-text small mb-0">Open</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comment-dots fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">
                                <?php echo count(array_filter($conversations, fn($c) => $c['status'] === 'pending')); ?>
                            </h5>
                            <p class="card-text small mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-1"><?php echo count($conversations); ?></h5>
                            <p class="card-text small mb-0">Total</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Conversations List -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="h6 mb-0 fw-bold">Conversations</h5>
                    <span class="badge bg-primary"><?php echo count($conversations); ?></span>
                </div>
                <div class="card-body p-0">
                    <!-- View Toggle -->
                    <div class="p-2 border-bottom bg-light">
                        <div class="btn-group w-100 rounded-3 overflow-hidden border">
                            <a href="messages.php?view=active" class="btn btn-sm <?php echo $view === 'active' ? 'btn-primary' : 'btn-white'; ?> fw-bold" style="font-size: 11px;">Active</a>
                            <a href="messages.php?view=archived" class="btn btn-sm <?php echo $view === 'archived' ? 'btn-primary' : 'btn-white'; ?> fw-bold" style="font-size: 11px;">Archived</a>
                        </div>
                    </div>
                    <!-- Filters -->
                    <div class="p-3 border-bottom">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select form-select-sm" name="filter_status" onchange="filterConversations(this)">
                                    <option value="">All Statuses</option>
                                    <option value="open" <?php echo $filters['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Search conversations..." 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>" 
                                       onkeyup="filterConversationsBySearch(this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conversation List -->
                    <div class="list-group list-group-flush" id="conversationsList" style="max-height: 800px; overflow-y: auto;">
                        <?php if (empty($conversations)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No conversations found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                                <div class="list-group-item list-group-item-action conversation-item d-flex align-items-center p-3 <?php echo $conv['admin_unread_count'] > 0 ? 'bg-light' : ''; ?>"
                                     onclick="loadConversation(<?php echo $conv['id']; ?>)">
                                    <div class="flex-shrink-0 me-3 position-relative">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($conv['user_first_name'], 0, 1)); ?>
                                        </div>
                                        <?php if ($conv['admin_unread_count'] > 0): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                <?php echo $conv['admin_unread_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 fw-bold small"><?php echo htmlspecialchars($conv['user_first_name'] . ' ' . $conv['user_last_name']); ?></h6>
                                        <p class="text-muted small mb-0 text-truncate"><?php echo htmlspecialchars($conv['last_message'] ?? 'No messages yet'); ?></p>
                                    </div>
                                    <div class="ms-2">
                                        <span class="badge bg-<?php echo ['open' => 'success', 'pending' => 'warning', 'closed' => 'secondary'][$conv['status']] ?? 'secondary'; ?> small">
                                            <?php echo ucfirst($conv['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Chat Area -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="h6 mb-0 fw-bold">Message Thread</h5>
                </div>
                <div class="card-body" style="height: 850px; display: flex; flex-direction: column;">
                    <div id="chatContainer" class="flex-grow-1 overflow-auto mb-3" style="min-height: 400px;">
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Select a conversation</h5>
                            <p class="text-muted">Choose a conversation from the list to start messaging</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <form id="messageForm" onsubmit="sendMessage(event)" class="d-none">
                            <div class="mb-2">
                                <select class="form-select form-select-sm" id="cannedSelect" onchange="insertCanned(this.value)">
                                    <option value="">Quick responses...</option>
                                    <?php foreach ($cannedResponses as $canned): ?>
                                    <option value="<?php echo htmlspecialchars($canned['message']); ?>"><?php echo htmlspecialchars($canned['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <textarea class="form-control" id="messageInput" placeholder="Type your message..." rows="2" required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let activeConversationId = null;
let targetCustomerId = null; // For new conversations

function filterConversations(select) {
    const params = new URLSearchParams(window.location.search);
    params.set(select.name.replace('filter_', ''), select.value);
    window.location.href = '?' + params.toString();
}

function filterConversationsBySearch(searchValue) {
    // Update URL with search parameter
    const params = new URLSearchParams(window.location.search);
    if (searchValue) {
        params.set('search', searchValue);
    } else {
        params.delete('search');
    }
    window.location.href = '?' + params.toString();
}

async function loadConversation(id) {
    if (!id) return;

    activeConversationId = id;
    targetCustomerId = null; // Reset target customer

    // Mark as active
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });

    const activeItem = document.querySelector(`.conversation-item[onclick="loadConversation(${id})"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }

    try {
        const response = await fetch(`../api/messages/admin/get_conversation.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            displayChat(data);
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
        alert('Error loading conversation. Please try again.');
    }
}

function displayChat(data) {
    const container = document.getElementById('chatContainer');

    // Set the global variables for the conversation product image and ID
    window.conversationProductImage = data.conversation.product_image;
    window.conversationProductId = data.conversation.product_id;

    // Show messages
    container.innerHTML = `
        <div class="mb-3">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3"
                     style="width: 40px; height: 40px;">
                    ${data.conversation.user_first_name.charAt(0).toUpperCase()}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">${data.conversation.user_first_name} ${data.conversation.user_last_name}</h5>
                            <a href="customer_details.php?id=${data.conversation.user_id}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2 shadow-xs" title="View Customer Profile" style="font-size: 0.75rem;">
                                <i class="fas fa-user me-1"></i> Profile
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm rounded-pill px-3 fw-bold text-uppercase shadow-none border-light-subtle" 
                                    style="font-size: 10px; width: 110px;" 
                                    onchange="updateStatus(this.value)">
                                <option value="open" ${data.conversation.status === 'open' ? 'selected' : ''}>Open</option>
                                <option value="pending" ${data.conversation.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="closed" ${data.conversation.status === 'closed' ? 'selected' : ''}>Closed</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-light border shadow-xs rounded-circle" 
                                    onclick="archiveConversation(${data.conversation.id}, ${data.conversation.is_archived == 1 ? "'restore'" : "'archive'"})"
                                    title="${data.conversation.is_archived == 1 ? 'Restore' : 'Archive'} Conversation">
                                <i class="fas fa-${data.conversation.is_archived == 1 ? 'undo' : 'archive'} text-warning" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    </div>
                    ${data.conversation.product_name ?
                        `<div class="d-flex align-items-center">
                            <a href="../product.php?id=${data.conversation.product_id}" target="_blank" class="text-muted text-decoration-none d-flex align-items-center">
                                <i class="fas fa-box me-1"></i>
                                <span class="me-2">${data.conversation.product_name}</span>
                            </a>
                            ${data.conversation.product_id ?
                                `<a href="../product.php?id=${data.conversation.product_id}" target="_blank">
                                    <img src="../img/product-placeholder.jpg" class="product-image-thumb rounded" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;" onerror="this.src='../img/product-placeholder.jpg';">
                                </a>` :
                                ''
                            }
                        </div>` :
                        '<small class="text-muted">General Inquiry</small>'
                    }
                </div>
            </div>
        </div>
        <div id="messagesArea" class="border rounded p-3 mb-3" style="height: 600px; overflow-y: auto; background-color: #f8f9fa;">
            ${displayMessages(data.messages)}
        </div>
    `;

    // Show message form
    document.getElementById('messageForm').classList.remove('d-none');

    // Scroll to bottom
    const messagesArea = document.getElementById('messagesArea');
    messagesArea.scrollTop = messagesArea.scrollHeight;

    // Update the header product image
    updateProductImage();
    
    // Update all product message images
    updateProductMessageImages();
}

// Function to update the header product image
async function updateProductImage() {
    if (window.conversationProductId) {
        try {
            const response = await fetch(`get_product_image.php?product_id=${window.conversationProductId}`);
            const result = await response.json();

            if (result.image_url) {
                const imgElement = document.querySelector('.product-image-thumb');
                if (imgElement) {
                    imgElement.src = result.image_url;
                    imgElement.onerror = function() {
                        this.src = '../img/product-placeholder.jpg';
                    };
                }
            }
        } catch (error) {
            console.error('Error fetching product image:', error);
        }
    }
}

// Function to update all product images in messages
async function updateProductMessageImages() {
    const images = document.querySelectorAll('.product-message-thumb[data-product-id]');
    
    // Process unique product IDs to minimize requests
    const productIds = new Set();
    images.forEach(img => productIds.add(img.getAttribute('data-product-id')));
    
    for (const pid of productIds) {
        try {
            const response = await fetch(`get_product_image.php?product_id=${pid}`);
            const result = await response.json();
            
            if (result.image_url) {
                // Update all images with this product ID
                const specificImages = document.querySelectorAll(`.product-message-thumb[data-product-id="${pid}"]`);
                specificImages.forEach(img => {
                    img.src = result.image_url;
                    img.onerror = function() {
                        this.src = '../img/product-placeholder.jpg';
                    };
                });
            }
        } catch (error) {
            console.error(`Error updating image for product ${pid}:`, error);
        }
    }
}

function displayMessages(messages) {
    if (!messages || messages.length === 0) {
        return '<div class="text-center text-muted py-5"><i class="fas fa-comments fa-2x mb-3"></i><p>No messages yet</p></div>';
    }

    return messages.map(msg => {
        const isSent = msg.sender_type === 'admin';
        // Check if message contains product information
        const isProductMessage = msg.message.includes('Product:') && msg.message.includes('Link:');

        return `
            <div class="d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}">
                <div class="d-flex flex-column ${isSent ? 'align-items-end' : 'align-items-start'}" style="max-width: 80%;">
                    <div class="p-3 rounded-3 ${isSent ? 'bg-primary text-white' : 'bg-light'}">
                        ${isProductMessage ? formatProductMessage(msg.message, window.conversationProductImage, window.conversationProductId) : `<div>${msg.message}</div>`}
                    </div>
                    <small class="text-muted mt-1">
                        ${new Date(msg.created_at).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}
                    </small>
                </div>
            </div>
        `;
    }).join('');
}

function formatProductMessage(message, productImage, productId) {
    // Split the message to extract product information
    const lines = message.split('\n');
    let formattedMessage = '<div class="product-message d-flex">';

    // Add product thumbnail if we have a product ID to fetch the actual image
    if (productId) {
        formattedMessage += `<div class="me-3"><img src="../img/product-placeholder.jpg" class="product-message-thumb rounded border" style="width: 60px; height: 60px; object-fit: cover;" data-product-id="${productId}" onerror="this.src='../img/product-placeholder.jpg';"></div>`;
    }

    formattedMessage += '<div>';

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (line.startsWith('Product:')) {
            const productName = line.substring(8).trim(); // Remove 'Product:' prefix
            formattedMessage += `<div class="fw-bold text-dark mb-1"><i class="fas fa-box text-primary me-1"></i>${productName}</div>`;
        } else if (line.startsWith('Link:')) {
            const productLink = line.substring(5).trim(); // Remove 'Link:' prefix
            formattedMessage += `<div class="mb-2"><a href="${productLink}" target="_blank" class="text-decoration-none"><i class="fas fa-external-link-alt me-1"></i>View Product</a></div>`;
        } else if (line) {
            formattedMessage += `<div class="mb-1">${line}</div>`;
        }
    }

    formattedMessage += '</div></div>';

    return formattedMessage;
}

async function sendMessage(e) {
    e.preventDefault();

    const message = document.getElementById('messageInput').value.trim();
    if (!message) return;

    if (!activeConversationId && !targetCustomerId) return;

    const payload = {
        message: message
    };

    if (activeConversationId) {
        payload.conversation_id = activeConversationId;
    } else {
        payload.customer_id = targetCustomerId;
    }

    try {
        const response = await fetch('../api/messages/admin/send.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('messageInput').value = '';
            loadConversation(activeConversationId || data.conversation_id);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error sending message. Please try again.');
    }
}

async function updateStatus(status) {
    if (!activeConversationId) return;

    try {
        await fetch('../api/messages/admin/update_conversation.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                conversation_id: activeConversationId,
                status: status
            })
        });
        
        // Reload the conversation to update status display
        loadConversation(activeConversationId);
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Error updating status. Please try again.');
    }
}

function archiveConversation(id, action) {
    if (!confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} this conversation?`)) return;
    
    const form = document.getElementById('archiveForm');
    document.getElementById('archive_conv_id').value = id;
    document.getElementById('archive_action').value = action;
    form.submit();
}

function insertCanned(message) {
    if (message) {
        document.getElementById('messageInput').value = message;
        document.getElementById('cannedSelect').selectedIndex = 0;
    }
}

// Auto-open conversation logic
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const customerId = urlParams.get('customer_id');
    const customerName = urlParams.get('customer_name') || 'Customer';

    if (customerId) {
        const firstConversation = document.querySelector('.conversation-item');
        if (firstConversation) {
            // Existing conversation found - open it
            const onclick = firstConversation.getAttribute('onclick');
            const match = onclick.match(/loadConversation\((\d+)\)/);
            if (match && match[1]) {
                loadConversation(match[1]);
            }
        } else {
            // No conversation found - start NEW
            // For now, just select the first conversation if available
            initiateNewChat(customerId, decodeURIComponent(customerName));
        }
    }
});

function initiateNewChat(customerId, customerName) {
    targetCustomerId = customerId;
    activeConversationId = null;

    const container = document.getElementById('chatContainer');

    container.innerHTML = `
        <div class="mb-3">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white me-3"
                     style="width: 40px; height: 40px;">
                    ${customerName.charAt(0).toUpperCase()}
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">New Message to ${customerName}</h5>
                    <small class="text-muted">Starting a new conversation</small>
                </div>
            </div>
        </div>
        <div class="text-center py-5">
            <i class="fas fa-paper-plane fa-2x text-muted mb-3"></i>
            <p>Type a message below to start the conversation.</p>
        </div>
    `;
    
    // Show message form
    document.getElementById('messageForm').classList.remove('d-none');
}
</script>

<!-- Hidden form for archive/restore actions -->
<form id="archiveForm" method="POST" action="messages.php?view=<?php echo $view; ?>" style="display:none;">
    <input type="hidden" name="action" id="archive_action">
    <input type="hidden" name="conversation_id" id="archive_conv_id">
</form>

<?php require_once 'footer.php'; ?>