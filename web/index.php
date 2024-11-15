<?php


use App\API\Router;

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();  // Print the error message
}
