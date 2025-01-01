<?php

namespace App\Controllers;

use App\Helpers\JWTHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\CookieManager;

use App\Validators\HTTPRequestValidator;

use App\Models\UsersModel;

use RuntimeException;

class UsersController
{
    private $jwt;
    private $usersModel;
    private $cookieManager;
    private $acceptableParamsKeys = ['id'];

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

            $response = $this->usersModel->getUserByID($id);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendSuccessResponse([], $response['message']);
            }

            unset($response['data']['password']);
            unset($response['data']['activationCode']);
            unset($response['data']['resetToken']);
            unset($response['data']['activationToken']);

            return ResponseHelper::sendSuccessResponse($response['data'], 'User found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }


    public function getUserByEmail()
    {
        try {
            $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

            if (!$this->jwt->validateToken($token)) {
                $this->cookieManager->resetCookieHeader();
                return ResponseHelper::sendUnauthorizedResponse('Invalid token');
            }

            $decodedData = $this->jwt->decodeJWTData($token);

            $email = $decodedData->email;

            $response = $this->usersModel->getUserByEmail($email);

            if ($response['status'] === 'failed') {
                $this->cookieManager->resetCookieHeader();
                return ResponseHelper::sendUnauthorizedResponse($response['message']);
            }

            unset($response['data']['password']);
            unset($response['data']['password']);
            unset($response['data']['activationCode']);
            unset($response['data']['resetToken']);
            unset($response['data']['activationToken']);

            return ResponseHelper::sendSuccessResponse($response['data'], 'User found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateUserData(array $params, array $payload)
    {
        try {
            $userID = $params['id'];

            $response = $this->usersModel->updateUserData($userID, $payload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            return ResponseHelper::sendSuccessResponse([], 'User data updated successfully');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
