<?php

namespace App\API;

use RuntimeException;

use App\Helpers\ResponseHelper;

class Routes
{
    private $routes;

    public function __construct()
    {
        $this->routes = [
            /* PetCare API */
            'api/petCare/delete/:id' => [
                'handler' => 'PetCareController@deletePetCarePost',
                'middleware' => 'false',
            ],
            'api/petCare/update/:id' => [
                'handler' => 'PetCareController@updatePetCarePost',
                'middleware' => false
            ],
            'api/petCare/add' => [
                'handler' => 'PetCareController@addNewPetCarePost',
                'middleware' => false
            ],
            "api/petCare/status/:status" => [
                'handler' => 'PetCareController@getAllPetCarePostsByStatus',
                'middleware' => false
            ],
            "api/petCare" => [
                'handler' => 'PetCareController@getAllPetCarePosts',
                'middleware' => false
            ],
            /* Appointments API */
            "api/appointments/delete" => [
                'handler' => "AppointmentsController@deleteAppointmentByID",
                'middleware' => false
            ],
            "api/appointments/add" => [
                'handler' => "AppointmentsController@addNewAppointment",
                'middleware' => false
            ],
            "api/appointments/requestID/:requestID" => [
                'handler' => "AppointmentsController@getAppointmentByRequestID",
                'middleware' => false
            ],
            "api/appointments/:id" => [
                'handler' => "AppointmentsController@getAppointmentByID",
                'middleware' => false
            ],
            "api/appointments" => [
                'handler' => "AppointmentsController@getAllAppointments"
            ],
            /* Notifications API Routes */
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
            /* Posts API Routes */
            "api/posts/interaction/delete/:postID/:userID" => [
                'handler' => "PostsInteractionController@deletePostInteraction",
                'middleware' => false
            ],
            "api/posts/interaction/add" => [
                'handler' => "PostsInteractionController@addNewPostInteraction",
                'middleware' => false
            ],
            "api/posts/approvalStatus/update/:postType/:postID" => [
                'handler'  => "PostsController@updatePostApprovalStatus",
                'middleware' => false
            ],
            "api/posts/media/update/:postID" => [
                'handler' => "PostsController@updatePostMedia",
                'middleware' => false
            ],
            "api/posts/event/add" => [
                'handler' => "PostsController@addNewEventPost",
                'middleware' => false
            ],
            "api/posts/media/add" => [
                'handler' => "PostsController@addNewPostMedia",
                'middleware' => false
            ],
            "api/posts/add" => [
                'handler' => 'PostsController@addNewPost',
                'middleware' => false
            ],
            "api/posts/id/postType/:id/:typeOfPost" => [
                'handler' => 'PostsController@getAllPostsByIDAndTypeOfPost',
                'middleware' => false
            ],
            "api/posts/userID/:userID/:approvalStatus" => [
                'handler' => 'PostsController@getAllPostsByUserIDAndStatus',
                'middleware' => false
            ],
            "api/posts/:typeOfPost" => [
                'handler' => 'PostsController@getAllPostsByTypeOfPost',
                'middleware' => false
            ],
            "api/posts/approvalStatus/:approvalStatus" => [
                'handler' => 'PostsController@getAllPostsPetFeeds',
                'middleware' => false
            ],
            /* Requests API Routes */
            "api/requests/update/status/:id" => [
                "handler" => "RequestsController@updateRequestStatus",
                "middleware" => false
            ],
            "api/requests/userOwnerID/id/:userOwnerID/:id" => [
                "handler" => "RequestsController@getUserRequestByUserOwnerIDAndID",
                "middleware" => false
            ],
            "api/requests/userID/status/:userID/:status" => [
                "handler" => "RequestsController@getUserRequestByUserIDAndStatus",
                "middleware" => false,
            ],
            "api/requests/userOwnerID/status/:userOwnerID/:status" => [
                "handler" => "RequestsController@getAllRequestsByUserOwnerIDAndStatus",
                "middleware" => false
            ],
            "api/requests/status/:status" => [
                "handler" => "RequestsController@getAllRequestsByStatus",
                "middleware" => false
            ],
            "api/requests/add" => [
                "handler" => "RequestsController@addNewUserRequest",
                "middleware" => false
            ],
            "api/requests/userID/id/:userID/:id" => [
                "handler" => "RequestsController@getUserRequestByUserIDAndID",
                "middleware" => false
            ],
            "api/requests/userID/:userID" => [
                "handler" => "RequestsController@getUserRequestByUserID",
                "middleware" => false
            ],
            "api/requests/:id" => [
                "handler" => "RequestsController@getRequestByID",
                "middleware" => false
            ],
            /* Pets API Routes */
            "api/pets/ageCategories" => [
                "handler" => "PetsController@getAllPetsAgeCategories",
                "middleware" => false,
            ],
            "api/pets/breeds/:petType" => [
                "handler" => "PetsController@getAllPetBreedsByType",
                "middleware" => false
            ],
            "api/pets/update/adoptionStatus/:id" => [
                "handler" => "PetsController@updatePetAdoptionStatus",
                "middleware" => false
            ],
            "api/pets/update/approvalStatus/:id" => [
                "handler" => "PetsController@updatePetApprovalStatus",
                "middleware" => false
            ],
            "api/pets/delete/:id" => [
                "handler" => "PetsController@deletePet",
                "middleware" => false
            ],
            "api/pets/update/:id" => [
                "handler" => "PetsController@updatePetData",
                "middleware" => false
            ],
            "api/pets/add" => [
                "handler" => "PetsController@addNewPet",
                "middleware" => false
            ],
            "api/pets/id/adoptionStatus/:id/:adoptionStatus" => [
                "handler" => "PetsController@getPetByIDAndAdoptionStatus",
                "middleware" => false
            ],
            "api/pets/userID/approvalStatus/:userID/:approvalStatus" => [
                "handler" => "PetsController@getAllPetsByUserIDAndApprovalStatus",
                "middleware" => false
            ],
            "api/pets/id/:id" => [
                "handler" => "PetsController@getPetByID",
                "middleware" => false
            ],
            "api/pets/petTypes" => [
                "handler" => "PetsController@getAllPetTypes",
                "middleware" => false
            ],
            "api/pets/status/:status" => [
                "handler" => "PetsController@getAllPetsByAdoptionStatus",
                "middleware" => false
            ],
            "/api/pets" => [
                "handler" => "PetsController@getAllPets",
                "middleware" => false
            ],
            /* Users API Routes */
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
            /* Authentication API Routes */
            "api/auth/changePassword" => [
                "handler" => "AuthenticationController@changePassword",
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
        try {
            foreach ($this->routes as $route => $handler) {
                if ($route === $url_request) {
                    return $handler;
                }

                $route_parts = explode('/', $route);
                $request_parts = explode('/', $url_request);

                if (count($route_parts) === count($request_parts)) {
                    $params = [];
                    $is_match = true;

                    for ($i = 0; $i < count($route_parts); $i++) {
                        if (strpos($route_parts[$i], ':') === 0) {
                            $param_name = substr($route_parts[$i], 1);  // Remove leading ':'
                            $params[$param_name] = $request_parts[$i];
                        } else if ($route_parts[$i] !== $request_parts[$i]) {
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
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
