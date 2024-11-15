<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\API\Router;

try {
    $url = $_GET['url'] ?? '/';  // Capture the routed URL
    error_log("Routing URL: $url");  // Debug the captured URL

    $router = new Router();
    $router->handleRequest($url);  // Pass the URL to your Router logic
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error: " . $e->getMessage());
}
