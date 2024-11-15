<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\API\Router;

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();  // Print the error message
} catch (RuntimeException $e) {
    echo "Runtime Error: " . $e->getMessage();  // Print the error message
}
