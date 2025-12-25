<?php
// about.php - About us page
require_once 'includes/init.php';
require_once 'includes/header.php';
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('about_us')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <div class="card border-0 shadow-sm bg-dark text-white mb-5 overflow-hidden rounded-4">
        <div class="card-body p-5 text-center">
            <h1 class="display-4 fw-bold mb-4"><?php echo t('about_us'); ?></h1>
            <p class="fs-5 text-white-75 mx-auto mb-0" style="max-width: 800px;">
                <?php echo t('about_intro'); ?>
            </p>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-star text-danger me-2"></i> <?php echo t('why_choose_us'); ?></h2>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row g-4 text-center">
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                            <i class="fas fa-shield-alt text-danger fs-3"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('secure_shopping'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('secure_shopping_desc'); ?></p>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                            <i class="fas fa-shipping-fast text-danger fs-3"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('fast_delivery'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('fast_delivery_desc'); ?></p>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                            <i class="fas fa-undo text-danger fs-3"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('easy_returns'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('easy_returns_desc'); ?></p>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 70px; height: 70px;">
                            <i class="fas fa-headset text-danger fs-3"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('customer_support'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('customer_support_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-bullseye text-danger me-2"></i> <?php echo t('our_mission'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-0"><?php echo t('our_mission_desc'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-users text-danger me-2"></i> <?php echo t('our_team'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-0"><?php echo t('our_team_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-address-book text-danger me-2"></i> <?php echo t('contact_info'); ?></h2>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-3 border-end-md">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                            <i class="fas fa-map-marker-alt text-danger fs-4"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('address'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('address_desc'); ?></p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="p-3 border-end-md">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                            <i class="fas fa-phone text-danger fs-4"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('phone'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('phone_desc'); ?></p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="p-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                            <i class="fas fa-envelope text-danger fs-4"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-3"><?php echo t('email'); ?></h3>
                        <p class="text-muted small mb-0"><?php echo t('email_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
