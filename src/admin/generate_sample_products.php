<?php
/**
 * Product Sample Data Generator
 * 
 * Generates 5 realistic products for each subcategory
 * Total: 45 subcategories × 5 products = 225 products
 */

require_once '../db_connect.php';
require_once '../models/Category.php';
require_once '../models/Product.php';

$categoryModel = new Category();
$productModel = new Product();

// Get all subcategories
$stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NOT NULL ORDER BY parent_id, id");
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($subcategories) . " subcategories\n";
echo "Generating 5 products per subcategory...\n\n";

// Product templates by category type
$productTemplates = [
    'televisions' => [
        ['Samsung 55" QLED 4K Smart TV', 89999, 'High-end QLED display with quantum dot technology'],
        ['LG 65" OLED C3 Smart TV', 129999, 'Premium OLED with perfect blacks'],
        ['Sony Bravia 50" LED 4K', 69999, 'Excellent picture quality with HDR'],
        ['TCL 43" Android TV', 39999, 'Budget-friendly smart TV'],
        ['Hisense 32" HD Ready TV', 24999, 'Compact HD television']
    ],
    'cameras' => [
        ['Canon EOS R6 Mirrorless', 249999, 'Professional full-frame mirrorless camera'],
        ['Sony A7 IV Body', 279999, 'Versatile hybrid camera'],
        ['Nikon Z6 II Kit', 189999, 'Advanced mirrorless with dual processors'],
        ['Fujifilm X-T4', 169999, 'APS-C flagship with IBIS'],
        ['GoPro Hero 12 Black', 44999, 'Action camera with 5.3K video']
    ],
    'processors' => [
        ['Intel Core i9-14900K', 64999, '24-core flagship processor'],
        ['AMD Ryzen 9 7950X', 69999, '16-core Zen 4 processor'],
        ['Intel Core i7-14700K', 44999, 'High-performance gaming CPU'],
        ['AMD Ryzen 7 7800X3D', 49999, 'Gaming-optimized with 3D V-Cache'],
        ['Intel Core i5-14600K', 29999, 'Excellent mid-range processor']
    ],
    'graphics-cards' => [
        ['NVIDIA RTX 4090 24GB', 199999, 'Ultimate gaming graphics card'],
        ['AMD Radeon RX 7900 XTX', 129999, 'High-end RDNA 3 GPU'],
        ['NVIDIA RTX 4070 Ti', 89999, 'Premium 1440p gaming'],
        ['AMD RX 7800 XT', 64999, 'Excellent 1440p performance'],
        ['NVIDIA RTX 4060 Ti', 49999, 'Mainstream gaming GPU']
    ],
    'android-phones' => [
        ['Samsung Galaxy S24 Ultra', 149999, 'Flagship with S Pen'],
        ['Google Pixel 8 Pro', 119999, 'Pure Android experience'],
        ['OnePlus 12', 89999, 'Fast charging flagship'],
        ['Xiaomi 14 Pro', 79999, 'Premium camera phone'],
        ['Samsung Galaxy A54', 44999, 'Mid-range with great features']
    ],
    'headphones' => [
        ['Sony WH-1000XM5', 39999, 'Industry-leading noise cancellation'],
        ['Bose QuietComfort Ultra', 44999, 'Premium comfort and ANC'],
        ['Apple AirPods Max', 59999, 'Spatial audio excellence'],
        ['Sennheiser Momentum 4', 34999, 'Audiophile wireless headphones'],
        ['JBL Tune 770NC', 14999, 'Budget ANC headphones']
    ]
];

// Generic product generator for categories without specific templates
function generateGenericProducts($subcatName, $subcatSlug, $count = 5) {
    $products = [];
    $basePrice = rand(15000, 150000);

    for ($i = 1; $i <= $count; $i++) {
        $price = $basePrice + (rand(-5000, 10000) * $i);
        $products[] = [
            "$subcatName Model $i",
            $price,
            "High-quality $subcatName with advanced features"
        ];
    }

    return $products;
}

$insertedCount = 0;
$errors = [];

foreach ($subcategories as $subcat) {
    $slug = $subcat['slug'];
    $name = $subcat['name_en'];
    
    // Get products for this subcategory
    $products = $productTemplates[$slug] ?? generateGenericProducts($name, $slug);
    
    echo "Processing: {$name} ({$slug})\n";
    
    foreach ($products as $index => $prod) {
        try {
            $sku = strtoupper(substr($slug, 0, 3)) . '-' . str_pad($subcat['id'], 3, '0', STR_PAD_LEFT) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            $productData = [
                'name_en' => $prod[0],
                'slug' => strtolower(str_replace([' ', '"', "'"], ['-', '', ''], $prod[0])),
                'description_en' => $prod[2],
                'short_description_en' => substr($prod[2], 0, 100),
                'category_id' => $subcat['id'],
                'brand_id' => null,
                'price' => $prod[1],
                'discount_percentage' => rand(0, 30),
                'stock_quantity' => rand(5, 100),
                'sku' => $sku,
                'is_active' => 1,
                'is_featured' => $index === 0 ? 1 : 0,
                'is_new_arrival' => rand(0, 1),
                'is_best_seller' => $index < 2 ? 1 : 0
            ];
            
            $productModel->create($productData);
            $insertedCount++;
            
        } catch (Exception $e) {
            $errors[] = "Error inserting {$prod[0]}: " . $e->getMessage();
        }
    }
    
    echo "  ✓ Inserted 5 products\n";
}

echo "\n===========================================\n";
echo "SUMMARY\n";
echo "===========================================\n";
echo "Total products inserted: $insertedCount\n";
echo "Subcategories processed: " . count($subcategories) . "\n";

if (!empty($errors)) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nDone!\n";


