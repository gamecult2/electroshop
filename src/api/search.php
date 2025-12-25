<?php
// api/search.php - Search API endpoint

header('Content-Type: application/json');
require_once '../includes/functions.php';
require_once '../models/Search.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$endpoint = 'search';

if (is_rate_limited($ip, $endpoint)) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit;
}

record_api_request($ip, $endpoint);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get parameters
$query = $_GET['q'] ?? '';
$categoryId = $_GET['category_id'] ?? null;
$brandId = $_GET['brand_id'] ?? null;
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$minRating = $_GET['min_rating'] ?? null;
$inStock = $_GET['in_stock'] ?? null;
$sort = $_GET['sort'] ?? 'relevance';
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 20);

// Validate parameters
if ($minPrice !== null && !is_numeric($minPrice)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid min price']);
    exit;
}

if ($maxPrice !== null && !is_numeric($maxPrice)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid max price']);
    exit;
}

if ($minRating !== null && (!is_numeric($minRating) || $minRating < 1 || $minRating > 5)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid minimum rating']);
    exit;
}

// Prepare filters array
$filters = [];
if ($categoryId) $filters['category_id'] = $categoryId;
if ($brandId) $filters['brand_id'] = $brandId;
if ($minPrice) $filters['price_min'] = $minPrice;
if ($maxPrice) $filters['price_max'] = $maxPrice;
if ($minRating) $filters['min_rating'] = $minRating;
if ($inStock) $filters['in_stock'] = 1;

// Perform search
$searchModel = new Search();
$searchResults = $searchModel->searchProducts($query, $filters, $sort, $page, $limit);

// Save search history for logged-in users
if (is_logged_in() && !empty($query)) {
    save_search_history(get_current_user_id(), $query, $searchResults['total']);
}

echo json_encode([
    'success' => true,
    'data' => $searchResults,
    'query' => $query,
    'filters' => $filters
]);
?>
