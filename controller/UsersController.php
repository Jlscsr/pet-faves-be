<?php

use Helpers\JWTHelper;
use Helpers\ResponseHelper;
use Helpers\HeaderHelper;
use Helpers\CookieManager;

use Models\UsersModel;

class UsersController
{
    private $jwt;
    private $usersModel;
    private $cookieManager;

    public function __construct($pdo)
    {
        $this->jwt = new JWTHelper();
        $this->usersModel = new UsersModel($pdo);
        $this->cookieManager = new CookieManager();

        HeaderHelper::setResponseHeaders();
    }


    public function getUserByEmail()
    {
        try {
            $this->cookieManager->validateCookiePressence();

            $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

            if (!$this->jwt->validateToken($token)) {
                $this->cookieManager->resetCookieHeader();
                ResponseHelper::sendUnauthorizedResponse('Invalid token');
                return;
            }

            $decodedData = $this->jwt->decodeJWTData($token);

            $email = $decodedData->email;

            $user = $this->usersModel->getUserByEmail($email);

            if (!$user) {
                $this->cookieManager->resetCookieHeader();
                ResponseHelper::sendUnauthorizedResponse('Invalid token');
                return;
            }

            $responseData = [
                'id' => $user['id'],
                'firstName' => $user['firstName'],
                'middleName' => $user['middleName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'phoneNumber' => $user['phoneNumber'],
                'gender' => $user['gender'],
                'address' => $user['address'],
                'region' => $user['region'],
                'province' => $user['province'],
                'city' => $user['city'],
                'barangay' => $user['barangay'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at']
            ];

            ResponseHelper::sendSuccessResponse($responseData, 'User found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
