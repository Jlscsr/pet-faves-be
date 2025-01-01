<?php

namespace App\API;

use RuntimeException;

use App\Helpers\ResponseHelper;

use App\API\Route;

class Routes
{
    private $routes = [];
    private $currentPrefix = null;

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes()
    {

        $this->group("/api/log", function () {
            $this->add('/error/fe/add', 'SystemLogController@logFrontendError')
                ->middleware(false)
                ->requiredRole('none');
        });

        $this->group('/api/inquiry', function () {
            $this->add('/send', 'InquiryController@sendNewInquiry')
                ->middleware(false)
                ->requiredRole('none');
        });

        // Group Routes for Donations API
        $this->group('/api/donations', function () {
            $this->add('/add', 'DonationsController@addNewDonation')
                ->middleware(true)
                ->requiredRole('admin');
        });

        // Group Routes for Reports API
        $this->group('/api/reports', function () {
            $this->add('', 'ReportsController@getAllReports')
                ->middleware(false)
                ->requiredRole('none');
        });

        // Group Routes for PetCare API
        $this->group('/api/petCare', function () {
            $this->add('/add', 'PetCareController@addNewPetCarePost')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/update/:id', 'PetCareController@updatePetCarePost')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/delete/:id', 'PetCareController@deletePetCarePost')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/status/:status', 'PetCareController@getAllPetCarePostsByStatus')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('', 'PetCareController@getAllPetCarePosts')
                ->middleware(false)
                ->requiredRole('none');
        });

        // Group Routes for Appointments API
        $this->group('/api/appointments', function () {
            $this->add('/delete', 'AppointmentsController@deleteAppointmentByID')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/add', 'AppointmentsController@addNewAppointment')
                ->middleware(true)
                ->requiredRole('customer');

            $this->add('/requestID/:requestID', 'AppointmentsController@getAppointmentByRequestID')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/:id', 'AppointmentsController@getAppointmentByID')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('', 'AppointmentsController@getAllAppointments')
                ->middleware(true)
                ->requiredRole('both');
        });

        // Group Routes for Posts API
        $this->group('/api/posts', function () {
            $this->add('/delete/id/postType/:postID/:postType', 'PostsController@deletePostByIdAndPostType')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/update/approvalStatus/postID/postType/:postID/:postType', 'PostsController@updatePostApprovalStatus')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/interaction/delete/:postID/:userID', 'PostsInteractionController@deletePostInteraction')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/interaction/add', 'PostsInteractionController@addNewPostInteraction')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/media/update/:postID', 'PostsController@updatePostMedia')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/event/add', 'PostsController@addNewEventPost')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/media/add', 'PostsController@addNewPostMedia')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/add', 'PostsController@addNewPost')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/id/postType/:id/:typeOfPost', 'PostsController@getAllPostsByIDAndTypeOfPost')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/userID/:userID/:approvalStatus', 'PostsController@getAllPostsByUserIDAndStatus')
                ->middleware(true)
                ->requiredRole('both');

            /**
             * ! THIS ROUTE IS NOT USED ANYMORE IN THE APPLICATION
             */
            $this->add('/:typeOfPost', 'PostsController@getAllPostsByTypeOfPost')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/approvalStatus/:approvalStatus', 'PostsController@getAllPostsPetFeeds')
                ->middleware(true)
                ->requiredRole('none');
        });

        // Group Routes for Requests API
        $this->group('/api/requests', function () {
            $this->add('/check/userID/petID/:userID/:petID', 'RequestsController@checkIfUserAlreadyRequestedPet')
                ->middleware(true)
                ->requiredRole('customer');

            $this->add('/multiple/cancel', 'RequestsController@cancelMultitpleRequests')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/add', 'RequestsController@addNewUserRequest')
                ->middleware(true)
                ->requiredRole('customer');

            $this->add('/userID/id/:userID/:id', 'RequestsController@getUserRequestByUserIDAndID')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/userID/typeOfRequest/:userID/:typeOfRequest', 'RequestsController@getUserRequestByUserIDAndTypeOfRequest')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/status/typeOfRequest/:status/:typeOfRequest', 'RequestsController@getAllRequestsByStatusAndTypeOfRequest')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/userOwnerID/status/:userOwnerID/:status', 'RequestsController@getAllRequestsByUserOwnerIDAndStatus')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/userID/status/:userID/:status', 'RequestsController@getUserRequestByUserIDAndStatus')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/userOwnerID/id/:userOwnerID/:id', 'RequestsController@getUserRequestByUserOwnerIDAndID')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/update/status/:id', 'RequestsController@updateRequestStatus')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/update/typeOfRequest/:id', 'RequestsController@updateRequestTypeOfRequest')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/return', 'RequestsController@getAllReturnRequests')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/return/status/:status', 'RequestsController@getAllReturnRequestsByStatus')
                ->middleware(true)
                ->requiredRole('both');
        });

        // Group Routes for Pets API
        $this->group('/api/pets', function () {

            $this->add("/colors/type/breed/:type/:breed", "PetsController@getAllPetColorsByTypeAndBreed")
                ->middleware(true)
                ->requiredRole("none");

            $this->add('/add', 'PetsController@addNewPet')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/petTypes', 'PetsController@getAllPetTypes')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/id/adoptionStatus/:id/:adoptionStatus', 'PetsController@getPetByIDAndAdoptionStatus')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/delete/id/:id', 'PetsController@deletePet')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/update/id/:id', 'PetsController@updatePetData')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/update/approvalStatus/:id', 'PetsController@updatePetApprovalStatus')
                ->middleware(true)
                ->requiredRole('admin');

            $this->add('/update/adoptionStatus/:id', 'PetsController@updatePetAdoptionStatus')
                ->middleware(true)
                ->requiredRole('both');

            $this->add('/breeds/:petType', 'PetsController@getAllPetBreedsByType')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/ageCategories', 'PetsController@getAllPetsAgeCategories')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/adoptionStatus/:status', 'PetsController@getAllPetsByAdoptionStatus')
                ->middleware(middleware: false)
                ->requiredRole('none');

            $this->add('/id/:id', 'PetsController@getPetByID')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/all', 'PetsController@getAllPets')
                ->middleware(true)
                ->requiredRole('none');
        });

        // Group Routes for Users API
        $this->group('/api/user', function () {
            $this->add('/email', 'UsersController@getUserByEmail')
                ->middleware(true)
                ->requiredRole('none');

            $this->add('/id/:id', 'UsersController@getUserByID')
                ->middleware(true)
                ->requiredRole('none');

            $this->add('/update/:id', 'UsersController@updateUserData')
                ->middleware(true)
                ->requiredRole('customer');
        });

        // Group Routes for Tokens API
        $this->group('/api/tokens', function () {
            $this->add('/validate/reset/:token', 'TokensController@validateResetToken')
                ->middleware(false)
                ->requiredRole('none');
        });

        // Group Routes for Authentication API
        $this->group('/api/auth', function () {
            $this->add('/login', 'AuthenticationController@login')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/register', 'AuthenticationController@register')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/logout', 'AuthenticationController@logout')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/validate', 'AuthenticationController@validateToken')
                ->middleware(true)
                ->requiredRole('none');

            $this->add('/changePassword', 'AuthenticationController@changePassword')
                ->middleware(true)
                ->requiredRole('none');

            $this->add('/verifyAccount', 'AuthenticationController@verifyAccount')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/resendVerificationEmail', 'AuthenticationController@resendVerificationEmail')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/forgotPassword', 'AuthenticationController@forgotPassword')
                ->middleware(false)
                ->requiredRole('none');

            $this->add('/resetPassword', 'AuthenticationController@resetPassword')
                ->middleware(false)
                ->requiredRole('none');
        });
    }

    private function  group(string $prefix, callable $callback)
    {
        $this->currentPrefix = $prefix;
        $callback();
        $this->currentPrefix = null;
    }

    private function add(string $route, string $handler): Route
    {
        $fullPath = $this->currentPrefix ? $this->currentPrefix . $route : $route;
        $routeInstance = new Route($handler);
        $this->routes[$fullPath] = $routeInstance;
        return $routeInstance;
    }

    public function getRoute($url_request)
    {
        try {
            foreach ($this->routes as $route => $handler) {
                if ($route === $url_request) {
                    // Return the original Route object for exact matches
                    return $handler;
                }

                $route_parts = explode('/', $route);
                $request_parts = explode('/', $url_request);

                if (count($route_parts) === count($request_parts)) {
                    $params = [];
                    $is_match = true;

                    for ($i = 0; $i < count($route_parts); $i++) {
                        if (strpos($route_parts[$i], ':') === 0) {
                            $param_name = substr($route_parts[$i], 1); // Remove leading ':'
                            $params[$param_name] = $request_parts[$i];
                        } else if ($route_parts[$i] !== $request_parts[$i]) {
                            $is_match = false;
                            break;
                        }
                    }

                    // If everything matches, add params dynamically to the handler object
                    if ($is_match) {
                        $response = clone $handler; // Clone to avoid affecting the original
                        if (!empty($params)) {
                            $response->params = $params; // Add params only if they exist
                        }
                        return $response;
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
