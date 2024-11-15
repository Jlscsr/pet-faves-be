<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\API\Router;

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    $uri = $_SERVER['REQUEST_URI'];  // Full URI including query string, e.g., "/api/pets?limit=6&offset=0"
    $path = parse_url($uri, PHP_URL_PATH);  // Just the path, e.g., "/api/pets"

    $router = new Router();
    $router->handleRequest($path);  // Pass the URL to your Router logic
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "Error: " . $e->getMessage();
}
