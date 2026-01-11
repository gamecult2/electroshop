<?php
require_once 'includes/init.php';
require_once 'models/Customer.php';
require_once 'models/Order.php';
require_once 'models/Wishlist.php';
require_once 'models/Review.php';

require_login();

require_once 'includes/header.php';

$userId = get_current_user_id();
$customerModel = new Customer();
$orderModel = new Order();
$wishlistModel = new Wishlist();
$reviewModel = new Review();

$user = $customerModel->getById($userId);
$addresses = $customerModel->getCustomerAddresses($userId);
$orders = $orderModel->getUserOrders($userId);
$wishlistCount = $wishlistModel->getCount($userId);
// In a real app, pagination would be needed here
$userReviews = $reviewModel->getRecentReviews(50); // Fetching recent reviews, need to filter by user in loop or add method. 
// Wait, getRecentReviews gets ALL reviews. I need getUserReviews. 
// Review model has getUserReview($uid, $pid) but not getAllForUser.
// I will filter manually for now or better, rely on a custom query if I could.
// Let's assume for this context we iterate or I can add a simple query via $pdo directly if needed, 
// but sticking to "Review and edit", I will try to use what's available or add a small helper.
// Actually, I can use a direct query here for efficiency if the model is lacking, 
// OR I can filter the result of getRecentReviews if the volume is low, BUT that's bad practice.
// Let's look at Review.php again... `getUserReview` is single.
// I'll add a quick ad-hoc query here using the global PDO for the reviews tab.
$stmt = $GLOBALS['pdo']->prepare("SELECT r.*, p.name_en as product_name, (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as product_image FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.customer_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$userId]);
$myReviews = $stmt->fetchAll();


$csrfToken = generate_csrf_token();
$loyaltyPoints = 450;
$loyaltyTier = 'Silver';
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('my_account')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <!-- Account Header Banner -->
    <div class="card border-0 shadow-sm bg-dark text-white mb-4 overflow-hidden">
        <div class="card-body p-3 p-md-4 d-flex align-items-center">
            <div class="row align-items-center g-3 w-100">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-lg flex-shrink-0" style="width: 50px; height: 50px;">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h1 class="h6 mb-1 fw-bold"><?php echo t('welcome'); ?>, <?php echo htmlspecialchars($user['first_name']); ?></h1>
                            <span class="badge bg-danger rounded-pill px-2 py-1 x-small"><?php echo $loyaltyTier; ?> Member</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row text-center text-md-end g-2">
                        <div class="col-4">
                            <div class="p-1">
                                <span class="d-block fs-5 fw-bold"><?php echo $loyaltyPoints; ?></span>
                                <span class="d-block small opacity-75">Points</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 border-start border-white border-opacity-10">
                                <span class="d-block fs-5 fw-bold"><?php echo count($orders); ?></span>
                                <span class="d-block small opacity-75"><?php echo t('orders'); ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 border-start border-white border-opacity-10">
                                <span class="d-block fs-5 fw-bold"><?php echo $wishlistCount; ?></span>
                                <span class="d-block small opacity-75"><?php echo t('wishlist'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: calc(var(--header-height, 140px) + 20px); z-index: 1020;">
                <div class="card-header bg-danger text-white border-0 py-3 fw-bold">
                    <i class="fas fa-user-circle me-2"></i> <?php echo t('my_account'); ?>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <button class="list-group-item list-group-item-action active py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#dashboard" type="button" role="tab">
                        <i class="fas fa-th-large text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('dashboard'); ?></span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#orders" type="button" role="tab">
                        <i class="fas fa-box-open text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('order_history'); ?></span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-user text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('profile'); ?></span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#addresses" type="button" role="tab">
                        <i class="fas fa-map-marker-alt text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('addresses'); ?></span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#wishlist" type="button" role="tab">
                        <i class="fas fa-heart text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('wishlist'); ?></span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3 position-relative" data-bs-toggle="pill" data-bs-target="#messages" type="button" role="tab">
                        <i class="fas fa-comments text-secondary opacity-75" style="width: 20px;"></i> <span>Messages</span>
                        <span class="badge bg-danger rounded-pill position-absolute top-50 end-0 translate-middle-y me-3 d-none" id="accountMessagesBadge">0</span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#reviews" type="button" role="tab">
                        <i class="fas fa-star text-secondary opacity-75" style="width: 20px;"></i> <span>My Reviews</span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#loyalty" type="button" role="tab">
                        <i class="fas fa-gift text-secondary opacity-75" style="width: 20px;"></i> <span>Rewards</span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#settings" type="button" role="tab">
                        <i class="fas fa-cog text-secondary opacity-75" style="width: 20px;"></i> <span>Settings</span>
                    </button>
                    <a href="logout.php" class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3 text-danger border-top">
                        <i class="fas fa-sign-out-alt" style="width: 20px;"></i> <span><?php echo t('logout'); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <div class="tab-content border-0">
                
                <!-- 1. DASHBOARD -->
                <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold"><?php echo t('dashboard'); ?></h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Recent Orders Widget -->
                                <div class="col-md-7">
                                    <div class="p-4 rounded-4 border bg-white h-100">
                                        <h3 class="h6 mb-4 text-muted text-uppercase small"><?php echo t('recent_orders'); ?></h3>
                                        <?php if (empty($orders)): ?>
                                            <div class="text-center py-4">
                                                <p class="text-muted small mb-3"><?php echo t('no_orders_found'); ?></p>
                                                <a href="products.php" class="btn btn-outline-danger btn-sm rounded-pill px-4"><?php echo t('start_shopping'); ?></a>
                                            </div>
                                        <?php else: ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                                                    <div class="list-group-item px-0 py-3 border-light d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-bold small">#<?php echo $order['order_number']; ?></div>
                                                            <div class="text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                                        </div>
                                                        <span class="badge rounded-pill <?php 
                                                            echo match($order['status']) {
                                                                'pending' => 'bg-warning text-dark',
                                                                'processing' => 'bg-info',
                                                                'shipped' => 'bg-primary',
                                                                'delivered' => 'bg-success',
                                                                'cancelled' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                        ?> px-3 py-2 small"><?php echo t($order['status']); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mt-4 text-end">
                                                <button class="btn btn-link btn-sm text-danger text-decoration-none fw-bold" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target=\'#orders\']')).show()"><?php echo t('view_all'); ?> <i class="fas fa-chevron-right ms-1"></i></button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Loyalty Widget -->
                                <div class="col-md-5">
                                    <div class="p-4 rounded-4 border bg-light h-100 text-center">
                                        <h3 class="h6 mb-4 text-muted text-uppercase small">Loyalty Status</h3>
                                        <div class="display-5 fw-bold text-danger mb-1"><?php echo $loyaltyPoints; ?></div>
                                        <div class="text-muted small text-uppercase mb-4">Points</div>
                                        
                                        <div class="progress rounded-pill mb-3" style="height: 10px;">
                                            <div class="progress-bar bg-danger shadow-none" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-muted small mb-4">75% to next tier</p>
                                        
                                        <button class="btn btn-dark w-100 rounded-pill fw-bold" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target=\'#loyalty\']')).show()">View Rewards History</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. ORDER HISTORY -->
                <div class="tab-pane fade" id="orders" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold"><?php echo t('order_history'); ?></h2>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($orders)): ?>
                                <div class="p-5 text-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                        <i class="fas fa-shopping-bag text-muted fs-2"></i>
                                    </div>
                                    <p class="text-muted mb-4"><?php echo t('no_orders_description'); ?></p>
                                    <a href="products.php" class="btn btn-danger rounded-pill px-5 fw-bold"><?php echo t('start_shopping'); ?></a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase"><?php echo t('order_number'); ?></th>
                                                <th class="border-0 py-3 small fw-bold text-muted text-uppercase"><?php echo t('order_date'); ?></th>
                                                <th class="border-0 py-3 small fw-bold text-muted text-uppercase"><?php echo t('total'); ?></th>
                                                <th class="border-0 py-3 small fw-bold text-muted text-uppercase"><?php echo t('status'); ?></th>
                                                <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end"><?php echo t('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <strong class="text-dark small">#<?php echo $order['order_number']; ?></strong>
                                                </td>
                                                <td class="py-3">
                                                    <span class="text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="text-danger small"><?php echo format_price($order['total_amount']); ?></strong>
                                                        <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                            <i class="fas fa-tag text-success ms-2" style="font-size: 0.7rem;" title="Discount Applied"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge rounded-pill <?php 
                                                        echo match($order['status']) {
                                                            'pending' => 'bg-warning text-dark',
                                                            'processing' => 'bg-info',
                                                            'shipped' => 'bg-primary',
                                                            'delivered' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    ?> px-3 py-1 fw-bold" style="font-size: 10px; letter-spacing: 0.3px;"><?php echo strtoupper(t($order['status'])); ?></span>
                                                </td>
                                                <td class="px-4 py-3 text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold shadow-xs" style="font-size: 11px;"><?php echo t('view'); ?></a>
<<<<<<< Updated upstream
                                                        <?php if ($order['status'] === 'delivered'): 
                                                            // Check if all items in this order are reviewed
                                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi LEFT JOIN reviews r ON oi.product_id = r.product_id AND r.customer_id = ? WHERE oi.order_id = ? AND r.id IS NULL");
                                                            $stmt->execute([$userId, $order['id']]);
                                                            $unreviewedCount = $stmt->fetchColumn();
                                                            $btnText = ($unreviewedCount == 0) ? 'Reviewed' : 'Review';
                                                            $btnClass = ($unreviewedCount == 0) ? 'btn-outline-success' : 'btn-danger';
                                                        ?>
                                                            <button class="btn <?php echo $btnClass; ?> btn-sm rounded-pill px-3 fw-bold shadow-xs" style="font-size: 11px;" onclick="openReviewModal('<?php echo $order['id']; ?>')"><?php echo $btnText; ?></button>
                                                        <?php endif; ?>
=======
>>>>>>> Stashed changes
                                                        <?php if ($order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                                                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold shadow-xs" style="font-size: 11px;" onclick="trackOrder('<?php echo $order['id']; ?>')"><?php echo t('track_order'); ?></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. PROFILE -->
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold"><?php echo t('profile'); ?></h2>
                        </div>
                        <div class="card-body p-4">
                            <form id="profile-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6 text-start">
                                        <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('first_name'); ?></label>
                                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control border-light-subtle py-2 px-3 focus-ring" required>
                                    </div>
                                    <div class="col-md-6 text-start">
                                        <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('last_name'); ?></label>
                                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control border-light-subtle py-2 px-3" required>
                                    </div>
                                </div>
                                <div class="mb-4 text-start">
                                    <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('email'); ?></label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control border-light-subtle py-2 px-3" required readonly>
                                    <div class="form-text small"><i class="fas fa-lock me-1"></i> Email cannot be changed directly. Contact support.</div>
                                </div>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6 text-start">
                                        <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('phone_number'); ?></label>
                                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control border-light-subtle py-2 px-3">
                                    </div>
                                    <div class="col-md-6 text-start">
                                        <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('date_of_birth'); ?></label>
                                        <input type="date" name="date_of_birth" value="<?php echo $user['date_of_birth']; ?>" class="form-control border-light-subtle py-2 px-3">
                                    </div>
                                </div>
                                <div class="mb-5 text-start">
                                    <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('gender'); ?></label>
                                    <select name="gender" class="form-select border-light-subtle py-2 px-3">
                                        <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>><?php echo t('male'); ?></option>
                                        <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>><?php echo t('female'); ?></option>
                                        <option value="other" <?php echo $user['gender'] === 'other' ? 'selected' : ''; ?>><?php echo t('other'); ?></option>
                                    </select>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-danger rounded-pill px-5 py-2 fw-bold"><?php echo t('save_changes'); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 4. ADDRESSES -->
                <div class="tab-pane fade" id="addresses" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h2 class="h5 mb-0 fw-bold"><?php echo t('saved_addresses'); ?></h2>
                            <button class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" onclick="showAddAddressForm()"><i class="fas fa-plus me-2"></i> Add New</button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 mb-4" id="address-list">
                                <?php foreach ($addresses as $address): ?>
                                    <div class="col-md-6 address-item" data-id="<?php echo $address['id']; ?>">
                                        <div class="card h-100 border p-4 address-card position-relative <?php echo $address['is_default'] ? 'border-danger shadow-sm' : 'border-light-subtle'; ?>">
                                            <div class="d-flex gap-3">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="fw-bold text-dark small text-uppercase">
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                        </span>
                                                        <?php if ($address['is_default']): ?>
                                                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.6rem;">DEFAULT</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="text-muted small mb-3" style="line-height: 1.4; font-size: 0.85rem;">
                                                        <div><?php echo htmlspecialchars($address['street_address']); ?></div>
                                                        <div><?php echo htmlspecialchars($address['commune'] . ', ' . $address['daira']); ?></div>
                                                        <div><?php echo htmlspecialchars($address['wilaya'] . ' ' . $address['postal_code']); ?></div>
                                                    </div>
                                                    
                                                    <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center text-dark small fw-bold">
                                                            <i class="fas fa-phone-alt me-2 text-muted" style="font-size: 0.7rem;"></i>
                                                            <?php echo htmlspecialchars($address['phone_number']); ?>
                                                        </div>
                                                        
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <button class="btn btn-link btn-sm p-0 text-muted fw-bold text-decoration-none" onclick='editAddress(<?php echo json_encode($address); ?>)' style="font-size: 0.75rem;"><?php echo t('edit'); ?></button>
                                                            <span class="text-light-subtle small">|</span>
                                                            <button class="btn btn-link btn-sm p-0 text-danger fw-bold text-decoration-none" onclick="deleteAddress(<?php echo $address['id']; ?>)" style="font-size: 0.75rem;"><?php echo t('delete'); ?></button>
                                                            <?php if (!$address['is_default']): ?>
                                                                <span class="text-light-subtle small">|</span>
                                                                <button class="btn btn-link btn-sm p-0 text-dark fw-bold text-decoration-none" onclick="setDefaultAddress(<?php echo $address['id']; ?>)" style="font-size: 0.75rem;">Default</button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div id="add-address-form" class="collapse mt-4">
                                <div class="p-4 p-md-5 rounded-4 bg-light border">
                                    <h3 id="address-form-title" class="h5 fw-bold mb-4"><?php echo t('add_new_address'); ?></h3>
                                    <form id="new-address-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" id="address_id" name="id">
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-6 text-start">
                                                <label class="form-label small"><?php echo t('wilaya'); ?></label>
                                                <input type="text" id="new_wilaya" name="wilaya" required class="form-control border-light-subtle py-2 px-3">
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label small"><?php echo t('daira'); ?></label>
                                                <input type="text" id="new_daira" name="daira" required class="form-control border-light-subtle py-2 px-3">
                                            </div>
                                        </div>
                                        <div class="row g-4 mb-4 text-start">
                                            <div class="col-md-6">
                                                <label class="form-label small"><?php echo t('commune'); ?></label>
                                                <input type="text" id="new_commune" name="commune" required class="form-control border-light-subtle py-2 px-3">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small"><?php echo t('postal_code'); ?></label>
                                                <input type="text" id="new_postal_code" name="postal_code" class="form-control border-light-subtle py-2 px-3">
                                            </div>
                                        </div>
                                        <div class="mb-4 text-start">
                                            <label class="form-label small"><?php echo t('street_address'); ?></label>
                                            <input type="text" id="new_street_address" name="street_address" required class="form-control border-light-subtle py-2 px-3">
                                        </div>
                                        <div class="row g-4 mb-5 align-items-end text-start">
                                            <div class="col-md-6">
                                                <label class="form-label small"><?php echo t('phone_number'); ?></label>
                                                <input type="tel" id="new_phone_number" name="phone_number" required class="form-control border-light-subtle py-2 px-3">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check pb-2">
                                                    <input class="form-check-input" type="checkbox" id="new_is_default" name="is_default" value="1">
                                                    <label class="form-check-label small pointer" for="new_is_default">Set as default</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3">
                                            <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold"><?php echo t('save'); ?></button>
                                            <button type="button" class="btn btn-outline-dark rounded-pill px-5 fw-bold" onclick="hideAddAddressForm()"><?php echo t('cancel'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 6. WISHLIST -->
                <div class="tab-pane fade" id="wishlist" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold"><?php echo t('wishlist'); ?></h2>
                        </div>
                        <div class="card-body p-5 text-center">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-heart text-danger fs-3"></i>
                            </div>
                            <p class="text-muted fw-bold"><?php echo t('you_have'); ?> <strong><?php echo $wishlistCount; ?></strong> items saved.</p>
                            <div class="mt-4">
                                <a href="wishlist.php" class="btn btn-danger rounded-pill px-5 fw-bold">Go to Wishlist</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 7. MESSAGES -->
                <div class="tab-pane fade" id="messages" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0 fw-bold">My Messages</h2>
                            <button class="btn btn-danger btn-sm rounded-pill" onclick="toggleChatModal()">
                                <i class="fas fa-plus me-1"></i> New Message
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div id="conversationsListAccount" class="list-group list-group-flush">
                                <!-- Conversations will be loaded here -->
                                <div class="text-center py-5">
                                    <div class="spinner-border text-danger" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-3">Loading conversations...</p>
                                </div>
                            </div>

                            <!-- Pagination Controls -->
                            <div id="paginationControlsAccount" class="d-none">
                                <nav aria-label="Messages pagination" class="px-3 py-3">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item" id="prevPageAccount">
                                            <a class="page-link" href="#" aria-label="Previous" onclick="loadAccountConversationsPage(currentPageAccount - 1)">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <!-- Page numbers will be dynamically added here -->
                                        <li class="page-item" id="nextPageAccount">
                                            <a class="page-link" href="#" aria-label="Next" onclick="loadAccountConversationsPage(currentPageAccount + 1)">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 8. REVIEWS -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold">My Reviews</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php if (empty($myReviews)): ?>
                                <div class="text-center py-5">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                        <i class="fas fa-star text-warning fs-2"></i>
                                    </div>
                                    <p class="text-muted mb-4">You haven't written any reviews yet.</p>
                                    <a href="products.php" class="btn btn-outline-danger rounded-pill px-5 fw-bold">Review Purchased Items</a>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($myReviews as $review): ?>
                                        <div class="card border-light-subtle rounded-4">
                                            <div class="card-body p-4">
                                                <div class="d-flex gap-3">
                                                    <img src="<?php echo $review['product_image'] ?: 'img/product-placeholder.jpg'; ?>" 
                                                         alt="" 
                                                         class="rounded"
                                                         style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <h5 class="h6 fw-bold mb-0"><?php echo htmlspecialchars($review['product_name']); ?></h5>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <?php if (!$review['is_approved']): ?>
                                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle x-small px-2 py-1">Pending Approval</span>
                                                                <?php endif; ?>
                                                                <button class="btn btn-link p-0 text-primary small text-decoration-none fw-bold" 
                                                                        onclick='openEditReviewModal(<?php echo json_encode($review); ?>)'>
                                                                    <i class="fas fa-edit me-1"></i>Edit
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 text-warning small">
                                                            <?php for($i=1; $i<=5; $i++): ?>
                                                                <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                                            <?php endfor; ?>
                                                            <span class="text-muted ms-2"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                                        </div>
                                                        <p class="mb-0 text-muted small"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 8. LOYALTY -->
                <div class="tab-pane fade" id="loyalty" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold">Rewards & Loyalty</h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 align-items-center mb-5">
                                <div class="col-md-6 text-center text-md-start">
                                    <div class="display-4 fw-bold text-danger mb-1"><?php echo $loyaltyPoints; ?></div>
                                    <div class="h6 text-muted text-uppercase small mb-4">Total Points Balance</div>
                                    <div class="badge bg-danger rounded-pill px-4 py-2 fs-6 shadow-sm"><?php echo $loyaltyTier; ?> Member</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-4 bg-light rounded-4 border">
                                        <h4 class="h6 fw-bold mb-3">Tier Progress</h4>
                                        <div class="progress rounded-pill mb-3" style="height: 12px;">
                                            <div class="progress-bar bg-danger shadow-none" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="small text-muted mb-0">You need <strong>1,250 more points</strong> to reach <span class="fw-bold text-danger">Gold Tier</span>.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="h6 fw-bold mb-4 text-uppercase small">Available Rewards</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card border border-light-subtle rounded-4 h-100 opacity-75">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            <div class="bg-light p-3 rounded-circle text-danger"><i class="fas fa-tag fa-fw fs-4"></i></div>
                                            <div>
                                                <h5 class="h6 fw-bold mb-1">5% Discount Coupon</h5>
                                                <span class="text-muted small">Costs 500 Points</span>
                                            </div>
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill ms-auto px-3" disabled>Locked</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border border-light-subtle rounded-4 h-100 opacity-75">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            <div class="bg-light p-3 rounded-circle text-danger"><i class="fas fa-truck fa-fw fs-4"></i></div>
                                            <div>
                                                <h5 class="h6 fw-bold mb-1">Free Delivery</h5>
                                                <span class="text-muted small">Costs 800 Points</span>
                                            </div>
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill ms-auto px-3" disabled>Locked</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 9. SETTINGS -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold">Account Settings</h2>
                        </div>
                        <div class="card-body p-4">
                            <form id="password-form">
                                <h3 class="h6 fw-bold mb-4 text-uppercase small">Update Security</h3>
                                <div class="mb-4 text-start">
                                    <label class="form-label small"><?php echo t('current_password'); ?></label>
                                    <input type="password" id="current_password" name="current_password" required class="form-control border-light-subtle py-2 px-3">
                                </div>
                                <div class="row g-4 mb-5 text-start">
                                    <div class="col-md-6">
                                        <label class="form-label small"><?php echo t('new_password'); ?></label>
                                        <input type="password" id="new_password" name="new_password" required class="form-control border-light-subtle py-2 px-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small"><?php echo t('confirm_new_password'); ?></label>
                                        <input type="password" id="confirm_new_password" name="confirm_new_password" required class="form-control border-light-subtle py-2 px-3">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold"><?php echo t('save_changes'); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm border-danger-subtle bg-danger-subtle">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-danger mb-3">Danger Zone</h3>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 fw-bold">Delete Account</p>
                                    <small class="text-muted">Once you delete your account, there is no going back. Please be certain.</small>
                                </div>
                                <button class="btn btn-outline-danger rounded-pill fw-bold" onclick="confirmDeleteAccount()">Delete Account</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-danger">Delete Account?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4">
        <p class="text-muted mb-4">Are you sure you want to do this? This action cannot be undone. Please enter your password to confirm.</p>
        <input type="password" id="delete-confirm-password" class="form-control form-control-lg border-light-subtle" placeholder="Enter your password">
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="deleteAccount()">Permanently Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
...
</div>

<!-- Product Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-star text-warning me-2"></i>Review Your Purchase</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-4">Your feedback helps other shoppers make better choices. Thank you for sharing your experience!</p>
        <div id="reviewItemsContainer">
            <!-- Order items will be loaded here -->
            <div class="text-center py-4">
                <div class="spinner-border text-danger" role="status"></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let reviewModal;
document.addEventListener('DOMContentLoaded', function() {
    reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    // ... existing ...
});

async function openReviewModal(orderId) {
    reviewModal.show();
    const container = document.getElementById('reviewItemsContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-danger"></div><p class="mt-2 text-muted">Fetching items...</p></div>';

    try {
        const response = await fetch(`api/orders/get_items.php?order_id=${orderId}`);
        const data = await response.json();

        if (data.success) {
            renderReviewItems(data.items);
        } else {
            container.innerHTML = `<div class="alert alert-danger">${data.error || 'Failed to load items'}</div>`;
        }
    } catch (err) {
        container.innerHTML = '<div class="alert alert-danger">Connection error. Please try again.</div>';
    }
}

function renderReviewItems(items) {
    const container = document.getElementById('reviewItemsContainer');
    if (items.length === 0) {
        container.innerHTML = '<p class="text-center">No items found for this order.</p>';
        return;
    }

    container.innerHTML = items.map(item => {
        const isReviewed = !!item.review_id;
        const currentRating = item.review_rating || 0;
        
        return `
            <div class="card border-light-subtle rounded-3 mb-3 p-3 shadow-xs review-item-card" data-product-id="${item.product_id}">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <img src="${item.product_image || 'img/product-placeholder.jpg'}" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark">${item.product_name}</h6>
                            ${isReviewed ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill x-small px-2 py-1"><i class="fas fa-check-circle me-1"></i>Reviewed</span>' : ''}
                        </div>
                        <div class="rating-stars mb-2" id="stars-${item.product_id}" data-rating="${currentRating}">
                            ${[1,2,3,4,5].map(num => `
                                <i class="${num <= currentRating ? 'fas' : 'far'} fa-star text-warning pointer fs-5" onclick="setRating(${item.product_id}, ${num})"></i>
                            `).join('')}
                        </div>
                    </div>
                    <div class="col-12">
                        <textarea class="form-control form-control-sm border-light-subtle shadow-none mb-2" 
                                  placeholder="What did you like or dislike? How was the quality?" 
                                  rows="2" id="text-${item.product_id}">${item.review_text || ''}</textarea>
                        <div class="text-end">
                            ${isReviewed ? 
                                `<button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold" onclick="updateExistingReview(${item.review_id}, ${item.product_id}, this)">Update Review</button>` : 
                                `<button class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" onclick="submitSingleReview(${item.product_id}, this)">Post Review</button>`
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

async function updateExistingReview(reviewId, productId, btn) {
    const rating = document.getElementById(`stars-${productId}`).getAttribute('data-rating');
    const text = document.getElementById(`text-${productId}`).value.trim();

    if (!rating || rating == 0) {
        alert('Please select a star rating.');
        return;
    }
    if (!text) {
        alert('Please enter your review text.');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
        const response = await fetch('api/reviews.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                review_id: reviewId,
                rating: parseInt(rating),
                review_text: text
            })
        });
        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');
            // Refresh modal items to show updated state
            const orderId = new URLSearchParams(window.location.search).get('id') || btn.closest('.modal-content').querySelector('input[name="order_id"]')?.value; 
            // Better: just find the card and update it visually or just refresh the modal.
            // Since we don't store orderId easily, let's just show success in the card.
            const card = btn.closest('.review-item-card');
            card.innerHTML = `<div class="text-center py-3 text-success fw-bold"><i class="fas fa-check-circle me-2"></i>Updated! Your review is awaiting re-approval.</div>`;
        } else {
            alert(data.error || 'Failed to update review');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        alert('Connection error.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function setRating(productId, rating) {
    const starsContainer = document.getElementById(`stars-${productId}`);
    starsContainer.setAttribute('data-rating', rating);
    const stars = starsContainer.querySelectorAll('i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
}

async function submitSingleReview(productId, btn) {
    const card = document.querySelector(`.review-item-card[data-product-id="${productId}"]`);
    const rating = document.getElementById(`stars-${productId}`).getAttribute('data-rating');
    const text = document.getElementById(`text-${productId}`).value.trim();

    if (!rating) {
        alert('Please select a star rating.');
        return;
    }
    if (!text) {
        alert('Please enter your review text.');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    try {
        const response = await fetch('api/reviews.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                product_id: productId,
                rating: parseInt(rating),
                review_text: text
            })
        });
        const data = await response.json();

        if (data.success) {
            card.innerHTML = `<div class="text-center py-3 text-success fw-bold"><i class="fas fa-check-circle me-2"></i>Thank you! Your review is awaiting approval.</div>`;
            setTimeout(() => {
                if (document.querySelectorAll('.review-item-card').length === 0) {
                    reviewModal.hide();
                }
            }, 2000);
        } else {
            alert(data.error || 'Failed to submit review');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        alert('Connection error.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-edit text-primary me-2"></i>Edit Your Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="editReviewId">
        <div class="text-center mb-4">
            <div class="rating-stars" id="edit-stars-container">
                <i class="far fa-star text-warning pointer fs-3 mx-1" onclick="setEditRating(1)"></i>
                <i class="far fa-star text-warning pointer fs-3 mx-1" onclick="setEditRating(2)"></i>
                <i class="far fa-star text-warning pointer fs-3 mx-1" onclick="setEditRating(3)"></i>
                <i class="far fa-star text-warning pointer fs-3 mx-1" onclick="setEditRating(4)"></i>
                <i class="far fa-star text-warning pointer fs-3 mx-1" onclick="setEditRating(5)"></i>
            </div>
            <p class="text-muted small mt-2">Update your star rating and feedback</p>
        </div>
        <div class="mb-0">
            <label class="form-label small fw-bold text-muted text-uppercase">Your Feedback</label>
            <textarea class="form-control border-light-subtle shadow-none" id="editReviewInput" rows="4" required></textarea>
        </div>
      </div>
      <div class="modal-footer border-0 p-4 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold small border shadow-xs" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="saveReviewEdit(this)">Update Review</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    editReviewModal = new bootstrap.Modal(document.getElementById('editReviewModal'));

    // Restore active tab

    // Restore active tab
    let activeTabId = localStorage.getItem('activeAccountTab') || '#dashboard';
    if (!activeTabId.startsWith('#')) activeTabId = '#' + activeTabId;
    const triggerEl = document.querySelector(`button[data-bs-target="${activeTabId}"]`);
    if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();

    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(el => {
        el.addEventListener('shown.bs.tab', event => {
            localStorage.setItem('activeAccountTab', event.target.getAttribute('data-bs-target'));
        });
    });

    // Profile Form Submit
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('api/profile.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showNotification('An error occurred', 'error');
        });
    });

    // Password Form Submit
    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.action = 'change_password';

        fetch('api/profile.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.success) {
                showNotification(data.message, 'success');
                this.reset();
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showNotification('An error occurred', 'error');
        });
    });

    // Address Form Submit
    document.getElementById('new-address-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const isEdit = !!data.id;
        
        const method = isEdit ? 'PUT' : 'POST';

        fetch('api/addresses.php', {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload(); // Simple reload to refresh list
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showNotification('An error occurred', 'error');
        });
    });
});

function showAddAddressForm() { 
    document.getElementById('new-address-form').reset();
    document.getElementById('address_id').value = '';
    document.getElementById('address-form-title').textContent = '<?php echo t('add_new_address'); ?>';
    bootstrap.Collapse.getOrCreateInstance(document.getElementById('add-address-form')).show(); 
    document.getElementById('new_wilaya').focus();
}

function hideAddAddressForm() { 
    bootstrap.Collapse.getOrCreateInstance(document.getElementById('add-address-form')).hide(); 
}

function editAddress(address) {
    // Populate form
    document.getElementById('address_id').value = address.id;
    document.getElementById('new_wilaya').value = address.wilaya;
    document.getElementById('new_daira').value = address.daira;
    document.getElementById('new_commune').value = address.commune;
    document.getElementById('new_postal_code').value = address.postal_code;
    document.getElementById('new_street_address').value = address.street_address;
    document.getElementById('new_phone_number').value = address.phone_number;
    document.getElementById('new_is_default').checked = address.is_default == 1;
    
    document.getElementById('address-form-title').textContent = 'Edit Address';
    bootstrap.Collapse.getOrCreateInstance(document.getElementById('add-address-form')).show();
    document.getElementById('add-address-form').scrollIntoView({behavior: 'smooth'});
}

function deleteAddress(id) {
    if (!confirm('Are you sure you want to delete this address?')) return;
    
    fetch('api/addresses.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            document.querySelector(`.address-item[data-id="${id}"]`).remove();
        } else {
            showNotification(data.error, 'error');
        }
    });
}

function setDefaultAddress(id) {
    fetch('api/addresses.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id, action: 'set_default'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            location.reload();
        } else {
            showNotification(data.error, 'error');
        }
    });
}

function trackOrder(orderId) {
    // In a real app, fetch tracking info via AJAX
    new bootstrap.Modal(document.getElementById('trackingModal')).show();
}

function confirmDeleteAccount() {
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
}

function deleteAccount() {
    const password = document.getElementById('delete-confirm-password').value;
    if (!password) {
        alert('Please enter your password to confirm.');
        return;
    }

    fetch('api/profile.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'delete_account',
            password: password
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php?msg=account_deleted';
        } else {
            alert(data.error || 'Failed to delete account');
        }
    });
}

// Current page for account messages pagination
let currentPageAccount = 1;

// Load conversations for account messages tab with pagination
async function loadAccountConversations(page = 1) {
    try {
        const response = await fetch(`api/messages/conversations.php?page=${page}&limit=10`);
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Invalid server response');
        }

        if (data.success) {
            displayAccountConversations(data.conversations);
            updateAccountPaginationControls(data.pagination);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        document.getElementById('conversationsListAccount').innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle text-warning fs-1 mb-3 d-block"></i>
                <p class="text-muted">Failed to load conversations: ${error.message}</p>
            </div>
        `;
    }
}

// Load conversations for a specific page
async function loadAccountConversationsPage(page) {
    // Validate page number
    if (page < 1 || (window.accountPaginationData && page > window.accountPaginationData.total_pages)) {
        return;
    }

    currentPageAccount = page;
    loadAccountConversations(page);
}

// Update pagination controls for account messages
function updateAccountPaginationControls(pagination) {
    window.accountPaginationData = pagination;
    const container = document.getElementById('paginationControlsAccount');
    const prevBtn = document.getElementById('prevPageAccount');
    const nextBtn = document.getElementById('nextPageAccount');

    // Show pagination controls
    container.classList.remove('d-none');

    // Update previous button state
    if (currentPageAccount <= 1) {
        prevBtn.classList.add('disabled');
    } else {
        prevBtn.classList.remove('disabled');
    }

    // Update next button state
    if (currentPageAccount >= pagination.total_pages) {
        nextBtn.classList.add('disabled');
    } else {
        nextBtn.classList.remove('disabled');
    }

    // Update page numbers in pagination
    const paginationUl = container.querySelector('ul');
    const pageItems = Array.from(paginationUl.children).filter(el =>
        !el.id.includes('prev') && !el.id.includes('next')
    );

    // Remove existing page number items (except prev/next)
    pageItems.forEach(item => item.remove());

    // Calculate visible page range
    const totalPages = pagination.total_pages;
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPageAccount - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    // Adjust startPage if needed to ensure maxVisiblePages are shown
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    // Create page number buttons
    for (let i = startPage; i <= endPage; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = `page-item ${i === currentPageAccount ? 'active' : ''}`;
        pageLi.innerHTML = `
            <a class="page-link" href="#" onclick="loadAccountConversationsPage(${i})">${i}</a>
        `;
        if (nextBtn && nextBtn.parentNode) {
            nextBtn.parentNode.insertBefore(pageLi, nextBtn);
        } else {
            paginationUl.appendChild(pageLi);
        }
    }
}

function displayAccountConversations(conversations) {
    const container = document.getElementById('conversationsListAccount');
    
    if (conversations.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                    <i class="fas fa-comments text-muted fs-2"></i>
                </div>
                <p class="text-muted mb-4">No messages yet</p>
                <button class="btn btn-danger rounded-pill px-5 fw-bold" onclick="toggleChatModal()">
                    Start a Conversation
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = conversations.map(conv => `
        <div class="list-group-item list-group-item-action py-3 ${conv.customer_unread_count > 0 ? 'bg-warning-subtle' : ''}" style="cursor: pointer;" onclick="openConversationFromAccount(${conv.id})">
            <div class="d-flex gap-3">
                ${conv.product_image ? 
                    `<img src="${conv.product_image}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">` :
                    `<div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px;">
                        <i class="fas fa-comment"></i>
                    </div>`
                }
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <strong class="small">${conv.product_name || 'General Inquiry'}</strong>
                        ${conv.customer_unread_count > 0 ? `<span class="badge bg-danger rounded-pill">${conv.customer_unread_count}</span>` : ''}
                    </div>
                    <div class="small text-muted text-truncate">${conv.last_message || 'No messages'}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted x-small">${formatTime(conv.last_message_at)}</small>
                        <span class="badge ${conv.status === 'open' ? 'bg-success' : conv.status === 'pending' ? 'bg-warning' : 'bg-secondary'} x-small">
                            ${conv.status.charAt(0).toUpperCase() + conv.status.slice(1)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function openConversationFromAccount(conversationId) {
    currentConversationId = conversationId;
    document.getElementById('currentConversationId').value = conversationId;
    toggleChatModal();
    setTimeout(() => openConversation(conversationId), 300);
}

// Load conversations when Messages tab is shown
document.querySelector('button[data-bs-target="#messages"]')?.addEventListener('shown.bs.tab', function() {
    loadAccountConversations(1); // Load first page
    loadUnreadCount();
});

// Update message badge on page load
if (typeof loadUnreadCount === 'function') {
    loadUnreadCount();
    setInterval(() => {
        fetch('api/messages/unread_count.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    document.getElementById('accountMessagesBadge').textContent = data.count;
                    document.getElementById('accountMessagesBadge').classList.remove('d-none');
                } else {
                    document.getElementById('accountMessagesBadge').classList.add('d-none');
                }
            });
    }, 30000);
}
<<<<<<< Updated upstream

function openEditReviewModal(review) {
    document.getElementById('editReviewId').value = review.id;
    document.getElementById('editReviewInput').value = review.review_text;
    setEditRating(review.rating);
    editReviewModal.show();
}

function setEditRating(rating) {
    const container = document.getElementById('edit-stars-container');
    container.setAttribute('data-rating', rating);
    const stars = container.querySelectorAll('i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
}

async function saveReviewEdit(btn) {
    const id = document.getElementById('editReviewId').value;
    const rating = document.getElementById('edit-stars-container').getAttribute('data-rating');
    const text = document.getElementById('editReviewInput').value.trim();

    if (!rating) {
        alert('Please select a star rating.');
        return;
    }
    if (!text) {
        alert('Please enter your review text.');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
        const response = await fetch('api/reviews.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                review_id: id,
                rating: parseInt(rating),
                review_text: text
            })
        });
        const data = await response.json();

        if (data.success) {
            showNotification(data.message, 'success');
            editReviewModal.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.error || 'Failed to update review');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        alert('Connection error.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
=======
>>>>>>> Stashed changes
</script>


<?php require_once 'includes/footer.php'; ?>
