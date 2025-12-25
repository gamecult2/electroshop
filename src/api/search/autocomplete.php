<?php
// api/search/autocomplete.php
header('Content-Type: application/json');
require_once '../../includes/functions.php';
require_once '../../models/Search.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$searchModel = new Search();
$suggestions = $searchModel->getAutocompleteSuggestions($query);

echo json_encode(['suggestions' => $suggestions]);
?>
