<?php
// admin/pages.php - Layout editor for homepage sections
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../config/layout_config.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle layout configuration updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_layout':
                handleLayoutUpdate();
                break;
            case 'update_carousel':
                handleCarouselUpdate();
                break;
            case 'update_carousel_settings':
                handleCarouselSettingsUpdate();
                break;
            case 'upload_carousel_image':
                handleCarouselImageUpload();
                break;
            case 'delete_slide':
                handleSlideDelete();
                break;
        }
    }
}

function handleCarouselSettingsUpdate() {
    $showControls = isset($_POST['carousel_show_controls']);
    $newConfig = [
        'autoplay' => isset($_POST['carousel_autoplay']),
        'interval' => (int)$_POST['carousel_interval'] ?: 5000,
        'pause_on_hover' => isset($_POST['carousel_pause']),
        'animation_type' => $_POST['carousel_animation_type'] ?? 'slide',
        'animation_duration' => (int)$_POST['carousel_animation_duration'] ?: 500,
        'show_navigation' => $showControls,
        'show_indicators' => $showControls,
        'infinite_loop' => isset($_POST['carousel_infinite_loop']),
        'height' => !empty($_POST['carousel_height']) ? $_POST['carousel_height'] : '600px',
        'width' => !empty($_POST['carousel_width']) ? $_POST['carousel_width'] : '100%',
        'overlay_opacity' => (float)$_POST['carousel_overlay_opacity'] ?: 0.3,
        'lazy_loading' => isset($_POST['carousel_lazy_loading']),
        'cta_alignment' => $_POST['carousel_cta_alignment'] ?? 'center',
        'text_color' => $_POST['carousel_text_color'] ?? '#ffffff'
    ];

    // Save with existing sections and slides
    $config = loadLayoutConfig();
    saveLayoutConfig($config['sections'], $config['carousel_slides'], $newConfig);

    $_SESSION['success_message'] = 'Carousel settings updated successfully!';
    header('Location: pages.php');
    exit;
}

function handleLayoutUpdate() {
    global $layoutConfig;
    
    $sections = $_POST['sections'] ?? [];
    $updatedConfig = [];
    
    // Process each section
    foreach ($layoutConfig as $key => $default) {
        $enabled = isset($_POST["section_{$key}"]) && $_POST["section_{$key}"] === 'on';
        $order = (int)($_POST["order_{$key}"] ?? $default['order']);
        
        $updatedConfig[$key] = [
            'enabled' => $enabled,
            'order' => $order,
            'title' => $default['title'],
            'description' => $default['description'],
            'rows' => isset($_POST["rows_{$key}"]) ? (int)$_POST["rows_{$key}"] : ($default['rows'] ?? 1),
            'cards_per_row' => isset($_POST["cards_per_row_{$key}"]) ? (int)$_POST["cards_per_row_{$key}"] : ($default['cards_per_row'] ?? 6)
        ];
    }
    
    // Save the updated configuration
    $config = loadLayoutConfig();
    saveLayoutConfig($updatedConfig, $config['carousel_slides']);
    
    $_SESSION['success_message'] = 'Layout configuration updated successfully!';
    header('Location: pages.php');
    exit;
}

function handleCarouselUpdate() {
    $slideId = $_POST['slide_id']; // Keep as string to match JSON decoding
    $config = loadLayoutConfig();

    // Find the slide to update with more robust matching
    $slideIndex = -1;
    $slideToUpdate = null;
    foreach ($config['carousel_slides'] as $index => $slide) {
        // Compare both as strings to handle JSON decoding inconsistencies
        if ((string)$slide['id'] === (string)$slideId) {
            $slideIndex = $index;
            $slideToUpdate = $slide;
            break;
        }
    }

    if ($slideIndex !== -1 && $slideToUpdate !== null) {
        // Handle image upload if provided
        $imagePath = $_POST['image'] ?? $slideToUpdate['image'];

        if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['slide_image']['name'])) {
            $uploadDir = '../img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['slide_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'carousel-' . $slideId . '-' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $uploadPath)) {
                $imagePath = 'img/' . $fileName;
            } else {
                $_SESSION['error_message'] = 'Failed to upload new image. Keeping the current image.';
            }
        }

        // Update the slide with content fields only (shared parameters are in carousel config)
        $config['carousel_slides'][$slideIndex] = [
            'id' => $slideId, // Keep the original ID as string
            'image' => $imagePath,
            'title' => $_POST['title'] ?? $slideToUpdate['title'],
            'description' => $_POST['description'] ?? $slideToUpdate['description'],
            'button_text' => $_POST['button_text'] ?? $slideToUpdate['button_text'],
            'button_url' => $_POST['button_url'] ?? $slideToUpdate['button_url'],
            'enabled' => isset($_POST['enabled']),
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'order' => (int)($_POST['order'] ?? $slideToUpdate['order'] ?? 1)
        ];

        // Save updated config (preserve carousel config as well)
        $carouselConfig = $config['carousel_config'] ?? [
            'autoplay' => true,
            'interval' => 5000,
            'pause_on_hover' => true
        ];

        saveLayoutConfig($config['sections'], $config['carousel_slides'], $carouselConfig);

        $_SESSION['success_message'] = 'Carousel slide updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Slide not found for update. Slide ID: ' . $slideId . '. Available slides: ' . count($config['carousel_slides']);
    }

    header('Location: pages.php');
    exit;
}

function handleCarouselImageUpload() {
    if (isset($_FILES['carousel_image']) && $_FILES['carousel_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../img/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'carousel-' . time() . '-' . basename($_FILES['carousel_image']['name']);
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], $uploadPath)) {
            $config = loadLayoutConfig();
            $newSlide = [
                'id' => time(), // Use timestamp as ID
                'image' => 'img/' . $fileName,
                'title' => 'New Slide',
                'description' => 'Slide description',
                'button_text' => 'Shop Now',
                'button_url' => 'products.php',
                'enabled' => true
            ];
            
            $config['carousel_slides'][] = $newSlide;
            
            // Save updated config
            $currentSections = loadLayoutConfig()['sections'];
            saveLayoutConfig($currentSections, $config['carousel_slides']);
            
            $_SESSION['success_message'] = 'New carousel slide added successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to upload image.';
        }
    } else {
        $_SESSION['error_message'] = 'No image file provided or upload error.';
    }
    
    header('Location: pages.php');
    exit;
}

function handleSlideDelete() {
    $slideId = $_POST['slide_id']; // Keep as string to match JSON decoding
    $config = loadLayoutConfig();

    // Remove the slide with the given ID
    $config['carousel_slides'] = array_filter($config['carousel_slides'], function($slide) use ($slideId) {
        return (string)$slide['id'] !== (string)$slideId;
    });

    // Save updated config
    $currentSections = loadLayoutConfig()['sections'];
    saveLayoutConfig($currentSections, $config['carousel_slides']);

    $_SESSION['success_message'] = 'Carousel slide deleted successfully!';

    header('Location: pages.php');
    exit;
}

// Get current layout configuration
$layoutSections = getOrderedSections();
$carouselSlides = getCarouselSlides();

// Get any messages
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $messageType = 'success';
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $messageType = 'error';
    unset($_SESSION['error_message']);
}

// Set page title and heading variables for the template
$page_title = 'Pages';
$page_heading = 'Homepage';

// Include the shared header template
include 'header.php';

?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Tab Navigation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-justified" id="pageTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill fw-bold py-2" id="carousel-tab" data-bs-toggle="pill" data-bs-target="#carousel" type="button" role="tab" aria-controls="carousel" aria-selected="true">
                                <i class="fas fa-sliders-h me-2"></i> Carousel Management
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="layout-tab" data-bs-toggle="pill" data-bs-target="#layout" type="button" role="tab" aria-controls="layout" aria-selected="false">
                                <i class="fas fa-th-large me-2"></i> Layout Editor
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="pageTabsContent">
                <!-- Carousel Settings Tab -->
                <div class="tab-pane fade show active" id="carousel" role="tabpanel" aria-labelledby="carousel-tab">
                    <form method="POST" action="pages.php">
                        <input type="hidden" name="action" value="update_carousel_settings">
                        
                        <div class="row g-2">
                            <div class="col-md-6 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-2 border-0">
                                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-play-circle me-2 text-primary"></i> Playback & Loop</h5>
                                    </div>
                                    <div class="card-body p-3 pt-0">
                                        <div class="row g-3">
                                            <div class="col-12 bg-light p-2 rounded-3 border border-light-subtle">
                                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                                    <label class="form-check-label fw-bold small text-dark" for="carousel_autoplay">Auto-playback</label>
                                                    <input class="form-check-input ms-0 shadow-none" type="checkbox" name="carousel_autoplay" id="carousel_autoplay" <?php echo (getCarouselConfig()['autoplay']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                            <div class="col-12 bg-light p-2 rounded-3 border border-light-subtle">
                                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                                    <label class="form-check-label fw-bold small text-dark" for="carousel_infinite_loop">Infinite Loop</label>
                                                    <input class="form-check-input ms-0 shadow-none" type="checkbox" name="carousel_infinite_loop" id="carousel_infinite_loop" <?php echo (getCarouselConfig()['infinite_loop']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                            <div class="col-12 bg-light p-2 rounded-3 border border-light-subtle">
                                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                                    <label class="form-check-label fw-bold small text-dark" for="carousel_pause">Pause on Interaction</label>
                                                    <input class="form-check-input ms-0 shadow-none" type="checkbox" name="carousel_pause" id="carousel_pause" <?php echo (getCarouselConfig()['pause_on_hover']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                            <div class="col-12 bg-light p-2 rounded-3 border border-light-subtle">
                                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                                    <label class="form-check-label fw-bold small text-dark" for="carousel_show_controls">Show Navigation</label>
                                                    <input class="form-check-input ms-0 shadow-none" type="checkbox" name="carousel_show_controls" id="carousel_show_controls" <?php echo (getCarouselConfig()['show_navigation'] || getCarouselConfig()['show_indicators']) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-2 border-0">
                                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-clock me-2 text-primary"></i> Timing & Duration</h5>
                                    </div>
                                    <div class="card-body p-3 pt-0">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Slide Interval (ms)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-history small"></i></span>
                                                <input type="number" name="carousel_interval" class="form-control border-light-subtle shadow-none py-2" value="<?php echo getCarouselConfig()['interval']; ?>" min="1000" max="10000" step="500">
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Animation Duration (ms)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-wind small"></i></span>
                                                <input type="number" name="carousel_animation_duration" class="form-control border-light-subtle shadow-none py-2" value="<?php echo getCarouselConfig()['animation_duration']; ?>" min="100" max="2000" step="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-2 border-0">
                                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-magic me-2 text-primary"></i> Visual Effects</h5>
                                    </div>
                                    <div class="card-body p-3 pt-0">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Transition Style</label>
                                            <select name="carousel_animation_type" class="form-select border-light-subtle shadow-none py-2">
                                                <option value="slide" <?php echo (getCarouselConfig()['animation_type'] === 'slide') ? 'selected' : ''; ?>>Classic Slide</option>
                                                <option value="fade" <?php echo (getCarouselConfig()['animation_type'] === 'fade') ? 'selected' : ''; ?>>Cross Fade</option>
                                                <option value="zoom" <?php echo (getCarouselConfig()['animation_type'] === 'zoom') ? 'selected' : ''; ?>>Smooth Zoom</option>
                                            </select>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between">
                                                Overlay Intensity
                                                <span class="badge bg-light text-primary border border-light-subtle x-small"><?php echo round(getCarouselConfig()['overlay_opacity'] * 100); ?>%</span>
                                            </label>
                                            <input type="range" name="carousel_overlay_opacity" class="form-range" min="0" max="1" step="0.1" value="<?php echo getCarouselConfig()['overlay_opacity']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-2 border-0">
                                        <h5 class="mb-0 fw-bold small text-uppercase text-muted"><i class="fas fa-palette me-2 text-primary"></i> Dimensions & Style</h5>
                                    </div>
                                    <div class="card-body p-3 pt-0">
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label x-small fw-bold text-muted text-uppercase">Height</label>
                                                <input type="text" name="carousel_height" class="form-control form-control-sm border-light-subtle shadow-none py-2" value="<?php echo getCarouselConfig()['height']; ?>" placeholder="600px">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label x-small fw-bold text-muted text-uppercase">Width</label>
                                                <input type="text" name="carousel_width" class="form-control form-control-sm border-light-subtle shadow-none py-2" value="<?php echo getCarouselConfig()['width']; ?>" placeholder="100%">
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-8">
                                                <label class="form-label x-small fw-bold text-muted text-uppercase">Text Alignment</label>
                                                <select name="carousel_cta_alignment" class="form-select form-select-sm border-light-subtle shadow-none py-2">
                                                    <option value="left" <?php echo (getCarouselConfig()['cta_alignment'] === 'left') ? 'selected' : ''; ?>>Align Left</option>
                                                    <option value="center" <?php echo (getCarouselConfig()['cta_alignment'] === 'center') ? 'selected' : ''; ?>>Align Center</option>
                                                    <option value="right" <?php echo (getCarouselConfig()['cta_alignment'] === 'right') ? 'selected' : ''; ?>>Align Right</option>
                                                </select>
                                            </div>
                                            <div class="col-4 text-center">
                                                <label class="form-label x-small fw-bold text-muted text-uppercase d-block">Text Color</label>
                                                <input type="color" name="carousel_text_color" class="form-control form-control-sm form-control-color border-light-subtle shadow-none w-100 py-1" value="<?php echo getCarouselConfig()['text_color']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow">
                                <i class="fas fa-save me-2"></i> Save Carousel Configuration
                            </button>
                        </div>
                    </form>

                    <hr class="my-5 border-light-subtle">

                    <!-- Slides Management Section (Merged) -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i> Active Slides</h5>
                        <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-none fw-bold" onclick="toggleUploadSection()">
                            <i class="fas fa-plus-circle me-1"></i> Add New Slide
                        </button>
                    </div>

                    <div id="uploadSection" class="card border-0 shadow-sm mb-4" style="display: none;">
                        <div class="card-header bg-primary py-2 px-3 border-0">
                            <h6 class="mb-0 text-white fw-bold x-small text-uppercase">New Carousel Image</h6>
                        </div>
                        <div class="card-body p-4 text-center">
                            <form method="POST" action="pages.php" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_carousel_image">
                                <div class="mb-4">
                                    <label class="form-label d-block bg-light p-5 rounded-3 border border-dashed text-muted cursor-pointer hover-bg-light transition" for="carousel_image_input">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary-subtle opacity-50"></i>
                                        <div class="fw-bold">Click to choose image</div>
                                        <div class="x-small mt-1 text-muted">Recommended: 1920x600px</div>
                                        <input type="file" name="carousel_image" id="carousel_image_input" class="d-none" accept="image/*" required onchange="this.form.submit()">
                                    </label>
                                </div>
                                <button type="button" class="btn btn-link text-muted x-small fw-bold text-decoration-none" onclick="toggleUploadSection()">Cancel</button>
                            </form>
                        </div>
                    </div>

                    <div id="slides-list" class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 80px;">#Pos</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase" style="width: 150px;">Preview</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Slide Content</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Status</th>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end" style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($carouselSlides)): ?>
                                            <tr><td colspan="5" class="text-center py-5 text-muted">No slides found.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($carouselSlides as $slide): ?>
                                                <tr>
                                                    <td class="px-4"><span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2 py-1 x-small fw-bold"><?php echo $slide['order'] ?? 1; ?></span></td>
                                                    <td>
                                                        <?php if (!empty($slide['image'])): ?>
                                                            <div class="rounded-3 shadow-xs border border-light-subtle overflow-hidden" style="width: 100px; height: 50px;">
                                                                <img src="../<?php echo htmlspecialchars($slide['image']); ?>" alt="Slide" class="w-100 h-100 object-fit-cover">
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars(substr($slide['title'], 0, 40)); ?></div>
                                                        <div class="text-muted x-small italic mt-1 text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($slide['description'] ?? ''); ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?php echo $slide['enabled'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?> rounded-pill px-3 py-1 fw-bold x-small text-uppercase">
                                                            <?php echo $slide['enabled'] ? 'Visible' : 'Hidden'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-primary" onclick="editSlide(<?php echo $slide['id']; ?>)">
                                                                <i class="fas fa-edit me-1"></i> Edit
                                                            </button>
                                                            <form method="POST" action="pages.php" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_slide">
                                                                <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                                                                <button type="submit" class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-danger" onclick="return confirm('Delete this slide?')">
                                                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden edit forms for each slide (styled as modal-like cards) -->
                    <div id="editForms" style="display: none;">
                        <?php foreach ($carouselSlides as $slide): ?>
                            <div class="slide-edit-form p-4 bg-light rounded-4 shadow-sm border border-light-subtle mb-4" id="editForm_<?php echo $slide['id']; ?>" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i> Edit Slide #<?php echo $slide['id']; ?></h5>
                                    <button type="button" class="btn-close shadow-none" onclick="cancelEdit()"></button>
                                </div>
                                
                                <form method="POST" action="pages.php" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_carousel">
                                    <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">

                                    <div class="row g-4">
                                        <div class="col-md-5">
                                            <div class="bg-white p-3 rounded-4 border border-light-subtle shadow-xs">
                                                <label class="form-label small fw-bold text-muted text-uppercase mb-3">Slide Background</label>
                                                <?php if (!empty($slide['image'])): ?>
                                                    <div class="rounded-3 shadow-sm overflow-hidden mb-3 border border-light-subtle" style="aspect-ratio: 16/7;">
                                                        <img src="../<?php echo htmlspecialchars($slide['image']); ?>" alt="Preview" class="w-100 h-100 object-fit-cover">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="hidden" name="image" value="<?php echo htmlspecialchars($slide['image']); ?>">
                                                <div class="mb-2">
                                                    <input type="file" name="slide_image" class="form-control form-control-sm shadow-none border-light-subtle" accept="image/*">
                                                    <small class="text-muted x-small italic d-block mt-1">Upload to replace existing image.</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-7">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Headline Title</label>
                                                    <input type="text" name="title" class="form-control border-light-subtle shadow-none py-2" value="<?php echo htmlspecialchars($slide['title']); ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Display Order</label>
                                                    <input type="number" name="order" class="form-control border-light-subtle shadow-none py-2" value="<?php echo $slide['order'] ?? 1; ?>" min="1">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Short Description</label>
                                                    <textarea name="description" class="form-control border-light-subtle shadow-none py-2" rows="2"><?php echo htmlspecialchars($slide['description'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Call-to-Action Text</label>
                                                    <input type="text" name="button_text" class="form-control border-light-subtle shadow-none py-2" value="<?php echo htmlspecialchars($slide['button_text']); ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Call-to-Action URL</label>
                                                    <input type="text" name="button_url" class="form-control border-light-subtle shadow-none py-2" value="<?php echo htmlspecialchars($slide['button_url']); ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Scheduling (Start)</label>
                                                    <input type="datetime-local" name="start_date" class="form-control border-light-subtle shadow-none py-2" value="<?php echo htmlspecialchars($slide['start_date'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Scheduling (End)</label>
                                                    <input type="datetime-local" name="end_date" class="form-control border-light-subtle shadow-none py-2" value="<?php echo htmlspecialchars($slide['end_date'] ?? ''); ?>">
                                                </div>
                                                <div class="col-12">
                                                    <div class="bg-white p-3 rounded-3 border border-light-subtle mt-2">
                                                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-0">
                                                            <label class="form-check-label fw-bold small text-primary" for="enabled_<?php echo $slide['id']; ?>"><i class="fas fa-eye me-1"></i> Enable Slide Content</label>
                                                            <input class="form-check-input ms-0 shadow-none" type="checkbox" name="enabled" id="enabled_<?php echo $slide['id']; ?>" <?php echo $slide['enabled'] ? 'checked' : ''; ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-top text-end d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-light px-4 py-2 fw-bold text-muted rounded-pill border border-light-subtle shadow-xs" onclick="cancelEdit()">Discard Changes</button>
                                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow">Update Carousel Slide</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Layout Editor Tab -->
                <div class="tab-pane fade" id="layout" role="tabpanel" aria-labelledby="layout-tab">
                    <form method="POST" action="pages.php">
                        <input type="hidden" name="action" value="update_layout">
                        
                        <div class="card border-0 shadow-sm overflow-hidden mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-sort me-2 text-primary"></i> Section Ordering & Visibility</h5>
                                <p class="text-muted small mb-0 mt-1 italic">Use the arrows to reorder homepage sections.</p>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush" id="layout-editor">
                                    <?php
                                    $orderedSections = $layoutSections;
                                    uasort($orderedSections, function($a, $b) {
                                        return $a['order'] <=> $b['order'];
                                    });
                                    ?>
                                    <?php foreach ($orderedSections as $key => $section): ?>
                                        <div class="list-group-item p-4 border-light-subtle layout-section" data-key="<?php echo $key; ?>">
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="d-flex flex-column gap-1 order-controls">
                                                    <button type="button" class="btn btn-light btn-sm border-light-subtle shadow-xs move-up" onclick="moveSection(this, 'up')"><i class="fas fa-chevron-up x-small"></i></button>
                                                    <button type="button" class="btn btn-light btn-sm border-light-subtle shadow-xs move-down" onclick="moveSection(this, 'down')"><i class="fas fa-chevron-down x-small"></i></button>
                                                </div>
                                                
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <h6 class="mb-0 fw-bold text-dark fs-5"><?php echo htmlspecialchars($section['title']); ?></h6>
                                                        <div class="form-check form-switch pe-0">
                                                            <input class="form-check-input shadow-none" type="checkbox" name="section_<?php echo $key; ?>" id="section_<?php echo $key; ?>" <?php echo $section['enabled'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label fw-bold small text-muted text-uppercase" for="section_<?php echo $key; ?>">Display Status</label>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($section['description']); ?></p>
                                                    <input type="hidden" name="order_<?php echo $key; ?>" value="<?php echo $section['order']; ?>" class="order-input">
                                                    
                                                    <?php if (isset($section['rows'])): ?>
                                                        <div class="bg-light p-3 rounded-3 border border-light-subtle small shadow-xs">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Grid Pattern (Rows)</label>
                                                                    <select name="rows_<?php echo $key; ?>" class="form-select form-select-sm border-light-subtle shadow-none">
                                                                        <?php for($r=1; $r<=5; $r++): ?>
                                                                            <option value="<?php echo $r; ?>" <?php echo $section['rows'] == $r ? 'selected' : ''; ?>><?php echo $r; ?> Row(s)</option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label x-small fw-bold text-muted text-uppercase mb-1">Density (Cards/Row)</label>
                                                                    <select name="cards_per_row_<?php echo $key; ?>" class="form-select form-select-sm border-light-subtle shadow-none">
                                                                        <option value="4" <?php echo $section['cards_per_row'] == 4 ? 'selected' : ''; ?>>4 Items per row</option>
                                                                        <option value="5" <?php echo $section['cards_per_row'] == 5 ? 'selected' : ''; ?>>5 Items per row</option>
                                                                        <option value="6" <?php echo $section['cards_per_row'] == 6 ? 'selected' : ''; ?>>6 Items per row</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-5">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow">
                                <i class="fas fa-save me-2"></i> Confirm Layout Changes
                            </button>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <script>
        // Use standard Bootstrap 5 Tab event for internal state if needed
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('button[data-bs-toggle="pill"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', event => {
                    // Update URL hash or handle layout recalculations if necessary
                    const tabId = event.target.id;
                    localStorage.setItem('activeAdminTab', tabId);
                });
            });

            // Restore active tab
            const activeTabId = localStorage.getItem('activeAdminTab');
            if (activeTabId) {
                const activeTab = document.getElementById(activeTabId);
                if (activeTab) {
                    bootstrap.Tab.getOrCreateInstance(activeTab).show();
                }
            }
        });

        // Toggle upload section
        function toggleUploadSection() {
            const uploadSection = document.getElementById('uploadSection');
            uploadSection.style.display = uploadSection.style.display === 'none' ? 'block' : 'none';
        }

        // Edit slide functionality
        function editSlide(slideId) {
            document.getElementById('slides-list').style.display = 'none';
            
            // Hide all edit forms
            const editForms = document.querySelectorAll('.slide-edit-form');
            editForms.forEach(form => form.style.display = 'none');

            // Show current one
            const editForm = document.getElementById('editForm_' + slideId);
            if (editForm) {
                editForm.style.display = 'block';
                document.getElementById('editForms').style.display = 'block';
                window.scrollTo({ top: editForm.offsetTop - 100, behavior: 'smooth' });
            }
        }

        function cancelEdit() {
            document.getElementById('editForms').style.display = 'none';
            document.getElementById('slides-list').style.display = 'block';
        }

        // Reorder functionality for sections
        function moveSection(btn, direction) {
            const row = btn.closest('.layout-section');
            const container = document.getElementById('layout-editor');
            
            if (direction === 'up') {
                const prev = row.previousElementSibling;
                if (prev) container.insertBefore(row, prev);
            } else {
                const next = row.nextElementSibling;
                if (next) container.insertBefore(next, row);
            }
            
            updateOrderValues();
        }

        function updateOrderValues() {
            const sections = document.querySelectorAll('.layout-section');
            sections.forEach((section, index) => {
                const orderInput = section.querySelector('.order-input');
                if (orderInput) orderInput.value = index + 1;
            });
        }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

