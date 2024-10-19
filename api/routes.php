<?php

class Route
{
    private $routes;

    public function __construct()
    {
        $this->routes = [
            "api/appointments/request/add" => [
                'handler' => "AppointmentsController@addNewRequestAppointment",
                'middleware' => false
            ],
            "api/appointments/request/:id" => [
                'handler' => "AppointmentsController@getRequestAppointmentByRequestID",
                'middleware' => false
            ],
            "api/notifications/status/update/:id/:userID" => [
                'handler' => 'NotificationsController@updateNotificationStatus',
                'middleware' => false
            ],
            "api/notifications/add" => [
                'handler' => 'NotificationsController@addNewNotification',
                'middleware' => false
            ],
            "api/notifications/:userID/:status" => [
                'handler' => 'NotificationsController@getAllNotificationsByUserIDAndStatus',
                'middleware' => false
            ],
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
            "api/request/update/status/:id" => [
                "handler" => "RequestsController@updateRequestStatus",
                "middleware" => false
            ],
            "api/request/typeOfRequest/:type" => [
                "handler" => "RequestsController@getRequestByTypeofRequest"
            ],
            "api/request/add" => [
                "handler" => "RequestsController@addNewUserRequest",
                "middleware" => false
            ],
            "api/request/user/:userID/:id" => [
                "handler" => "RequestsController@getUserRequestByUserIDAndID",
                "middleware" => false
            ],
            "api/request/user/:id" => [
                "handler" => "RequestsController@getUserRequestByUserID",
                "middleware" => false
            ],
            "api/request/:id" => [
                "handler" => "RequestsController@getRequestByID",
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
            "api/pets/status/:status" => [
                "handler" => "PetsController@getAllPetsByAdoptionStatus",
                "middleware" => false
            ],
            "api/pets" => [
                "handler" => "PetsController@getAllPets",
                "middleware" => false
            ],
            "api/user/update/:id" => [
                'handler' => "UsersController@updateUserData",
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

            // Split the route and request into parts
            $route_parts = explode('/', $route);
            $request_parts = explode('/', $url_request);

            // Check if the number of parts match
            if (count($route_parts) === count($request_parts)) {
                $params = [];
                $is_match = true;

                // Loop through each part to extract parameters or check for match
                for ($i = 0; $i < count($route_parts); $i++) {
                    // Check for dynamic parameter (starts with ':')
                    if (strpos($route_parts[$i], ':') === 0) {
                        $param_name = substr($route_parts[$i], 1);  // Remove leading ':'
                        $params[$param_name] = $request_parts[$i];
                    } else if ($route_parts[$i] !== $request_parts[$i]) {
                        // If parts do not match, mark as not matching
                        $is_match = false;
                        break;
                    }
                }

                // If everything matches, return the handler and parameters
                if ($is_match) {
                    return [
                        'handler' => $handler,
                        'params' => $params
                    ];
                }
            }
        }

        // If no match is found, return null
        return null;
    }
}
