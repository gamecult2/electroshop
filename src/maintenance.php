<?php
require_once 'includes/functions.php';
require_once 'db_connect.php';

// If maintenance mode is disabled, redirect to home
if (get_setting('maintenance_mode') !== '1') {
    header('Location: index.php');
    exit;
}

$site_title = get_setting('site_title', 'Online Store');
$site_logo = get_setting('site_logo');
$logo_height = get_setting('site_logo_height', '60');
$primaryColor = get_setting('logo_primary_color', '#6c757d');
$secondaryColor = get_setting('logo_secondary_color', '#dc3545');
$logoFontSize = get_setting('site_logo_font_size', '28');

// Logic to split the title for two-tone coloring (same as header)
if (strpos($site_title, ' ') !== false) {
    $parts = explode(' ', $site_title, 2);
    $part1 = $parts[0];
    $part2 = $parts[1];
} else {
    $len = strlen($site_title);
    $mid = ceil($len / 2);
    $splitPos = $mid;
    for ($i = 1; $i < $len; $i++) {
        if (ctype_upper($site_title[$i])) {
            $splitPos = $i;
            break;
        }
    }
    $part1 = substr($site_title, 0, $splitPos);
    $part2 = substr($site_title, $splitPos);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - <?php echo htmlspecialchars($site_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-card {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #fff5f5;
            color: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        .site-brand {
            text-decoration: none;
            font-weight: bold;
            display: block;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="maintenance-card text-center">
            <!-- Site Branding -->
            <div class="mb-4">
                <?php if ($site_logo): ?>
                    <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_title); ?>" style="max-height: <?php echo $logo_height; ?>px;">
                <?php else: ?>
                    <div class="site-brand" style="font-size: <?php echo $logoFontSize; ?>px;">
                        <span style="color: <?php echo $primaryColor; ?>;"><?php echo htmlspecialchars($part1); ?></span><span style="color: <?php echo $secondaryColor; ?>;"><?php echo htmlspecialchars($part2); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="icon-box">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="h3 fw-bold mb-3">Under Maintenance</h1>
            <p class="text-muted mb-4">We're currently performing some scheduled maintenance. We'll be back online shortly!</p>
            <hr class="my-4 opacity-50">
            <p class="small text-muted mb-0">Thank you for your patience.</p>
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <div class="mt-4">
                    <a href="admin/dashboard.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">Go to Admin Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>