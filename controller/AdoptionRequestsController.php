<?php

use Helpers\ResponseHelper;
use Models\AdoptionRequestsModel;

class AdoptionRequestsController
{
    private $pdo;
    private $adoptionRequestModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->adoptionRequestModel = new AdoptionRequestsModel($this->pdo);
    }

    public function getAllUserRequestsByStatus($param)
    {
        if (empty($param)) {
            ResponseHelper::sendErrorResponse("Invalid or missing status parameter", 400);
            return;
        }

        $status = null;

        // chekc if the status in param has "-", if true, break it and store it in 2 vartiables
        if (strpos($param['status'], '-') !== false) {
            $status = explode('-', $param['status']);
        } else {
            $status = $param['status'];
        }

        try {
            $adoptionRequests = $this->adoptionRequestModel->getAllUserRequestsByStatus($status);

            if (!$adoptionRequests) {
                ResponseHelper::sendSuccessResponse([], 'No adoption requests found');
                return;
            }

            ResponseHelper::sendSuccessResponse($adoptionRequests, 'Adoption requests found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserID($param)
    {
        try {
            if (empty($param) || !isset($param['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing id parameter", 400);
                return;
            }

            $id = (int) $param['id'];


            $adoptionRequest = $this->adoptionRequestModel->getUserRequestByUserID($id);

            if (!$adoptionRequest) {
                ResponseHelper::sendSuccessResponse([], 'No adoption request found');
                return;
            }

            ResponseHelper::sendSuccessResponse($adoptionRequest, 'Adoption request found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewUserRequest($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }

            $request = $this->adoptionRequestModel->addNewUserRequest($payload);

            if (!$request) {
                ResponseHelper::sendErrorResponse("Failed to add pet", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse($request, "New Adoption Request added successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateUserRequestStatus($param, $payload)
    {

        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }

            $id = (int) $param['id'];

            $request = $this->adoptionRequestModel->updateUserRequestStatus($id, $payload);

            if (!$request) {
                ResponseHelper::sendErrorResponse("Failed to update adoption request", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse($request, "Adoption request updated successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
