<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\DonationsModel;

use RuntimeException;

class DonationsController
{
    private $donationsModel;

    public function __construct($pdo)
    {
        $this->donationsModel = new DonationsModel($pdo);
    }

    public function addNewDonation($payload)
    {
        try {
            $payload['id'] = Uuid::uuid7()->toString();

            $response = $this->donationsModel->addNewDonation($payload);

            if ($response['failed']) {
                return ResponseHelper::sendErrorResponse($response['message'], 400);
            }

            return ResponseHelper::sendSuccessResponse([], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
