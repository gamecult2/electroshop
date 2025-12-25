<?php
// src/admin/dashboard.php - Comprehensive Admin Dashboard
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Order.php';
require_once '../models/Product.php';
require_once '../models/User.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle AJAX Status Update
if (isset($_GET['ajax']) && $_GET['ajax'] === 'update_order_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $orderId = (int)$_POST['order_id'];
    $status = sanitize_input($_POST['status']);
    
    $orderModel = new Order();
    if ($orderModel->updateStatus($orderId, $status)) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
    }
    exit;
}

// Date range selection
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// --- 1. Sales & Revenue Analytics ---
// Total Revenue in period
$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Revenue comparison (previous period)
$diff = (strtotime($endDate) - strtotime($startDate));
$prevStartDate = date('Y-m-d', strtotime($startDate) - $diff - 86400);
$prevEndDate = date('Y-m-d', strtotime($startDate) - 86400);

$stmt->execute([$prevStartDate, $prevEndDate]);
$prevRevenue = $stmt->fetchColumn() ?: 0;
$revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 100;

// Average Order Value
$stmt = $pdo->prepare("SELECT AVG(total_amount) FROM orders WHERE status != 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$avgOrderValue = $stmt->fetchColumn() ?: 0;

// Sales Charts Data (Daily Trends)
$stmt = $pdo->prepare("SELECT DATE(created_at) as date, SUM(total_amount) as amount FROM orders WHERE status != 'cancelled' AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date ASC");
$stmt->execute([$startDate, $endDate]);
$dailySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Revenue by Product Category
$stmt = $pdo->prepare("
    SELECT c.name_en as category, SUM(oi.total_price) as revenue 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    JOIN products p ON oi.product_id = p.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE o.status != 'cancelled' AND DATE(o.created_at) BETWEEN ? AND ? 
    GROUP BY c.id 
    ORDER BY revenue DESC 
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$revenueByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 2. Order Management ---
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$orderStatusBreakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT o.*, c.first_name, c.last_name, c.email FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC LIMIT 10");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Inventory Management ---
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_quantity AND stock_quantity > 0");
$lowStockCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0");
$outOfStockCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProductsCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(price * stock_quantity) FROM products");
$inventoryWorth = $stmt->fetchColumn() ?: 0;

// --- 4. Customer Analytics ---
$stmt = $pdo->query("SELECT COUNT(*) FROM customers");
$totalCustomers = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$newCustomersToday = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$newCustomersWeek = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$newCustomersMonth = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT c.id, c.first_name, c.last_name, c.email, SUM(o.total_amount) as total_spent 
    FROM customers c 
    JOIN orders o ON c.id = o.customer_id 
    WHERE o.status = 'delivered' 
    GROUP BY c.id 
    ORDER BY total_spent DESC 
    LIMIT 5
");
$topCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 5. Performance Metrics ---
// Conversion Rate (Total Orders / Total Product Views)
$stmt = $pdo->query("SELECT SUM(views_count) FROM products");
$totalProductViews = $stmt->fetchColumn() ?: 1;
$stmt2 = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrdersAllTime = $stmt2->fetchColumn();
$conversionRate = ($totalOrdersAllTime / $totalProductViews) * 100;

$stmt = $pdo->query("
    SELECT p.name_en, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 10
");
$topSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 6. Financial Overview ---
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'pending'");
$pendingPayments = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'refunded'");
$refundRequestsCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT payment_method, COUNT(*) as count FROM orders GROUP BY payment_method");
$paymentMethodsDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Activity Feed ---
$stmt = $pdo->query("
    (SELECT 'order' as type, created_at, CONCAT('Order #', order_number, ' placed by customer') as activity FROM orders)
    UNION
    (SELECT 'customer' as type, created_at, CONCAT('New customer registered: ', email) as activity FROM customers)
    UNION
    (SELECT 'review' as type, created_at, CONCAT('New product review submitted') as activity FROM reviews)
    UNION
    (SELECT 'stock' as type, NOW() as created_at, CONCAT(name_en, ': Low stock alert (', stock_quantity, ' left)') as activity FROM products WHERE stock_quantity <= min_stock_quantity)
    ORDER BY created_at DESC LIMIT 15
");
$activityFeed = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Set page title and heading for the template
$page_title = 'Dashboard';
$page_heading = 'Admin Dashboard';
include 'header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">




<div class="card border-0 shadow-sm p-3 mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <span class="small fw-bold text-muted text-uppercase"><i class="fas fa-calendar-alt me-1"></i> Period:</span>
        </div>
        <div class="col-auto">
            <input type="text" id="date-range" class="form-control form-control-sm border-light-subtle shadow-none" placeholder="Select date range" value="<?php echo "$startDate to $endDate"; ?>" style="width: 200px;">
        </div>
        <div class="col-auto">
            <button class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" onclick="applyDateFilter()">Filter</button>
        </div>
        <div class="col text-end">
            <div class="d-flex gap-2 justify-content-end flex-wrap">
                <a href="add_product.php" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2"><i class="fas fa-plus"></i> Add Product</a>
                <a href="orders.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2"><i class="fas fa-tasks"></i> Process Orders</a>
                <a href="export_dashboard.php?type=csv&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2"><i class="fas fa-file-export"></i> Export Report</a>
            </div>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
    <!-- Revenue & Sales -->
    <div class="col">
        <div class="card h-100 border-0 border-start border-danger border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Total Revenue</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo format_price($totalRevenue); ?></div>
            <div class="x-small fw-bold <?php echo $revenueGrowth >= 0 ? 'text-success' : 'text-danger'; ?>">
                <i class="fas fa-arrow-<?php echo $revenueGrowth >= 0 ? 'up' : 'down'; ?> me-1"></i> 
                <?php echo number_format(abs($revenueGrowth), 1); ?>% vs prev. period
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 border-start border-primary border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Avg. Order Value</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo format_price($avgOrderValue); ?></div>
            <div class="x-small text-muted fw-bold">Across period</div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 border-start border-success border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Inventory Worth</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo format_price($inventoryWorth); ?></div>
            <div class="x-small text-muted fw-bold"><?php echo $totalProductsCount; ?> products</div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="col">
        <div class="card h-100 border-0 border-start border-warning border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Low Stock Alerts</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo $lowStockCount; ?></div>
            <div class="x-small text-danger fw-bold"><?php echo $outOfStockCount; ?> out of stock</div>
        </div>
    </div>
    
    <!-- Customers -->
    <div class="col">
        <div class="card h-100 border-0 border-start border-danger border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Total Customers</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo $totalCustomers; ?></div>
            <div class="x-small text-success fw-bold">
                +<?php echo $newCustomersMonth; ?> this month
            </div>
        </div>
    </div>

    <!-- Performance -->
    <div class="col">
        <div class="card h-100 border-0 border-start border-primary border-4 shadow-sm p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-2">Store Conv.</h6>
            <div class="h4 fw-bold text-dark mb-1"><?php echo number_format($conversionRate, 2); ?>%</div>
            <div class="x-small text-muted fw-bold">Views vs Orders</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="h5 fw-bold mb-4 text-dark"><i class="fas fa-chart-line me-2 text-danger"></i> Sales Trend</div>
            <canvas id="salesChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 mb-4 h-100">
            <div class="h5 fw-bold mb-4 text-dark"><i class="fas fa-chart-pie me-2 text-primary"></i> Revenue by Category</div>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-shopping-bag me-2 text-success"></i> Recent Orders</h2>
                <a href="orders.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Order #</th>
                            <th class="border-0">Customer</th>
                            <th class="border-0">Total</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong class="text-dark">#<?php echo $order['order_number']; ?></strong></td>
                            <td class="text-muted fw-medium"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td class="fw-bold text-danger"><?php echo format_price($order['total_amount']); ?></td>
                            <td>
                                <select class="form-select form-select-sm rounded-pill fw-bold text-uppercase px-3 shadow-none <?php 
                                    echo match($order['status']) {
                                        'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                        'processing' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                        'shipped' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                        'delivered' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                        'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                        default => 'bg-secondary-subtle'
                                    };
                                ?>" onchange="updateStatus(this, <?php echo $order['id']; ?>, this.value)" style="font-size: 10px; width: 120px;">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light rounded-circle text-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h2 class="h5 fw-bold mb-4 text-dark"><i class="fas fa-stream me-2 text-warning"></i> Live Activity</h2>
            <div class="list-group list-group-flush overflow-auto shadow-none" style="max-height: 380px;">
                <?php foreach ($activityFeed as $item): ?>
                <div class="list-group-item d-flex align-items-center gap-3 border-0 px-0 py-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm <?php 
                        echo $item['type'] == 'order' ? 'bg-primary' : ($item['type'] == 'customer' ? 'bg-success' : ($item['type'] == 'review' ? 'bg-warning' : 'bg-danger')); 
                    ?>" style="width: 36px; height: 36px; font-size: 14px;">
                        <i class="fas fa-<?php echo $item['type'] == 'order' ? 'shopping-cart' : ($item['type'] == 'customer' ? 'user-plus' : ($item['type'] == 'review' ? 'comment' : 'exclamation-triangle')); ?>"></i>
                    </div>
                    <div>
                        <div class="small fw-bold text-dark mb-0"><?php echo htmlspecialchars($item['activity']); ?></div>
                        <small class="text-muted x-small"><?php echo date('M d, H:i', strtotime($item['created_at'])); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 mb-4 h-100">
            <h2 class="h5 fw-bold mb-4 text-dark"><i class="fas fa-file-invoice-dollar me-2 text-info"></i> Financial Overview</h2>
            <div class="p-0">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span class="small fw-bold text-muted text-uppercase x-small">Pending Payments</span>
                    <span class="fw-bold text-danger"><?php echo format_price($pendingPayments); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span class="small fw-bold text-muted text-uppercase x-small">Refunded Orders</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3"><?php echo $refundRequestsCount; ?> Items</span>
                </div>
                <hr class="my-4 opacity-10">
                <h6 class="fw-bold text-muted text-uppercase x-small mb-3">Payment Methods</h6>
                <?php foreach ($paymentMethodsDistribution as $pm): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold text-dark"><?php echo strtoupper($pm['payment_method'] ?: 'Other'); ?></small>
                        <span class="x-small fw-bold text-primary"><?php echo $pm['count']; ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar rounded-pill" role="progressbar" style="width: <?php echo ($pm['count'] / array_sum(array_column($paymentMethodsDistribution, 'count'))) * 100; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 mb-4 h-100">
            <h2 class="h5 fw-bold mb-4 text-dark"><i class="fas fa-trophy me-2 text-danger"></i> Top Selling Products</h2>
            <div class="list-group list-group-flush shadow-none mb-0">
                <?php foreach ($topSellingProducts as $p): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-2 border-bottom-dashed">
                    <span class="small text-dark fw-bold"><?php echo htmlspecialchars($p['name_en']); ?></span>
                    <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill small px-3"><?php echo $p['total_sold']; ?> sold</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Initialize Date Range Picker
    flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d"
    });

    function applyDateFilter() {
        const range = document.getElementById('date-range').value;
        if (range.includes(' to ')) {
            const parts = range.split(' to ');
            window.location.href = `dashboard.php?start_date=${parts[0]}&end_date=${parts[1]}`;
        }
    }

    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($dailySales, 'date')); ?>,
            datasets: [{
                label: 'Daily Sales',
                data: <?php echo json_encode(array_column($dailySales, 'amount')); ?>,
                borderColor: '#e4393c',
                backgroundColor: 'rgba(228, 57, 60, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#e4393c'
            }]
        },
        options: {
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    padding: 12,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.05)' }
                }, 
                x: { grid: { display: false } } 
            }
        }
    });

    // Category Distribution Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($revenueByCategory, 'category')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($revenueByCategory, 'revenue')); ?>,
                backgroundColor: ['#e4393c', '#007bff', '#28a745', '#ffc107', '#17a2b8'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, weight: 'bold' }
                    }
                } 
            }
        }
    });

    // AJAX Order Status Update
    function updateStatus(selectElement, orderId, status) {
        const bgClasses = {
            'pending': 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            'processing': 'bg-info-subtle text-info-emphasis border-info-subtle',
            'shipped': 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
            'delivered': 'bg-success-subtle text-success-emphasis border-success-subtle',
            'cancelled': 'bg-danger-subtle text-danger-emphasis border-danger-subtle'
        };
        
        // Remove previous status classes
        selectElement.className = 'form-select form-select-sm rounded-pill fw-bold text-uppercase px-3 shadow-none';
        
        // Add current status classes
        if (bgClasses[status]) {
            bgClasses[status].split(' ').forEach(c => selectElement.classList.add(c));
        }

        let formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);

        fetch('dashboard.php?ajax=update_order_status', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message);
        })
        .catch(err => {
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    function showToast(msg, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
        } else {
            console.log('Toast:', msg);
        }
    }
</script>

<?php include 'footer.php'; ?>
