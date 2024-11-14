<?php

use Helpers\JWTHelper;
use Helpers\ResponseHelper;
use Helpers\CookieManager;

use Validators\HTTPRequestValidator;

use Models\UsersModel;

class UsersController
{
    private $jwt;
    private $usersModel;
    private $cookieManager;
    private $acceptableParamsKeys = ['id'];
    private $acceptablePayloadKeys = ['id', 'firstName', 'lastName', 'email', 'phoneNumber', 'gender', 'password', 'address', 'region', 'province', 'city', 'barangay', 'validIDImageURL', 'selfieImageURL', 'role', 'age', 'job', 'lifeStyle', 'livingStatus', 'petCareTimeCommitment', 'budgetForPetCare'];

    public function __construct($pdo)
    {
        $this->jwt = new JWTHelper();
        $this->usersModel = new UsersModel($pdo);
        $this->cookieManager = new CookieManager();
    }

    public function getUserByID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];

            $user = $this->usersModel->getUserByID($id);

            if (!$user) {
                return ResponseHelper::sendSuccessResponse([], "User not found");
            }

            unset($user['password']);

            return ResponseHelper::sendSuccessResponse($user, 'User found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }


    public function getUserByEmail()
    {
        try {
            $this->cookieManager->validateCookiePressence();

            $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

            if (!$this->jwt->validateToken($token)) {
                $this->cookieManager->resetCookieHeader();
                return ResponseHelper::sendUnauthorizedResponse('Invalid token');
            }

            $decodedData = $this->jwt->decodeJWTData($token);

            $email = $decodedData->email;

            $user = $this->usersModel->getUserByEmail($email);

            if (!$user) {
                $this->cookieManager->resetCookieHeader();
                return ResponseHelper::sendUnauthorizedResponse('Invalid token');
            }

            unset($user['password']);

            return ResponseHelper::sendSuccessResponse($user, 'User found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateUserData(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['id'];

            $isUserDataUpdated = $this->usersModel->updateUserData($userID, $payload);

            if (!$isUserDataUpdated) {
                return ResponseHelper::sendErrorResponse("User not found", 404);
            }

            return ResponseHelper::sendSuccessResponse([], 'User data updated successfully');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
