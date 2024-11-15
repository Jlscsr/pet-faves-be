<?php

namespace App\API;

use App\Configs\DatabaseConnection;
use App\Helpers\HeaderHelper;
use App\Helpers\ResponseHelper;
use App\API\Routes;

use RuntimeException;


class Router
{
    private $pdo;
    private $routes;

    public function __construct()
    {
        // Connect to the database
        $this->pdo = DatabaseConnection::connect();

        // Initialize route
        $this->routes = new Routes();
    }

    public function handleRequest()
    {
        echo $_GET['url'];
        // Set headers
        HeaderHelper::SendPreflighthHeaders();
        HeaderHelper::setResponseHeaders();

        // Get the URL from the query parameter
        $url = $_GET['url'] ?? '';
        $request_method = strtoupper(trim($_SERVER['REQUEST_METHOD']));

        // Get the handler for the route
        $handler = $this->routes->get_route($url);

        // Check if middleware is required
        $middleware_required = isset($handler['middleware']) && is_array($handler['middleware']) && $handler['middleware']['required'];

        try {
            if ($middleware_required) {
                $this->handleMiddleware($handler['middleware']['handler']);
            }

            // Get the controller and method
            list($controller, $method) = explode('@', is_array($handler['handler']) ? $handler['handler']['handler'] : $handler['handler']);
            $this->processRequest($controller, $method, $handler, $request_method);
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    private function handleMiddleware($middleware)
    {
        try {
            require_once dirname(__DIR__) . '/middleware/' . $middleware . '.php';
            $is_valid = new $middleware();

            if (!$is_valid) {
                ResponseHelper::sendUnauthorizedResponse('Invalid Token or User is not authorized');
                return;
            }
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
            return;
        }
    }

    private function processRequest($controller, $method, $handler, $request_method)
    {
        $controllerClass = 'App\\Controllers\\' . $controller;
        $controller = new $controllerClass($this->pdo);

        $payload = json_decode(file_get_contents('php://input'), true);

        switch ($request_method) {
            case 'GET':
                $this->handleGet($controller, $method, $handler);
                break;
            case 'POST':
                $this->handlePost($controller, $method, $handler, $payload);
                break;
            case 'PUT':
                $this->handlePut($controller, $method, $handler, $payload);
                break;
            case 'DELETE':
                $this->handleDelete($controller, $method, $handler);
                break;
            default:
                ResponseHelper::sendErrorResponse('Invalid Request Method', 400);
                break;
        }
    }

    private function handleGet($controller, $method, $handler)
    {
        if (isset($handler['params'])) {
            $controller->$method($handler['params']);
        } else {
            $controller->$method();
        }
    }

    private function handlePost($controller, $method, $handler, $payload)
    {
        if ($payload === null) {
            ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
            return;
        }

        if (isset($handler['params'])) {
            $controller->$method($handler['params'], $payload);
            return;
        } else {
            $controller->$method($payload);
        }
    }

    private function handlePut($controller, $method, $handler, $payload)
    {
        if ($payload === null) {
            ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
            return;
        }

        $controller->$method($handler['params'], $payload);
    }

    private function handleDelete($controller, $method, $handler)
    {
        $controller->$method($handler['params']);
    }
}
