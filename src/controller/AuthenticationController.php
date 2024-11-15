<?php

use Ramsey\Uuid\Uuid;

use App\Helpers\JWTHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\HeaderHelper;
use App\Helpers\CookieManager;

use App\Models\UsersModel;

use App\Validators\HTTPRequestValidator;

use App\Config\EnvironmentLoader;


class AuthenticationController
{
    private $jwt;
    private $usersModel;
    private $cookieManager;
    private $expectedRegisterPayload = ['firstName', 'lastName', 'email', 'password'];
    private $expectedloginPayloadKeys = ['email', 'password'];

    public function __construct($pdo)
    {
        $this->jwt = new JWTHelper();
        $this->usersModel = new UsersModel($pdo);
        $this->cookieManager = new CookieManager();

        EnvironmentLoader::load();
        HeaderHelper::setResponseHeaders();
    }

    public function register(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedRegisterPayload, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $password = $payload['password'];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
            $payload['password'] = $hashed_password;

            $isRegisterSuccess = $this->usersModel->addNewUser($payload);

            if (!$isRegisterSuccess) {
                return ResponseHelper::sendErrorResponse('Failed to register user', 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'User registered successfully', 201);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function login(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedloginPayloadKeys, $payload);

            $email = $payload['email'];
            $password = $payload['password'];

            $response = $this->usersModel->getUserByEmail($email);


            if (!isset($response['password'])) {
                return ResponseHelper::sendErrorResponse('No email found on our database');
            }

            $stored_password = $response['password'];

            if (!password_verify($password, $stored_password)) {
                return ResponseHelper::sendErrorResponse('Incorrect Password');
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

            return ResponseHelper::sendSuccessResponse(['rl' => $response['role']], 'Logged In success', 201);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function changePassword(array $payload)
    {
        try {
            $email = $payload['email'];
            $newPassword = $payload['newPassword'];
            $oldPassword = $payload['oldPassword'];

            $response = $this->usersModel->changePassword($email, $oldPassword, $newPassword);
            if (!$response) {
                return ResponseHelper::sendErrorResponse('Failed to change password', 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Password changed successfully', 201);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function logout()
    {
        $this->cookieManager->resetCookieHeader();
        return ResponseHelper::sendSuccessResponse([], 'Logout Succesfully', 200);
    }

    public function validateToken()
    {

        $this->cookieManager->validateCookiePressence();

        $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

        if (!$this->jwt->validateToken($token)) {
            $this->cookieManager->resetCookieHeader();
            return ResponseHelper::sendUnauthorizedResponse('Invalid token');
        }

        $decodedData = $this->jwt->decodeJWTData($token);

        return ResponseHelper::sendSuccessResponse(['rl' => $decodedData->rl], 'Token is valid', 200);
    }
}
