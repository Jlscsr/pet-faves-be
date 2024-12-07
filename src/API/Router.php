<?php

namespace App\API;

use RuntimeException;
use Exception;

use App\Configs\DatabaseConnection;

use App\Helpers\HeaderHelper;
use App\Helpers\ResponseHelper;

use App\API\Routes;

class Router
{
    private $pdo;
    private $routes;

    public function __construct()
    {
        // Connect to the database
        $this->pdo = DatabaseConnection::connect();

        // Set the headers
        HeaderHelper::SendPreflighthHeaders();
        HeaderHelper::setResponseHeaders();

        // Initialize route
        $this->routes = new Routes();
    }

    public function handleRequest($url)
    {
        try {
            // Get the URL from the query parameter
            $request_method = strtoupper(trim($_SERVER['REQUEST_METHOD']));

            $handler = $this->routes->getRoute($url);

            if (!$handler) {
                throw new RuntimeException("Route not found for URL: $url");
            }

            // Check if middleware is required
            $middleware_required = $handler->middleware;

            if ($middleware_required) {
                $this->handleMiddleware($handler->requiredRole);
            }

            $handlerDefinition = $handler->handler;

            if (empty($handlerDefinition)) {
                throw new RuntimeException("Handler not defined for route.");
            }

            list($controller, $method) = explode('@', $handlerDefinition);

            if (empty($controller) || empty($method)) {
                throw new RuntimeException("Invalid handler format. Expected 'Controller@method'.");
            }

            // Get the controller and method
            $this->processRequest($controller, $method, $handler, $request_method);
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    private function handleMiddleware($requiredRole)
    {
        try {
            $midllewareClass = 'App\\Middlewares\\RequestMiddleware';
            $middleware = new $midllewareClass($requiredRole);

            $response = $middleware->getResponse();

            if ($response['status'] === 'failed') {
                ResponseHelper::sendUnauthorizedResponse($response['message']);
                exit;
            }
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    private function processRequest($controller, $method, $handler, $request_method)
    {
        try {
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
        } catch (Exception $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    private function handleGet($controller, $method, $handler)
    {
        if (property_exists($handler, 'params')) {
            $controller->$method($handler->params);
        } else {
            $controller->$method();
        }
    }

    private function handlePost($controller, $method, $handler, $payload)
    {
        if ($payload === null) {
            return ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
        }

        if (property_exists($handler, 'params') && !empty($handler->params)) {
            $controller->$method($handler->params, $payload);
            return;
        } else {
            $controller->$method($payload);
        }
    }

    private function handlePut($controller, $method, $handler, $payload)
    {
        if ($payload === null) {
            return ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
        }

        $controller->$method($handler->params, $payload);
    }

    private function handleDelete($controller, $method, $handler)
    {
        $controller->$method($handler->params);
    }
}
