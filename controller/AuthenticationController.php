<?php

use Helpers\JWTHelper;
use Helpers\ResponseHelper;
use Helpers\HeaderHelper;
use Helpers\CookieManager;

use Models\UsersModel;

require_once dirname(__DIR__) . '/config/load_env.php';

class AuthenticationController
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

    public function register($payload)
    {
        try {
            if (!is_array($payload) || empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty");
                return;
            }

            $password = $payload['password'];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
            $payload['password'] = $hashed_password;

            $response = $this->usersModel->addNewUser($payload);

            if (!$response) {
                ResponseHelper::sendErrorResponse('Failed to register new customer', 500);
                return;
            }

            ResponseHelper::sendSuccessResponse([], 'User registered successfully', 201);
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
            return;
        }
    }

    public function login($payload)
    {
        if (!is_array($payload) || empty($payload)) {
            ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
            return;
        }

        $email = $payload['email'];
        $password = $payload['password'];

        $response = $this->usersModel->getUserByEmail($email);

        if (!is_array($response) && !$response) {
            ResponseHelper::sendErrorResponse('Missing email payload');
            return;
        }

        if (empty($response)) {
            ResponseHelper::sendErrorResponse('Incorrect Email');
            return;
        }

        $stored_password = $response['password'];

        if (!password_verify($password, $stored_password)) {
            ResponseHelper::sendErrorResponse('Incorrect Password');
            return;
        }

        // 5hrs expiry time for token
        $expiry_date = time() + (5 * 3600);

        $to_be_tokenized = [
            "id" => $response['id'],
            'email' => $response['email'],
            'password' => $response['password'],
            'rl' => $response['role'],
            'expiry_date' => $expiry_date
        ];

        $token = $this->jwt->encodeDataToJWT($to_be_tokenized);

        $this->cookieManager->setCookiHeader($token, $expiry_date);

        ResponseHelper::sendSuccessResponse(['rl' => $response['role']], 'Logged In success', 201);
    }

    public function getUserByUserUID() {}

    public function logout()
    {
        $this->cookieManager->resetCookieHeader();
        ResponseHelper::sendSuccessResponse([], 'Logout Succesfully', 200);
        return;
    }

    public function validateToken()
    {

        $this->cookieManager->validateCookiePressence();

        $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

        if (!$this->jwt->validateToken($token)) {
            $this->cookieManager->resetCookieHeader();
            ResponseHelper::sendUnauthorizedResponse('Invalid token');
            return;
        }

        $decodedData = $this->jwt->decodeJWTData($token);

        ResponseHelper::sendSuccessResponse(['rl' => $decodedData->rl], 'Token is valid', 200);
    }
}
