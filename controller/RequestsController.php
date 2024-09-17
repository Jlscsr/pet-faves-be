<?php

use Helpers\ResponseHelper;
use Models\RequestsModel;

class RequestsController
{
    private $pdo;
    private $requestsModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->requestsModel = new RequestsModel($this->pdo);
    }

    public function getRequestByTypeofRequest($param)
    {
        if (empty($param)) {
            ResponseHelper::sendErrorResponse("Invalid or missing status parameter", 400);
            return;
        }

        $typeOfRequest = $param['type'];

        try {
            $requestsLists = $this->requestsModel->getRequestByTypeofRequest($typeOfRequest);

            if (!$requestsLists) {
                ResponseHelper::sendSuccessResponse([], 'No adoption requests found');
                return;
            }

            ResponseHelper::sendSuccessResponse($requestsLists, 'Requests found');
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


            $adoptionRequest = $this->requestsModel->getUserRequestByUserID($id);

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

            $request = $this->requestsModel->addNewUserRequest($payload);

            if (!$request) {
                ResponseHelper::sendErrorResponse("Failed to add pet", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse($request, "New Adoption Request added successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateRequestStatus($param, $payload)
    {

        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }

            $id = (int) $param['id'];

            $request = $this->requestsModel->updateRequestStatus($id, $payload);

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
