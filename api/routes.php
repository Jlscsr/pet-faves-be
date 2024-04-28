<?php

class Route
{
    private $routes;

    public function __construct()
    {
        $this->routes = [
            "api/pet/add" => [
                "handler" => "PetsController@addNewPet",
                "middleware" => false
            ],
            "api/pet/all" => [
                "handler" => "PetsController@getAllPets",
                "middleware" => false
            ]

        ];
    }

    public function get_route($url_request)
    {
        foreach ($this->routes as $route => $handler) {
            // Check for direct match
            if ($route === $url_request) {
                return $handler;
            }

            // Check for dynamic parameters
            $route_parts = explode('/', $route);
            $requests_parts = explode('/', $url_request);

            if (count($route_parts) === count($requests_parts)) {
                $params = [];
                for ($i = 0; $i < count($route_parts); $i++) {
                    if (strpos($route_parts[$i], ':') === 0) {
                        $param_name = substr($route_parts[$i], 1);
                        $params[$param_name] = $requests_parts[$i];
                    } else if ($route_parts[$i] !== $requests_parts[$i]) {
                        break;
                    }
                }

                if (!empty($params)) {
                    return [
                        'handler' => $handler,
                        'params' => $params
                    ];
                }
            }
        }

        return null;
    }
}
