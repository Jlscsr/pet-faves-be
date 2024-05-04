<?php

use Models\PetFeedsModel;

use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

use Validators\Controllers\PetFeedsValidator;

class PetFeedsController
{
    private $pdo;
    private $petFeedsModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petFeedsModel = new PetFeedsModel($this->pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getAllPetsFromPetFeeds()
    {
        try {
            $response = $this->petFeedsModel->getAllPetsFromPetFeeds();

            if (!$response) {
                ResponseHelper::sendErrorResponse("Error fetching pets from pet feeds table");
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched pets from pet feeds table');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsFromPetFeedsByStatus($param)
    {
        try {
            PetFeedsValidator::validateGETParameter($param);

            $status = $param['status'];

            $response = $this->petFeedsModel->getAllPetsFromPetFeedsByStatus($status);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Error fetching pets from pet feeds table");
                exit;
            }

            ResponseHelper::sendSuccessResponse($response, 'Successfully fetched pets from pet feeds table');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewPetToPetFeeds($payload)
    {
        try {
            PetFeedsValidator::validatePOSTPayload($payload);
            // Add sanity checks here

            $response = $this->petFeedsModel->addNewPetToPetFeeds($payload);

            if (!$response) {
                ResponseHelper::sendErrorResponse("Error adding pet to pet feeds table");
            }
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
