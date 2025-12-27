<!-- Chat Widget Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width: 380px; margin-left: auto; margin-right: 20px; margin-top: 160px; margin-bottom: 20px;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <!-- Chat Header -->
            <div class="modal-header bg-danger text-white border-0 py-2 px-3">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div id="chatBackButton" class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center cursor-pointer d-none" onclick="loadConversations()" style="width: 26px; height: 26px; font-size: 0.75rem;" title="Back to list">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <i class="fas fa-comment-dots" id="chatHeaderIcon"></i>
                    <div class="flex-grow-1 ms-1">
                        <h6 class="mb-0 small fw-bold" id="chatModalTitle">Customer Support</h6>
                        <small class="d-block opacity-75" id="chatSubtitle" style="font-size: 0.65rem;">Online</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-link text-white p-0 shadow-none" onclick="minimizeChat()" title="Minimize">
                            <i class="fas fa-minus small"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.6rem;"></button>
                    </div>
                </div>
            </div>
            
            <!-- Conversation List View -->
            <div id="conversationListView" class="d-none">
                <div class="modal-body p-0">
                    <div class="p-2 border-bottom bg-light">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-0 bg-white"><i class="fas fa-search x-small"></i></span>
                            <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." id="searchConversations">
                        </div>
                    </div>
                    <div id="conversationsList" class="overflow-auto" style="max-height: 550px;">
                        <!-- Conversation items will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-2">
                    <button type="button" class="btn btn-danger btn-sm w-100 py-1" onclick="showChatForm()">
                        <i class="fas fa-plus me-1 small"></i> New Chat
                    </button>
                </div>
            </div>
            
            <!-- Chat View -->
            <div id="chatView">
                <!-- Product Context (if applicable) -->
                <div id="productContext" class="d-none border-bottom bg-light p-2">
                    <div class="d-flex align-items-center gap-2">
                        <img id="productContextImage" src="" alt="" class="rounded" style="width: 30px; height: 30px; object-fit: cover;">
                        <div class="x-small flex-grow-1">
                            <div class="fw-bold text-truncate" id="productContextName"></div>
                            <div class="text-muted x-small" id="productContextSku"></div>
                        </div>
                        <button class="btn btn-sm btn-link text-muted p-0 shadow-none" onclick="clearProductContext()" title="Remove">
                            <i class="fas fa-times small"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Messages Container -->
                <div class="modal-body p-2 bg-light" id="messagesContainer" style="max-height: 550px; min-height: 400px; overflow-y: auto;">
                    <div id="messagesList">
                        <!-- Messages will be loaded here -->
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comment-slash fs-3 mb-2 d-block opacity-25"></i>
                            <p class="small mb-0">Start a conversation!</p>
                        </div>
                    </div>
                    <div id="typingIndicator" class="d-none">
                        <div class="d-flex align-items-center gap-2 text-muted x-small">
                            <div class="spinner-grow spinner-grow-sm" role="status" style="width: 0.4rem; height: 0.4rem;"></div>
                            <span>Typing...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Message Input -->
                <div class="modal-footer border-top bg-white p-2">
                    <form id="chatMessageForm" class="w-100">
                        <input type="hidden" id="currentConversationId" value="">
                        <input type="hidden" id="currentProductId" value="">
                        
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary border-end-0 px-2" onclick="toggleQuickMessages()" title="Quick messages">
                                <i class="fas fa-bolt x-small"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary border-end-0 border-start-0 px-2" onclick="document.getElementById('chatFileInput').click()" title="Attach file">
                                <i class="fas fa-paperclip x-small"></i>
                            </button>
                            <input type="file" id="chatFileInput" class="d-none" accept="image/*,.pdf,.doc,.docx">
                            <textarea id="chatMessageInput" class="form-control border-start-0 border-end-0 shadow-none py-1" placeholder="Type..." rows="1" style="resize: none; max-height: 80px; font-size: 0.85rem;"></textarea>
                            <button type="submit" class="btn btn-danger px-3" id="sendMessageBtn">
                                <i class="fas fa-paper-plane x-small"></i>
                            </button>
                        </div>
                        
                        <!-- Quick Messages Dropdown -->
                        <div id="quickMessagesDropdown" class="d-none mt-2 p-2 border rounded bg-white shadow-sm">
                            <div class="small fw-bold text-muted mb-2">Quick Messages:</div>
                            <div class="d-grid gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="insertQuickMessage('Is this product available?')">
                                    Is this product available?
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="insertQuickMessage('What is the shipping time?')">
                                    What is the shipping time?
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="insertQuickMessage('Can I get a discount on bulk orders?')">
                                    Can I get a discount on bulk orders?
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="insertQuickMessage('What is your return policy?')">
                                    What is your return policy?
                                </button>
                            </div>
                        </div>
                        
                        <!-- Attachment Preview -->
                        <div id="attachmentPreview" class="d-none mt-2 p-2 border rounded bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-file"></i>
                                <span class="small flex-grow-1" id="attachmentName"></span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearAttachment()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Button -->
<?php if (is_logged_in()): ?>
<div class="chat-float-button" id="chatFloatButton" style="position: fixed; bottom: 90px; right: 20px; z-index: 1050;">
    <button class="btn btn-danger rounded-circle shadow-lg position-relative" style="width: 60px; height: 60px;" onclick="toggleChatModal()" title="Open Chat">
        <i class="fas fa-comment-dots fs-4"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark d-none" id="chatUnreadBadge">
            0
        </span>
    </button>
</div>
<?php endif; ?>

<style>
/* Chat message styles */
.chat-message {
    max-width: 75%;
    margin-bottom: 0.75rem;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.chat-message.sent {
    margin-left: auto;
}

.chat-message.received {
    margin-right: auto;
}

.chat-message .message-bubble {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    word-wrap: break-word;
}

.chat-message.sent .message-bubble {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.chat-message.received .message-bubble {
    background: white;
    border: 1px solid #e0e0e0;
    border-bottom-left-radius: 0.25rem;
}

.chat-message .message-time {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.chat-message-attachment {
    max-width: 200px;
    border-radius: 0.5rem;
    cursor: pointer;
}

.conversation-item {
    padding: 0.75rem;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.unread {
    background-color: #fff3cd;
}

.conversation-item.active {
    background-color: #ffe5e5;
}

#chatMessageInput {
    min-height: 38px;
}

#chatMessageInput:focus {
    outline: none;
    box-shadow: none;
}

.chat-float-button .btn:hover {
    transform: scale(1.1);
    transition: transform 0.2s;
}

.cursor-pointer {
    cursor: pointer;
}
</style>

<script>
// Chat System JavaScript
let currentConversationId = null;
let currentProductId = null;
let currentAttachment = null;
let messagePollingInterval = null;
let typingTimeout = null;

// Initialize chat
document.addEventListener('DOMContentLoaded', function() {
    <?php if (is_logged_in()): ?>
    loadUnreadCount();
    setInterval(loadUnreadCount, 30000); // Check every 30 seconds
    <?php endif; ?>
    
    // Auto-resize textarea
    const textarea = document.getElementById('chatMessageInput');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Typing indicator
        textarea.addEventListener('input', function() {
            if (currentConversationId) {
                clearTimeout(typingTimeout);
                sendTypingIndicator();
                typingTimeout = setTimeout(() => {}, 3000);
            }
        });
    }
    
    // Chat message form
    const chatForm = document.getElementById('chatMessageForm');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }
    
    // File input
    const fileInput = document.getElementById('chatFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
});

function toggleChatModal() {
    const modalEl = document.getElementById('chatModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    loadConversations();
}

function minimizeChat() {
    const modalEl = document.getElementById('chatModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

async function loadUnreadCount() {
    try {
        const response = await fetch('api/messages/unread_count.php');
        const data = await response.json();
        if (data.success && data.count > 0) {
            document.getElementById('chatUnreadBadge').textContent = data.count;
            document.getElementById('chatUnreadBadge').classList.remove('d-none');
        } else {
            document.getElementById('chatUnreadBadge').classList.add('d-none');
        }
    } catch (error) {
        console.error('Error loading unread count:', error);
    }
}

async function loadConversations() {
    try {
        const response = await fetch('api/messages/conversations.php');
        const data = await response.json();
        
        if (data.success) {
            displayConversations(data.conversations);
            document.getElementById('conversationListView').classList.remove('d-none');
            document.getElementById('chatView').classList.add('d-none');
            
            // Reset Header
            document.getElementById('chatModalTitle').textContent = 'Customer Support';
            document.getElementById('chatSubtitle').textContent = 'Online';
            document.getElementById('chatBackButton').classList.add('d-none');
            document.getElementById('chatHeaderIcon').classList.remove('d-none');
            clearProductContext();
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
    }
}

function displayConversations(conversations) {
    const container = document.getElementById('conversationsList');
    
    if (conversations.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fs-1 mb-3 d-block opacity-25"></i>
                <p>No conversations yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.customer_unread_count > 0 ? 'unread' : ''}" onclick="openConversation(${conv.id})">
            <div class="d-flex align-items-center gap-2">
                <div class="flex-shrink-0" style="width: 45px; height: 45px;">
                    ${conv.product_image ? 
                        `<img src="${conv.product_image}" alt="" class="rounded w-100 h-100 object-fit-cover shadow-xs border">` : 
                        '<div class="bg-secondary bg-opacity-25 rounded d-flex align-items-center justify-content-center text-secondary w-100 h-100 border"><i class="fas fa-comment small"></i></div>'
                    }
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <div class="fw-bold text-dark text-truncate pe-2" style="font-size: 0.8rem;">${conv.product_name || 'General Inquiry'}</div>
                        <div class="text-muted" style="font-size: 0.6rem; white-space: nowrap;">${formatTime(conv.last_message_at)}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted text-truncate pe-2" style="font-size: 0.7rem; line-height: 1.2;">${conv.last_message || 'No messages'}</div>
                        ${conv.customer_unread_count > 0 ? `<span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 0.6rem; padding: 0.2em 0.5em;">${conv.customer_unread_count}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

async function openConversation(conversationId) {
    currentConversationId = conversationId;
    document.getElementById('currentConversationId').value = conversationId;
    
    document.getElementById('conversationListView').classList.add('d-none');
    document.getElementById('chatView').classList.remove('d-none');
    
    // Update Header UI
    document.getElementById('chatBackButton').classList.remove('d-none');
    document.getElementById('chatHeaderIcon').classList.add('d-none');
    
    await loadMessages(conversationId);
    
    // Start polling for new messages
    if (messagePollingInterval) clearInterval(messagePollingInterval);
    messagePollingInterval = setInterval(() => loadMessages(conversationId), 5000);
}

function showChatForm() {
    currentConversationId = null;
    document.getElementById('currentConversationId').value = '';
    document.getElementById('conversationListView').classList.add('d-none');
    document.getElementById('chatView').classList.remove('d-none');
    
    // Update Header UI
    document.getElementById('chatBackButton').classList.remove('d-none');
    document.getElementById('chatHeaderIcon').classList.add('d-none');
    
    document.getElementById('messagesList').innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fas fa-comment-slash fs-3 mb-2 d-block opacity-25"></i>
            <p class="small mb-0">Start a conversation!</p>
        </div>
    `;
}

function openProductChat(productId, productName) {
    currentProductId = productId;
    document.getElementById('currentProductId').value = productId;
    
    // Show product context
    document.getElementById('productContext').classList.remove('d-none');
    document.getElementById('productContextName').textContent = productName;
    
    toggleChatModal();
    showChatForm();
}

function clearProductContext() {
    currentProductId = null;
    document.getElementById('currentProductId').value = '';
    document.getElementById('productContext').classList.add('d-none');
}

async function loadMessages(conversationId) {
    try {
        const response = await fetch(`api/messages/get.php?conversation_id=${conversationId}`);
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
            updateConversationHeader(data.conversation);
            
            if (data.admin_typing) {
                document.getElementById('typingIndicator').classList.remove('d-none');
            } else {
                document.getElementById('typingIndicator').classList.add('d-none');
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

function displayMessages(messages) {
    const container = document.getElementById('messagesList');
    
    if (messages.length === 0) {
        return;
    }
    
    container.innerHTML = messages.map(msg => {
        const isSent = msg.sender_type === 'customer';
        const time = formatTime(msg.created_at);
        
        return `
            <div class="chat-message ${isSent ? 'sent' : 'received'} d-flex flex-column">
                <div class="message-bubble">
                    ${msg.message}
                    ${msg.attachments ? displayAttachments(JSON.parse(msg.attachments)) : ''}
                </div>
                <div class="message-time ${isSent ? 'text-end' : 'text-start'}">${time}</div>
            </div>
        `;
    }).join('');
    
    // Scroll to bottom
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function displayAttachments(attachments) {
    return attachments.map(att => {
        if (att.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            return `<img src="${att}" class="chat-message-attachment d-block mt-2" alt="Attachment">`;
        } else {
            return `<a href="${att}" class="btn btn-sm btn-outline-light mt-2" target="_blank">
                <i class="fas fa-file"></i> ${att.split('/').pop()}
            </a>`;
        }
    }).join('');
}

function updateConversationHeader(conversation) {
    if (conversation.product_name) {
        document.getElementById('chatModalTitle').textContent = conversation.product_name;
        document.getElementById('chatSubtitle').textContent = `Conversation about ${conversation.product_name}`;
        
        if (conversation.product_image) {
            document.getElementById('productContext').classList.remove('d-none');
            document.getElementById('productContextImage').src = conversation.product_image;
            document.getElementById('productContextName').textContent = conversation.product_name;
            document.getElementById('productContextSku').textContent = `SKU: ${conversation.product_sku || 'N/A'}`;
        }
    }
}

async function sendMessage() {
    const textarea = document.getElementById('chatMessageInput');
    const message = textarea.value.trim();
    
    if (!message && !currentAttachment) return;
    
    const btn = document.getElementById('sendMessageBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const formData = {
            conversation_id: currentConversationId || null,
            product_id: currentProductId || null,
            message: message
        };
        
        // Handle attachment upload first if present
        if (currentAttachment) {
            const attachmentPath = await uploadAttachment();
            if (attachmentPath) {
                formData.attachments = [attachmentPath];
            }
        }
        
        const response = await fetch('api/messages/send.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            textarea.value = '';
            textarea.style.height = 'auto';
            clearAttachment();
            
            if (data.conversation_id) {
                currentConversationId = data.conversation_id;
                document.getElementById('currentConversationId').value = data.conversation_id;
                loadMessages(data.conversation_id);
            }
        } else {
            alert(data.message || 'Failed to send message');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
}

async function uploadAttachment() {
    if (!currentAttachment) return null;
    
    const formData = new FormData();
    formData.append('file', currentAttachment);
    formData.append('conversation_id', currentConversationId || 'temp');
    
    // Use product name as slug if available
    const slugSource = document.getElementById('chatModalTitle').textContent;
    if (slugSource && slugSource !== 'Customer Support') {
        formData.append('slug', slugSource);
    }
    
    try {
        const response = await fetch('api/messages/upload.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        return data.success ? data.file_path : null;
    } catch (error) {
        console.error('Error uploading attachment:', error);
        return null;
    }
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    currentAttachment = file;
    document.getElementById('attachmentPreview').classList.remove('d-none');
    document.getElementById('attachmentName').textContent = file.name;
}

function clearAttachment() {
    currentAttachment = null;
    document.getElementById('chatFileInput').value = '';
    document.getElementById('attachmentPreview').classList.add('d-none');
}

function toggleQuickMessages() {
    const dropdown = document.getElementById('quickMessagesDropdown');
    dropdown.classList.toggle('d-none');
}

function insertQuickMessage(text) {
    document.getElementById('chatMessageInput').value = text;
    document.getElementById('quickMessagesDropdown').classList.add('d-none');
    document.getElementById('chatMessageInput').focus();
}

async function sendTypingIndicator() {
    if (!currentConversationId) return;
    
    try {
        await fetch('api/messages/typing.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({conversation_id: currentConversationId})
        });
    } catch (error) {
        console.error('Error sending typing indicator:', error);
    }
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    if (diff < 604800000) return Math.floor(diff / 86400000) + 'd ago';
    
    return date.toLocaleDateString();
}

// Clean up polling when modal is closed
document.getElementById('chatModal').addEventListener('hidden.bs.modal', function () {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
        messagePollingInterval = null;
    }
});
</script>