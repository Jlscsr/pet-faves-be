<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\API\Router;

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    $uri = $_SERVER['REQUEST_URI'];

    $router = new Router();
    $router->handleRequest($uri);  // Pass the URL to your Router logic
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "Error: " . $e->getMessage();
}
