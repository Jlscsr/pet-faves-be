<?php

echo "Hello, World!";
require_once dirname(__DIR__) . '/vendor/autoload.php';
echo "Hello, World!2";

use App\API\Router;

echo "Hello, World!3";
try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();  // Print the error message
}
echo "Hello, World!4";
