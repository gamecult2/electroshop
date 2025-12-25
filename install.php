<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$messageType = '';

// Requirements Check
$requirements = [
    'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'JSON Extension' => extension_loaded('json'),
    'GD Extension' => extension_loaded('gd'),
    'MBString Extension' => extension_loaded('mbstring'),
    'Writable src/ Directory' => is_writable('src/'),
    'Writable src/config.php' => is_writable('src/config.php'),
];

$allRequirementsMet = !in_array(false, $requirements, true);

if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $name = $_POST['db_name'];

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Store in session for next step
        $_SESSION['db_config'] = [
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'name' => $name
        ];
        
        header('Location: ?step=3');
        exit;
    } catch (PDOException $e) {
        $message = "Connection failed: " . $e->getMessage();
        $messageType = 'error';
    }
}

if ($step == 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['db_config'])) {
        header('Location: ?step=2');
        exit;
    }

    $db_config = $_SESSION['db_config'];
    
    try {
        $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['name']}", $db_config['user'], $db_config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Import SQL from DB.sql
        $sqlFile = 'DB.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Remove comments and execute queries
            // Basic SQL split by semicolon (careful with semicolons in strings, but for standard schema it's okay)
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
            
            // Update src/config.php with new credentials
            $configFile = 'src/config.php';
            if (file_exists($configFile)) {
                $configContent = file_get_contents($configFile);
                
                $configContent = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '{$db_config['host']}');", $configContent);
                $configContent = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '{$db_config['user']}');", $configContent);
                $configContent = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '{$db_config['pass']}');", $configContent);
                $configContent = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '{$db_config['name']}');", $configContent);
                
                file_put_contents($configFile, $configContent);
            }
            
            // Create default admin user if admin_users table exists and is empty
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'admin_users'")->rowCount();
            if ($tableCheck > 0) {
                $adminCheck = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
                if ($adminCheck == 0) {
                    $password = password_hash('admin123', PASSWORD_DEFAULT);
                    $pdo->prepare("INSERT INTO admin_users (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)")
                        ->execute(['admin@qwenshop.dz', $password, 'Super', 'Admin', 'admin']);
                }
            }

            header('Location: ?step=4');
            exit;
        } else {
            $message = "SQL file not found at DB.sql";
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = "Setup failed: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install GameCult - Modern E-commerce</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bs-primary: #e4393c;
            --bs-primary-rgb: 228, 57, 60;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #334155;
        }
        .install-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        .step-pill {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        .step-item.active .step-pill {
            background: var(--bs-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.3);
        }
        .step-item.completed .step-pill {
            background: #22c55e;
            color: white;
        }
        .step-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #94a3b8;
        }
        .step-item.active .step-label {
            color: #1e293b;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(var(--bs-primary-rgb), 0.5);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
        }
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            border-radius: 50rem;
        }
        .btn-primary:hover {
            background-color: #c42a2d;
            border-color: #c42a2d;
        }
        .progress {
            height: 6px;
            background-color: #f1f5f9;
            border-radius: 50rem;
        }
        .logo-box {
            width: 60px;
            height: 60px;
            background: var(--bs-primary);
            color: white;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 16px rgba(228, 57, 60, 0.2);
        }
        .requirement-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }
        .requirement-item:hover {
            background: #fff;
            border-color: #e2e8f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="logo-box">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2 class="fw-800 text-dark mb-1">GameCult Installer</h2>
                    <p class="text-muted small">Complete the steps to launch your store</p>
                </div>

                <div class="card install-card overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <!-- Step Indicators -->
                        <div class="row text-center mb-5 g-0">
                            <div class="col step-item <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                                <div class="step-pill mx-auto">
                                    <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                                </div>
                                <div class="step-label">Requirements</div>
                            </div>
                            <div class="col step-item <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                                <div class="step-pill mx-auto">
                                    <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                                </div>
                                <div class="step-label">Database</div>
                            </div>
                            <div class="col step-item <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                                <div class="step-pill mx-auto">
                                    <?php echo $step > 3 ? '<i class="fas fa-check"></i>' : '3'; ?>
                                </div>
                                <div class="step-label">Setup</div>
                            </div>
                            <div class="col step-item <?php echo $step >= 4 ? 'active' : ''; ?>">
                                <div class="step-pill mx-auto">4</div>
                                <div class="step-label">Finish</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-5">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo max(0, min(100, ($step-1)*33.33)); ?>%" aria-valuenow="<?php echo ($step-1)*33.33; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo ($messageType === 'error') ? 'danger' : 'success'; ?> d-flex align-items-center border-0 shadow-sm mb-4 rounded-3" role="alert">
                                <i class="fas <?php echo ($messageType === 'error') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-3 fs-5"></i>
                                <div><?php echo $message; ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Step 1: System Requirements -->
                        <?php if ($step == 1): ?>
                            <div class="step-content">
                                <h4 class="fw-bold mb-3"><i class="fas fa-microchip me-2 text-primary"></i> System Check</h4>
                                <p class="text-muted small mb-4">We're checking if your server is ready to host GameCult.</p>
                                
                                <div class="requirements mb-4">
                                    <?php foreach ($requirements as $req => $met): ?>
                                        <div class="requirement-item d-flex justify-content-between align-items-center">
                                            <span class="small fw-600"><?php echo $req; ?></span>
                                            <span class="badge rounded-pill <?php echo $met ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> px-3 py-2">
                                                <i class="fas fa-<?php echo $met ? 'check-circle' : 'times-circle'; ?> me-1"></i> <?php echo $met ? 'Verified' : 'Failed'; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (!$allRequirementsMet): ?>
                                    <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis small mb-4">
                                        <h6 class="fw-bold"><i class="fas fa-lightbulb me-2"></i> How to fix:</h6>
                                        <ul class="mb-0 ps-3 mt-2">
                                            <li>Enable missing PHP extensions in your configuration.</li>
                                            <li>Ensure your PHP version is at least 7.4.</li>
                                            <li>Set write permissions (755) for the root and <code>src/</code> directory.</li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-secondary w-100 disabled" disabled>Installation Blocked</button>
                                <?php else: ?>
                                    <div class="bg-success-subtle text-success p-3 rounded-3 small mb-4 text-center">
                                        <i class="fas fa-thumbs-up me-2"></i> <strong>Excellent!</strong> All requirements are satisfied.
                                    </div>
                                    <a href="?step=2" class="btn btn-primary w-100 shadow-sm">Continue to Database <i class="fas fa-arrow-right ms-2"></i></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Step 2: Database Configuration -->
                        <?php if ($step == 2): ?>
                            <div class="step-content">
                                <h4 class="fw-bold mb-3"><i class="fas fa-database me-2 text-primary"></i> Database Link</h4>
                                <p class="text-muted small mb-4">Please provide your MySQL database connection markers.</p>
                                
                                <form method="POST" action="?step=2" class="row g-3">
                                    <div class="col-12">
                                        <label class="small fw-bold text-muted text-uppercase mb-1">Database Host</label>
                                        <input type="text" name="db_host" class="form-control" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" required>
                                        <div class="form-text x-small">Usually <code>localhost</code></div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted text-uppercase mb-1">Username</label>
                                        <input type="text" name="db_user" class="form-control" value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>" required placeholder="root">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted text-uppercase mb-1">Password</label>
                                        <input type="password" name="db_pass" class="form-control" value="<?php echo htmlspecialchars($_POST['db_pass'] ?? ''); ?>" placeholder="••••••••">
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="small fw-bold text-muted text-uppercase mb-1">Database Name</label>
                                        <input type="text" name="db_name" class="form-control" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'gamecult'); ?>" required>
                                        <div class="form-text x-small text-primary">We will create the database if it doesn't exist.</div>
                                    </div>
                                    
                                    <div class="col-12 mt-4 pt-2">
                                        <button type="submit" class="btn btn-primary w-100 shadow-sm mb-2">Connect Database <i class="fas fa-link ms-2"></i></button>
                                        <a href="?step=1" class="btn btn-link w-100 text-muted text-decoration-none small fw-600">Go Back</a>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Step 3: Database Setup -->
                        <?php if ($step == 3): ?>
                            <div class="step-content text-center py-4">
                                <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h4 class="fw-bold mb-3">Initializing GameCult</h4>
                                <p class="text-muted">We're setting up your database tables, indexes, and initial configurations. This handles the Heavy lifting for you.</p>
                                
                                <div class="alert alert-light border border-light-subtle small text-muted mt-4">
                                    <i class="fas fa-info-circle me-1"></i> Just a few seconds remaining...
                                </div>

                                <form method="POST" action="?step=3" id="autoForm">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm mt-3">Finalize Installation <i class="fas fa-magic ms-2"></i></button>
                                </form>
                            </div>
                            <script>
                                setTimeout(function() {
                                    document.getElementById('autoForm').submit();
                                }, 3000);
                            </script>
                        <?php endif; ?>

                        <!-- Step 4: Completion -->
                        <?php if ($step == 4): ?>
                            <div class="step-content text-center">
                                <div class="display-1 text-success mb-4">
                                    <i class="fas fa-check-circle shadow-lg rounded-circle"></i>
                                </div>
                                <h3 class="fw-800 mb-2">Success!</h3>
                                <p class="text-muted mb-4">GameCult version 2.0 is now live on your server.</p>
                                
                                <div class="bg-light p-4 rounded-4 text-start border border-light-subtle mb-4">
                                    <h6 class="fw-bold text-muted text-uppercase small mb-3"><i class="fas fa-key me-2"></i> Admin Control Panel</h6>
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span>Email:</span>
                                        <span class="fw-bold">admin@gamecult.dz</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0 small">
                                        <span>Password:</span>
                                        <span class="fw-bold">admin123</span>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning border-0 small text-start shadow-xs mb-4">
                                    <i class="fas fa-shield-alt me-2"></i> <strong>Security Tip:</strong> Change your default password immediately after logging in.
                                </div>
                                
                                <a href="src/index.php" class="btn btn-primary w-100 shadow-sm btn-lg px-5">Launch My Store <i class="fas fa-rocket ms-2"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-muted x-small">© <?php echo date('Y'); ?> GameCult. Built with <i class="fas fa-heart text-danger"></i> for Algers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>