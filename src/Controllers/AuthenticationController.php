<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;
use RuntimeException;

use App\Helpers\JWTHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\HeaderHelper;
use App\Helpers\CookieManager;
use App\Helpers\EmailHelper;

use App\Validators\AuthenticationValidators\LoginFieldsValidator;
use App\Validators\AuthenticationValidators\RegisterFieldsValidator;

use App\Models\UsersModel;

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

    public function register(array $payload)
    {
        try {
            $sanitizedPayload = RegisterFieldsValidator::validateAndSanitizeFields($payload);

            if (self::checkForEmailExistence($sanitizedPayload['email'])) return;

            $uuid = Uuid::uuid7()->toString();
            $sanitizedPayload['id'] = $uuid;

            $password = $sanitizedPayload['password'];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
            $sanitizedPayload['password'] = $hashed_password;

            $response = $this->usersModel->addNewUser($sanitizedPayload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            $emailHelper = new EmailHelper();
            $emailHelper->sendEmailForAccountVerification($sanitizedPayload['email'], 'PetFaves | Account Activation', $response['data']['activationCode']);

            return ResponseHelper::sendSuccessResponse(
                ['activationToken' => $response['data']['activationToken']],
                $response['message']
            );
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function login(array $payload)
    {
        try {
            $sanitizedPayload = LoginFieldsValidator::validateAndSanitizeFields($payload);

            $email = $sanitizedPayload['email'];
            $password = $sanitizedPayload['password'];

            $response = $this->usersModel->getUserByEmail($email);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse('Email: Invalid Email or Password');
            }

            $stored_password = $response['data']['password'];

            if (!password_verify($password, $stored_password)) {
                return ResponseHelper::sendErrorResponse('Password: Incorrect Password');
            }

            // Check first if the user account is verified
            if (!$response['data']['isVerified']) {
                return ResponseHelper::sendSuccessResponse([], 'Account not verified');
            }

            self::setCookie($response['data']);

            return ResponseHelper::sendSuccessResponse(['role' => $response['data']['role']], 'Logged In success', 201);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function verifyAccount(array $payload)
    {
        try {
            $activationToken = $payload['activationToken'];
            $activationCode = $payload['activationCode'];

            $response = $this->usersModel->verifyAccount($activationToken, $activationCode);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            return ResponseHelper::sendSuccessResponse([], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function resendVerificationEmail(array $payload)
    {
        try {
            $email = $payload['email'];

            $response = $this->usersModel->resendVerificationEmail($email);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            $emailHelper = new EmailHelper();
            $emailHelper->sendEmailForAccountVerification($email, 'PetFaves | Account Activation', $response['data']['activationCode']);

            return ResponseHelper::sendSuccessResponse($response['data'], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function forgotPassword(array $payload)
    {
        try {
            $email = $payload['email'];

            $response = $this->usersModel->forgotPassword($email);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            $emailHelper = new EmailHelper();
            $emailHelper->sendEmailForPasswordReset($email, 'PetFaves | Password Reset', $response['data']['resetToken']);

            return ResponseHelper::sendSuccessResponse([], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function resetPassword(array $payload)
    {
        try {
            $newPassword = $payload['newPassword'];
            $resetToken = $payload['resetToken'];

            $response = $this->usersModel->resetPassword($resetToken, $newPassword);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            return ResponseHelper::sendSuccessResponse([], $response['message']);
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

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }
            return ResponseHelper::sendSuccessResponse([], $response['message']);
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

        $this->cookieManager->validateCookiePresence();

        $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

        if (!$this->jwt->validateToken($token)) {
            $this->cookieManager->resetCookieHeader();
            return ResponseHelper::sendUnauthorizedResponse('Invalid token');
        }

        $decodedData = $this->jwt->decodeJWTData($token);

        return ResponseHelper::sendSuccessResponse(['role' => $decodedData->role], 'Token is valid', 200);
    }


    private function checkForEmailExistence(string $email): bool
    {
        $response = $this->usersModel->getUserByEmail($email);

        if ($response['status'] === 'success') {
            ResponseHelper::sendErrorResponse('Email: Email already exists', 400);
            return true;
        }

        return false;
    }

    private function setCookie($response)
    {
        // 5hrs expiry time for token
        $expirationDate = time() + (5 * 3600);

        $toBeTokenized = [
            "id" => $response['id'],
            'email' => $response['email'],
            'password' => $response['password'],
            'role' => $response['role'],
            'expirationDate' => $expirationDate
        ];

        $token = $this->jwt->encodeDataToJWT($toBeTokenized);

        $this->cookieManager->setCookiHeader($token, $expirationDate);
    }
}
