<?php
// Backward-compatible redirect for the retired compact catalog layout.
$query = $_GET;
header('Location: products.php' . ($query ? '?' . http_build_query($query) : ''), true, 301);
exit;
