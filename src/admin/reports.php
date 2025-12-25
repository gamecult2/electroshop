<?php
// admin/reports.php - Sales and analytics reports page

session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['admin_role'] !== 'admin') {
    die('Unauthorized: Only administrators can view reports.');
}

$reportType = $_GET['type'] ?? 'sales';
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d');     // Today

// Sales Report
if ($reportType === 'sales') {
    $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_sales
            FROM orders 
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $dailySales = $stmt->fetchAll();
    
    // Summary statistics
    $summarySql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value,
                    COUNT(DISTINCT customer_id) as unique_customers
                   FROM orders 
                   WHERE created_at BETWEEN ? AND ?";
    
    $summaryStmt = $pdo->prepare($summarySql);
    $summaryStmt->execute([$startDate, $endDate . ' 23:59:59']);
    $summary = $summaryStmt->fetch();
}

// Top Products Report
if ($reportType === 'products') {
    $sql = "SELECT 
                p.name_en,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as total_revenue,
                p.stock_quantity
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY oi.product_id
            ORDER BY total_sold DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $topProducts = $stmt->fetchAll();
}

// Top Categories Report
if ($reportType === 'categories') {
    $sql = "SELECT 
                c.name_en,
                COUNT(oi.id) as items_sold,
                SUM(oi.total_price) as revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY c.id
            ORDER BY revenue DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $topCategories = $stmt->fetchAll();
}

$page_title = 'Reports & Analytics';
$page_heading = 'Business Intelligence';

// Include the shared header template
include 'header.php';
?>

            <!-- Top Filter & Navigation Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($reportType); ?>">
                        
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Start Date</label>
                            <div class="input-group border-light-subtle">
                                <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-calendar-alt small"></i></span>
                                <input type="date" name="start_date" class="form-control border-light-subtle shadow-none" value="<?php echo $startDate; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">End Date</label>
                            <div class="input-group border-light-subtle">
                                <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-calendar-check small"></i></span>
                                <input type="date" name="end_date" class="form-control border-light-subtle shadow-none" value="<?php echo $endDate; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Report Category</label>
                            <div class="nav nav-pills bg-light p-1 rounded-3 border border-light-subtle" id="reportNav" role="tablist">
                                <a href="?type=sales&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                                   class="nav-link flex-fill text-center rounded-2 py-2 fw-bold small <?php echo $reportType === 'sales' ? 'active bg-primary shadow-sm' : 'text-muted'; ?>">
                                   <i class="fas fa-chart-line me-1"></i> Sales
                                </a>
                                <a href="?type=products&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                                   class="nav-link flex-fill text-center rounded-2 py-2 fw-bold small <?php echo $reportType === 'products' ? 'active bg-primary shadow-sm' : 'text-muted'; ?>">
                                   <i class="fas fa-box me-1"></i> Products
                                </a>
                                <a href="?type=categories&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                                   class="nav-link flex-fill text-center rounded-2 py-2 fw-bold small <?php echo $reportType === 'categories' ? 'active bg-primary shadow-sm' : 'text-muted'; ?>">
                                   <i class="fas fa-tags me-1"></i> Categories
                                </a>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-none">
                                <i class="fas fa-sync-alt me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($reportType === 'sales'): ?>
                <!-- Sales Summary Metrics -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm card-hover transition translate-up">
                            <div class="card-body p-4 border-start border-primary border-4 rounded-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-bold small text-uppercase mb-1">Total Orders</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo number_format($summary['total_orders']); ?></h3>
                                    </div>
                                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                                        <i class="fas fa-shopping-basket fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm card-hover transition translate-up">
                            <div class="card-body p-4 border-start border-success border-4 rounded-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-bold small text-uppercase mb-1">Total Revenue</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo format_price($summary['total_revenue']); ?></h3>
                                    </div>
                                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                                        <i class="fas fa-dollar-sign fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm card-hover transition translate-up">
                            <div class="card-body p-4 border-start border-info border-4 rounded-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-bold small text-uppercase mb-1">Avg. Order</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo format_price($summary['avg_order_value']); ?></h3>
                                    </div>
                                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                                        <i class="fas fa-calculator fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm card-hover transition translate-up">
                            <div class="card-body p-4 border-start border-warning border-4 rounded-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-bold small text-uppercase mb-1">Customers</h6>
                                        <h3 class="mb-0 fw-bold"><?php echo number_format($summary['unique_customers']); ?></h3>
                                    </div>
                                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                                        <i class="fas fa-user-friends fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Revenue Trends Chart -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-chart-area me-2 text-primary"></i> Revenue Trajectory</h5>
                                <span class="badge bg-light text-primary border border-light-subtle rounded-pill fw-bold x-small">DAILY SALES DATA</span>
                            </div>
                            <div class="card-body p-4">
                                <canvas id="salesChart" height="280"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Daily Breakdown Table -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-list-alt me-2 text-primary"></i> Daily Performance</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-3 py-2 small fw-bold text-muted text-uppercase">Date</th>
                                                <th class="border-0 py-2 small fw-bold text-muted text-uppercase text-center">Qty</th>
                                                <th class="border-0 px-3 py-2 small fw-bold text-muted text-uppercase text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($dailySales, 0, 8) as $day): ?>
                                                <tr>
                                                    <td class="px-3 border-light-subtle"><div class="fw-bold small"><?php echo date('M j', strtotime($day['date'])); ?></div></td>
                                                    <td class="text-center border-light-subtle"><span class="badge bg-light text-dark border border-light-subtle rounded-pill fw-bold x-small"><?php echo $day['order_count']; ?></span></td>
                                                    <td class="px-3 text-end fw-bold text-primary small border-light-subtle"><?php echo format_price($day['total_sales']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($dailySales) > 8): ?>
                                    <div class="p-3 text-center border-top border-light-subtle">
                                        <button class="btn btn-link py-0 text-muted small text-uppercase fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#detailedDailyModal">View All Daily Data <i class="fas fa-chevron-right ms-1"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    const salesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [
                                <?php foreach (array_reverse($dailySales) as $day): ?>
                                    '<?php echo date('M j', strtotime($day['date'])); ?>',
                                <?php endforeach; ?>
                            ],
                            datasets: [{
                                label: 'Revenue',
                                data: [
                                    <?php foreach (array_reverse($dailySales) as $day): ?>
                                        <?php echo $day['total_sales']; ?>,
                                    <?php endforeach; ?>
                                ],
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.05)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#0d6efd',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { borderDash: [5, 5], color: '#f0f0f0' },
                                    ticks: { font: { size: 11 }, callback: function(value) { return '$' + value; } }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            }
                        }
                    });
                });
                </script>

            <?php elseif ($reportType === 'products'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-star me-2 text-warning"></i> Best Selling Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase">Product Title</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Units Sold</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Total Revenue</th>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-center">Inventory</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProducts as $product): ?>
                                        <tr>
                                            <td class="px-4">
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['name_en']); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold x-small"><?php echo number_format($product['total_sold']); ?> sold</span>
                                            </td>
                                            <td class="fw-bold text-success small"><?php echo format_price($product['total_revenue']); ?></td>
                                            <td class="px-4 text-center">
                                                <?php if ($product['stock_quantity'] <= 5): ?>
                                                    <span class="badge bg-danger rounded-pill px-2 py-1 x-small fw-bold shadow-xs"><i class="fas fa-exclamation-triangle me-1"></i> <?php echo $product['stock_quantity']; ?> left</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2 py-1 x-small fw-bold"><?php echo $product['stock_quantity']; ?> in stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($reportType === 'categories'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-folder-open me-2 text-info"></i> Top Performance by Category</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase">Category Name</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Catalog Items Sold</th>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Accumulated Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCategories as $category): ?>
                                        <tr>
                                            <td class="px-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-info-subtle text-info p-2 rounded-3 me-3"><i class="fas fa-tag small"></i></div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($category['name_en']); ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-3 py-1 fw-bold x-small"><?php echo number_format($category['items_sold']); ?> units</span>
                                            </td>
                                            <td class="px-4 text-end">
                                                <div class="fw-bold text-primary"><?php echo format_price($category['revenue']); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if ($reportType === 'sales' && count($dailySales) > 8): ?>
    <!-- Modal for Detailed Daily Data -->
    <div class="modal fade" id="detailedDailyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light py-3">
                    <h5 class="modal-title fw-bold small text-uppercase text-muted"><i class="fas fa-calendar-alt me-2 text-primary"></i> Full Daily Breakdown</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white sticky-top shadow-xs">
                            <tr>
                                <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase">Full Date</th>
                                <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Orders</th>
                                <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailySales as $day): ?>
                                <tr>
                                    <td class="px-4 border-light-subtle"><div class="fw-bold small"><?php echo date('M j, Y', strtotime($day['date'])); ?></div></td>
                                    <td class="text-center border-light-subtle"><span class="badge bg-light text-dark border border-light-subtle rounded-pill px-3 fw-bold x-small"><?php echo $day['order_count']; ?></span></td>
                                    <td class="px-4 text-end fw-bold text-primary small border-light-subtle"><?php echo format_price($day['total_sales']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

