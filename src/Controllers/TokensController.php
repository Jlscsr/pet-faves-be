<?php

namespace App\Controllers;

use RuntimeException;

use App\Helpers\ResponseHelper;

use App\Models\TokensModel;

class TokensController
{
    private $tokensModel;

    public function __construct($pdo)
    {
        $this->tokensModel = new TokensModel($pdo);
    }

    public function validateResetToken(array $params)
    {
        try {
            $resetToken = $params['token'];

            $response = $this->tokensModel->validateResetToken($resetToken);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            return ResponseHelper::sendSuccessResponse([], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
