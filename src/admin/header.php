<?php
// admin/header.php
// Shared header and sidebar for admin pages

// Fetch unread messages
require_once '../db_connect.php';
$unreadMessagesStmt = $pdo->query("SELECT * FROM contact_messages WHERE is_resolved = 0 ORDER BY created_at DESC LIMIT 5");
$unreadMessages = $unreadMessagesStmt->fetchAll();
$unreadMessagesCount = count($unreadMessages);

// Fetch new orders (pending) from today only
$newOrdersStmt = $pdo->query("SELECT * FROM orders WHERE status = 'pending' AND DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 5");
$newOrders = $newOrdersStmt->fetchAll();
$newOrdersCount = count($newOrders);

require_once __DIR__ . '/includes/ui.php';
require __DIR__ . '/includes/pages.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - <?php echo htmlspecialchars(get_setting('site_title', 'QwenShop')); ?></title>
    <!-- Bootstrap 5 -->
    <?php 
    $bsTheme = get_setting('bootstrap_theme', 'online');
    if ($bsTheme === 'online'): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php else: ?>
        <link href="../assets/css/themes/<?php echo htmlspecialchars($bsTheme); ?>/bootstrap.css" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/admin.js"></script>

<?php include __DIR__ . '/includes/theme.php'; ?>
</head>
<body class="bg-light admin-app">
    <a class="skip-link" href="#admin-main">Skip to content</a>
    <button type="button" class="sidebar-overlay" onclick="toggleSidebar()" tabindex="-1" aria-label="Close navigation"></button>
    <div class="container-fluid p-0 d-flex min-vh-100">
        <!-- Sidebar -->
        <div id="adminSidebar" class="bg-dark text-white d-flex flex-column flex-shrink-0 p-3" role="navigation" aria-label="Admin navigation" style="width: 250px; position: sticky; top: 0; height: 100vh; overflow-y: auto;">
            <button type="button" class="btn btn-outline-light d-lg-none mb-3" onclick="toggleSidebar()">Close navigation</button>
            <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <?php 
                $siteLogo = get_setting('site_logo');
                $logoHeight = get_setting('site_logo_height', '40');
                if ($siteLogo): ?>
                    <img src="../<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo" style="max-height: <?php echo $logoHeight; ?>px;" class="me-2">
                    <span class="fs-4 fw-bold">Admin</span>
                <?php else: 
                    $siteTitle = get_setting('site_title', 'GameCult');
                    $primaryColor = get_setting('logo_primary_color', '#6c757d');
                    $secondaryColor = get_setting('logo_secondary_color', '#dc3545');
                    
                    if (strpos($siteTitle, ' ') !== false) {
                        $parts = explode(' ', $siteTitle, 2);
                        $part1 = $parts[0];
                        $part2 = $parts[1];
                    } else {
                        $len = strlen($siteTitle);
                        $mid = ceil($len / 2);
                        $splitPos = $mid;
                        for ($i = 1; $i < $len; $i++) {
                            if (ctype_upper($siteTitle[$i])) {
                                $splitPos = $i;
                                break;
                            }
                        }
                        $part1 = substr($siteTitle, 0, $splitPos);
                        $part2 = substr($siteTitle, $splitPos);
                    }
                ?>
                    <span class="fs-4 fw-bold"><span style="color: <?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($part1); ?></span><span style="color: <?php echo $secondaryColor; ?>;"><?php echo htmlspecialchars($part2); ?></span> <span class="text-white opacity-50">Admin</span></span>
                <?php endif; ?>
            </a>
            <hr class="bg-secondary">
            <ul class="nav nav-pills flex-column mb-auto">
                <?php foreach ($menu_items as $menu_link => $item): ?>
                    <li class="nav-item">
                        <a href="<?php echo $menu_link; ?>" <?= $active_page === $menu_link ? 'aria-current="page"' : '' ?> class="nav-link text-white <?php echo $active_page === $menu_link ? 'active' : ''; ?> py-2 px-3 mb-1">
                            <i class="<?php echo $item['icon']; ?> me-2" style="width: 20px; text-align: center;"></i>
                            <span><?php echo $item['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr class="bg-secondary">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fs-4 me-2"></i>
                    <strong>Admin</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div id="mainContent" class="flex-grow-1 overflow-auto bg-light">
            <!-- Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm sticky-top">
                <div class="container-fluid p-0">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-light border shadow-xs rounded-circle d-flex align-items-center justify-content-center" id="toggle-sidebar-btn" onclick="toggleSidebar()" aria-controls="adminSidebar" aria-expanded="false" aria-label="Toggle admin navigation" style="width: 40px; height: 40px;">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="h4 mb-0 fw-bold text-dark"><?php echo isset($page_heading) ? $page_heading : 'Admin Panel'; ?></h1>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 gap-md-3">
                        <!-- Messages Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light border shadow-xs rounded-circle d-flex align-items-center justify-content-center position-relative" type="button" data-bs-toggle="dropdown" aria-label="Open recent messages" style="width: 40px; height: 40px;">
                                <i class="fas fa-envelope text-muted"></i>
                                <?php if ($unreadMessagesCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" >
                                        <?php echo $unreadMessagesCount; ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 mt-2 rounded-4 overflow-hidden admin-dropdown-menu">
                                <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Recent Messages</h6>
                                    <span class="badge bg-danger rounded-pill"><?php echo $unreadMessagesCount; ?> New</span>
                                </div>
                                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                    <?php if ($unreadMessagesCount > 0): ?>
                                        <?php foreach ($unreadMessages as $msg): ?>
                                            <a href="contact_messages.php" class="list-group-item list-group-item-action p-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                        <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="fw-bold small text-dark"><?php echo htmlspecialchars($msg['name']); ?></span>
                                                            <span class="text-muted x-small"><?php echo time_elapsed_string($msg['created_at']); ?></span>
                                                        </div>
                                                        <div class="text-muted x-small text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted small">No new messages</div>
                                    <?php endif; ?>
                                </div>
                                <a href="messages.php" class="dropdown-item text-center py-2 bg-light text-primary fw-bold small border-top">View All Messages</a>
                            </div>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light border shadow-xs rounded-circle d-flex align-items-center justify-content-center position-relative" type="button" data-bs-toggle="dropdown" aria-label="Open order notifications" style="width: 40px; height: 40px;">
                                <i class="fas fa-bell text-muted"></i>
                                <?php if ($newOrdersCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-white" >
                                        <?php echo $newOrdersCount; ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 mt-2 rounded-4 overflow-hidden admin-dropdown-menu">
                                <div class="bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                                    <?php if ($newOrdersCount > 0): ?>
                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill small"><?php echo $newOrdersCount; ?> New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                    <?php if ($newOrdersCount > 0): ?>
                                        <?php foreach ($newOrders as $nOrder): ?>
                                            <a href="admin_order_details.php?id=<?php echo $nOrder['id']; ?>" class="list-group-item list-group-item-action p-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-success-subtle text-success-emphasis rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold small text-dark">New Order Received</div>
                                                        <div class="text-muted x-small">Order #<?php echo htmlspecialchars($nOrder['order_number']); ?> placed</div>
                                                        <div class="text-muted x-small mt-1"><?php echo time_elapsed_string($nOrder['created_at']); ?></div>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted small">No new notifications</div>
                                    <?php endif; ?>
                                </div>
                                <a href="orders.php" class="dropdown-item text-center py-2 bg-light text-muted fw-bold small border-top">Show All Orders</a>
                            </div>
                        </div>

                        <div class="vr mx-1 opacity-10"></div>

                        <a href="../index.php" class="btn btn-outline-secondary btn-sm px-3" target="_blank" rel="noopener" aria-label="Open storefront in a new tab">
                            <i class="fas fa-external-link-alt me-xl-1"></i> <span class="d-none d-xl-inline">Store</span>
                        </a>
                        <form method="POST" action="logout.php" class="m-0">
                            <button type="submit" class="btn btn-outline-secondary btn-sm px-3" aria-label="Sign out">
                                <i class="fas fa-sign-out-alt me-xl-1"></i> <span class="d-none d-xl-inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main id="admin-main" class="admin-page-content p-4" tabindex="-1">
