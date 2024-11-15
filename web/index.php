<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\API\Router;

$router = new Router();
$router->handleRequest();
