<?php

namespace App\Middlewares;

use RuntimeException;

use App\Helpers\CookieManager;
use App\Helpers\JWTHelper;

class RequestMiddleware
{
    private $jwt;
    private $cookieManager;
    private $requiredRole;
    private $response;

    public function __construct($requiredRole)
    {
        $this->jwt = new JWTHelper();
        $this->cookieManager = new CookieManager();
        $this->requiredRole = $requiredRole;

        $this->validateRequest();
    }

    public function validateRequest(): void
    {
        try {
            $this->checkCookiePressence();
        } catch (RuntimeException $e) {
            $this->cookieManager->resetCookieHeader();
            throw new RuntimeException($e->getMessage());
        }
    }

    public function checkCookiePressence()
    {
        try {
            $response = $this->cookieManager->validateCookiePresence();

            if ($response['status'] === 'failed') {
                $this->response = ['status' => 'failed', 'message' => 'Unauthorized Access. Please login to continue.'];
                return;
            }

            $this->validateToken();
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function validateToken(): void
    {
        $token = $this->cookieManager->extractAccessTokenFromCookieHeader();

        $response = $this->jwt->validateToken($token);

        if (is_array($response) && $response['status'] === 'failed') {
            $this->cookieManager->resetCookieHeader();
            $this->response = ['status' => 'failed', 'message' => $response['message']];
            return;
        }

        $decodedToken = $this->jwt->decodeJWTData($token);

        $this->verifyUserRole($decodedToken);
    }

    public function verifyUserRole(object $decodedToken)
    {
        $userCurrentRole = $decodedToken->role;

        if ($this->requiredRole === 'none' || $this->requiredRole === 'both') {
            $this->response = ['status' => 'success'];
            return;
        }

        if ($userCurrentRole !== $this->requiredRole) {
            $this->response = [
                'status' => 'failed',
                'message' => "Unauthorized Access. You're not allowed to access this resource."
            ];
            return;
        }

        $this->response = ['status' => 'success'];
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
