<?php
// config/layout_config.php - Layout configuration for homepage sections

// Define the default sections and their settings
$layoutConfig = [
    'flash_sales' => [
        'enabled' => true,
        'order' => 1,
        'title' => 'Flash Sales',
        'description' => 'Limited time offers section',
        'rows' => 1,
        'cards_per_row' => 6
    ],
    'best_sellers' => [
        'enabled' => true,
        'order' => 2,
        'title' => 'Best Sellers',
        'description' => 'Popular products section',
        'rows' => 1,
        'cards_per_row' => 6
    ],
    'category_showcase' => [
        'enabled' => true,
        'order' => 3,
        'title' => 'Category Showcase',
        'description' => 'Featured categories with products'
    ],
    'official_brands' => [
        'enabled' => true,
        'order' => 4,
        'title' => 'Official Brands',
        'description' => 'Brand showcase section'
    ],
    'new_arrivals' => [
        'enabled' => true,
        'order' => 5,
        'title' => 'New Arrivals',
        'description' => 'Latest products section',
        'rows' => 1,
        'cards_per_row' => 6
    ]
];

// Carousel configuration
$carouselConfig = [
    'autoplay' => true,
    'interval' => 5000, // in milliseconds (5 seconds)
    'pause_on_hover' => true,
    'animation_type' => 'slide', // slide, fade, zoom
    'animation_duration' => 500, // in milliseconds
    'show_navigation' => true, // show/hide arrows
    'show_indicators' => true, // show/hide dots
    'infinite_loop' => true, // whether carousel repeats continuously
    'height' => '600px', // fixed height
    'width' => '100%', // width
    'overlay_opacity' => 0.3, // opacity for text readability over images
    'lazy_loading' => true,
    'cta_alignment' => 'center', // text alignment (left, center, right)
    'text_color' => '#ffffff', // color for text on slides
    'breakpoints' => [
        'mobile' => ['width' => 768, 'height' => '400px'],
        'tablet' => ['width' => 1024, 'height' => '500px']
    ]
];

// Carousel slides configuration
$carouselSlides = [
    [
        'id' => 1,
        'image' => 'img/carousel-slide-1.jpg',
        'title' => 'Tech Deals Up to 50% Off',
        'description' => 'Latest Electronics & Gaming Gear',
        'button_text' => 'Shop Now',
        'button_url' => 'products.php',
        'enabled' => true,
        'start_date' => null,
        'end_date' => null,
        'order' => 1
    ],
    [
        'id' => 2,
        'image' => 'img/carousel-slide-2.jpg',
        'title' => 'Summer Sale',
        'description' => 'Best prices on summer products',
        'button_text' => 'View Deals',
        'button_url' => 'products.php?category=summer',
        'enabled' => true,
        'start_date' => null,
        'end_date' => null,
        'order' => 2
    ],
    [
        'id' => 3,
        'image' => 'img/carousel-slide-3.jpg',
        'title' => 'Free Shipping',
        'description' => 'On all orders over $50',
        'button_text' => 'Learn More',
        'button_url' => 'shipping-info.php',
        'enabled' => true,
        'start_date' => null,
        'end_date' => null,
        'order' => 3
    ]
];

// Function to save layout configuration
function saveLayoutConfig($config, $slides, $carouselConfig = null) {
    if ($carouselConfig === null) {
        // Load existing carousel config to preserve it
        $existingData = loadLayoutConfig();
        $carouselConfig = $existingData['carousel_config'] ?? [
            'autoplay' => true,
            'interval' => 5000,
            'pause_on_hover' => true,
            'animation_type' => 'slide',
            'animation_duration' => 500,
            'show_navigation' => true,
            'show_indicators' => true,
            'infinite_loop' => true,
            'height' => '600px',
            'width' => '100%',
            'overlay_opacity' => 0.3,
            'lazy_loading' => true,
            'cta_alignment' => 'center',
            'text_color' => '#ffffff'
        ];
    }

    $data = [
        'sections' => $config,
        'carousel_slides' => $slides,
        'carousel_config' => $carouselConfig
    ];
    file_put_contents(__DIR__ . '/layout_config.json', json_encode($data, JSON_PRETTY_PRINT));
}

// Function to load layout configuration
function loadLayoutConfig() {
    $configFile = __DIR__ . '/layout_config.json';
    
    // Default carousel configuration
    $defaultCarouselConfig = [
        'autoplay' => true,
        'interval' => 5000,
        'pause_on_hover' => true,
        'animation_type' => 'slide',
        'animation_duration' => 500,
        'show_navigation' => true,
        'show_indicators' => true,
        'infinite_loop' => true,
        'height' => '600px',
        'width' => '100%',
        'overlay_opacity' => 0.3,
        'lazy_loading' => true,
        'cta_alignment' => 'center',
        'text_color' => '#ffffff'
    ];

    if (file_exists($configFile)) {
        $data = json_decode(file_get_contents($configFile), true);
        
        // Initialize with defaults if missing
        $sections = $data['sections'] ?? [];
        $carousel_slides = $data['carousel_slides'] ?? [];
        $loaded_carousel_config = $data['carousel_config'] ?? [];
        
        // Merge loaded carousel config with defaults to ensure all keys exist
        $carousel_config = array_merge($defaultCarouselConfig, $loaded_carousel_config);
        
        // Merge sections with defaults
        global $layoutConfig;
        foreach ($layoutConfig as $key => $default) {
            if (isset($sections[$key])) {
                $sections[$key] = array_merge($default, $sections[$key]);
            } else {
                $sections[$key] = $default;
            }
        }
        
        // If carousel_slides is empty, initialize with defaults
        if (empty($carousel_slides)) {
            global $carouselSlides;
            $carousel_slides = $carouselSlides;
        }
        
        return [
            'sections' => $sections,
            'carousel_slides' => $carousel_slides,
            'carousel_config' => $carousel_config
        ];
    }
    
    return [
        'sections' => [], // Will be populated by getOrderedSections from global defaults
        'carousel_slides' => $global['carouselSlides'] ?? [], 
        'carousel_config' => $defaultCarouselConfig
    ];
}

// Function to get ordered sections
function getOrderedSections() {
    $config = loadLayoutConfig();
    $sections = $config['sections'];
    
    if (empty($sections)) {
        global $layoutConfig;
        $sections = $layoutConfig;
    }
    
    // Sort by order value
    uasort($sections, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });
    
    return $sections;
}

// Function to get carousel slides
function getCarouselSlides() {
    $config = loadLayoutConfig();
    $slides = $config['carousel_slides'];

    $now = new DateTime();

    // Filter slides based on enabled status and date scheduling
    $filteredSlides = array_filter($slides, function($slide) use ($now) {
        // Check if slide is enabled
        if (!$slide['enabled']) {
            return false;
        }

        // Check scheduling dates if set
        if (!empty($slide['start_date'])) {
            $startDate = new DateTime($slide['start_date']);
            if ($now < $startDate) {
                return false; // Not yet active
            }
        }

        if (!empty($slide['end_date'])) {
            $endDate = new DateTime($slide['end_date']);
            if ($now > $endDate) {
                return false; // Expired
            }
        }

        return true;
    });

    // Sort slides by order field
    uasort($filteredSlides, function($a, $b) {
        return ($a['order'] ?? 1) <=> ($b['order'] ?? 1);
    });

    return array_values($filteredSlides);
}

// Function to get carousel configuration
function getCarouselConfig() {
    $config = loadLayoutConfig();
    return $config['carousel_config'];
}
?>
