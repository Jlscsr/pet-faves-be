<?php

echo "Hello, World!";
require_once dirname(__DIR__) . '/vendor/autoload.php';
echo "Hello, World!2";

use App\API\Router;

echo "Hello, World!3";
$url = parse_url(getenv('JAWSDB_URL'));
var_dump($url['user']);
var_dump($url['pass']);
var_dump($url['host']);
try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();  // Print the error message
}
echo "Hello, World!4";
