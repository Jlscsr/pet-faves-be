<?php

class Route
{
    private $routes;

    public function __construct()
    {
        $this->routes = [
            "api/pet/pet-feeds/id/:id" => [
                "handler" => "PetFeedsController@getAllPetsFromPetFeedsByID",
                "middleware" => false
            ],
            "api/pet/pet-feeds/approval-status/update/:id" => [
                "handler" => "PetFeedsController@updatePetFeedsApprovalStatus",
                "middleware" => false
            ],
            "api/pet/pet-feeds/add" => [
                "handler" => "PetFeedsController@addNewPetToPetFeeds",
                "middleware" => false
            ],
            "api/pet/pet-feeds/status/:status" => [
                "handler" => "PetFeedsController@getAllPetsFromPetFeedsByStatus",
                "middleware" => false
            ],
            "api/pet/pet-feeds" => [
                "handler" => "PetFeedsController@getAllPetsFromPetFeeds",
                "middleware" => false
            ],
            "api/approvalQueue/id/:id" => [
                "handler" => "ApprovalQueueController@getApprovalQueueByID",
                "middleware" => false
            ],
            "api/approvalQueue/add" => [
                "handler" => "ApprovalQueueController@addNewApprovalQueue",
            ],
            "api/request/adoption/status/update/:id" => [
                "handler" => "AdoptionRequestsController@updateUserRequestStatus",
                "middleware" => false
            ],
            "api/request/adoption/add" => [
                "handler" => "AdoptionRequestsController@addNewUserRequest",
                "middleware" => false
            ],
            "api/request/:id" => [
                "handler" => "AdoptionRequestsController@getUserRequestByUserID",
                "middleware" => false
            ],
            "api/requests/adoption/:status" => [
                "handler" => "AdoptionRequestsController@getAllUserRequestsByStatus",
                "middleware" => false
            ],
            "api/pets/ageCategories" => [
                "handler" => "PetsController@getAllPetsAgeCategories",
                "middleware" => false,
            ],
            "api/pets/label/:label" => [
                "handler" => "PetsController@getAllPetsByLabel",
                "middleware" => false
            ],
            "api/pet/breeds/:petType" => [
                "handler" => "PetsController@getAllPetBreedsByType",
                "middleware" => false
            ],
            "api/pet/add" => [
                "handler" => "PetsController@addNewPet",
                "middleware" => false
            ],
            "api/pet/:id" => [
                "handler" => "PetsController@getPetByID",
                "middleware" => false
            ],
            "api/pets/types" => [
                "handler" => "PetsController@getAllPetTypes",
                "middleware" => false
            ],
            "api/pets" => [
                "handler" => "PetsController@getAllPets",
                "middleware" => false
            ],
            "api/user/id/:id" => [
                "handler" => "UsersController@getUserByID",
                "middleware" => false
            ],
            "api/user/email" => [
                "handler" => "UsersController@getUserByEmail",
                "middleware" => false
            ],
            "api/auth/validate" => [
                "handler" => "AuthenticationController@validateToken",
                "middleware" => false
            ],
            "api/auth/logout" => [
                "handler" => "AuthenticationController@logout",
                "middleware" => false
            ],
            "api/auth/register" => [
                "handler" => "AuthenticationController@register",
                "middleware" => false
            ],
            "api/auth/login" => [
                "handler" => "AuthenticationController@login",
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
