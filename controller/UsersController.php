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

    public function getUserByID($param)

    {
        try {
            if (empty($param) || !isset($param['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing id parameter", 400);
                return;
            }

            $id = (int) $param['id'];

            $user = $this->usersModel->getUserByID($id);

            if (!$user) {
                ResponseHelper::sendErrorResponse("User not found", 404);
                return;
            }

            $responseData = [
                'id' => $user['id'],
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'phoneNumber' => $user['phoneNumber'],
                'gender' => $user['gender'],
                'address' => $user['address'],
                'region' => $user['region'],
                'province' => $user['province'],
                'city' => $user['city'],
                'barangay' => $user['barangay'],
                'validIDImageURL' => $user['validIDImageURL'] ?? null,
                'selfieImageURL' => $user['selfieImageURL'] ?? null,
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at']
            ];

            ResponseHelper::sendSuccessResponse($responseData, 'User found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
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
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'phoneNumber' => $user['phoneNumber'],
                'gender' => $user['gender'],
                'address' => $user['address'],
                'region' => $user['region'],
                'province' => $user['province'],
                'city' => $user['city'],
                'barangay' => $user['barangay'],
                'validIDImageURL' => $user['validIDImageURL'] ?? null,
                'selfieImageURL' => $user['selfieImageURL'] ?? null,
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at']
            ];

            ResponseHelper::sendSuccessResponse($responseData, 'User found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateUserData($params, $payload)
    {
        try {
            if (empty($payload) || !isset($params['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing id parameter", 400);
                return;
            }

            $userID = $params['id'];

            $isUserDataUpdated = $this->usersModel->updateUserData($userID, $payload);

            if (!$isUserDataUpdated) {
                ResponseHelper::sendErrorResponse("User not found", 404);
                return;
            }

            ResponseHelper::sendSuccessResponse([], 'User data updated successfully');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
