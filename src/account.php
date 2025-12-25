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
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#payment" type="button" role="tab">
                        <i class="fas fa-credit-card text-secondary opacity-75" style="width: 20px;"></i> <span>Payment Methods</span>
                    </button>
                    <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center gap-3" data-bs-toggle="pill" data-bs-target="#wishlist" type="button" role="tab">
                        <i class="fas fa-heart text-secondary opacity-75" style="width: 20px;"></i> <span><?php echo t('wishlist'); ?></span>
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
                        <div class="card-body p-4">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                        <i class="fas fa-shopping-bag text-muted fs-2"></i>
                                    </div>
                                    <p class="text-muted mb-4"><?php echo t('no_orders_description'); ?></p>
                                    <a href="products.php" class="btn btn-danger rounded-pill px-5 fw-bold"><?php echo t('start_shopping'); ?></a>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-4">
                                    <?php foreach ($orders as $order): ?>
                                    <div class="card border border-light-subtle rounded-4 overflow-hidden">
                                        <div class="card-header bg-light py-3 px-4 border-bottom-0">
                                            <div class="row g-3 align-items-center">
                                                <div class="col-6 col-sm-3 text-center text-sm-start">
                                                    <span class="d-block text-muted small text-uppercase"><?php echo t('order_number'); ?></span> 
                                                    <strong class="small">#<?php echo $order['order_number']; ?></strong>
                                                </div>
                                                <div class="col-6 col-sm-3 text-center text-sm-start">
                                                    <span class="d-block text-muted small text-uppercase"><?php echo t('order_date'); ?></span> 
                                                    <strong class="small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></strong>
                                                </div>
                                                <div class="col-6 col-sm-3 text-center text-sm-start">
                                                    <span class="d-block text-muted small text-uppercase"><?php echo t('total'); ?></span> 
                                                    <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                                                        <strong class="text-danger"><?php echo format_price($order['total_amount']); ?></strong>
                                                        <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 px-2 py-1" style="font-size: 0.6rem;" title="Discount Applied">
                                                                <i class="fas fa-tag"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-3 text-center text-sm-end">
                                                    <span class="badge rounded-pill <?php 
                                                        echo match($order['status']) {
                                                            'pending' => 'bg-warning text-dark',
                                                            'processing' => 'bg-info',
                                                            'shipped' => 'bg-primary',
                                                            'delivered' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    ?> px-3 py-2 small shadow-sm"><?php echo t($order['status']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-4 border-top">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                                <div class="d-flex gap-2">
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-4 fw-bold"><?php echo t('view'); ?></a>
                                                    <?php if ($order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                                                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold" onclick="trackOrder('<?php echo $order['id']; ?>')"><?php echo t('track_order'); ?></button>
                                                    <?php endif; ?>
                                                </div>
                                                <button class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" onclick="reorder('<?php echo $order['id']; ?>')">Buy Again</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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
                
                <!-- 5. PAYMENT METHODS -->
                <div class="tab-pane fade" id="payment" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h2 class="h5 mb-0 fw-bold">Payment Methods</h2>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="py-5">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                                    <i class="fas fa-credit-card text-secondary fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold">No saved payment methods</h3>
                                <p class="text-muted small mb-4">Save your credit/debit cards for faster checkout.</p>
                                <button class="btn btn-outline-dark rounded-pill px-4 fw-bold" onclick="alert('Feature coming soon!')">Add New Card</button>
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
                
                <!-- 7. REVIEWS -->
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
                                                    <img src="assets/images/products/<?php echo $review['product_image'] ?? 'placeholder.jpg'; ?>" 
                                                         alt="" 
                                                         class="rounded"
                                                         style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                                                    <div class="flex-grow-1">
                                                        <h5 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($review['product_name']); ?></h5>
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Order Tracking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="tracking-timeline position-relative ps-4 border-start border-2 border-light ms-2">
            <div class="mb-4 position-relative">
                <div class="position-absolute start-0 translate-middle-x bg-success rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                <div class="fw-bold text-success">Order Delivered</div>
                <div class="small text-muted">Oct 24, 2025 - 2:30 PM</div>
            </div>
            <div class="mb-4 position-relative">
                <div class="position-absolute start-0 translate-middle-x bg-success rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                <div class="fw-bold">Out for Delivery</div>
                <div class="small text-muted">Oct 24, 2025 - 8:00 AM</div>
            </div>
            <div class="mb-4 position-relative">
                <div class="position-absolute start-0 translate-middle-x bg-success rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                <div class="fw-bold">Shipped</div>
                <div class="small text-muted">Oct 22, 2025 - 5:00 PM</div>
            </div>
            <div class="position-relative">
                <div class="position-absolute start-0 translate-middle-x bg-success rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                <div class="fw-bold">Order Placed</div>
                <div class="small text-muted">Oct 20, 2025 - 10:00 AM</div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

function reorder(orderId) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

    fetch('api/reorder.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({order_id: orderId})
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.success) {
            showNotification(data.message, 'success');
            updateCartBadge(); // Global function expected
            setTimeout(() => window.location.href = 'cart.php', 500);
        } else {
            showNotification(data.error || 'Failed to reorder', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showNotification('Connection error', 'error');
    });
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
</script>


<?php require_once 'includes/footer.php'; ?>
