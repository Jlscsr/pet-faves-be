<?php

use Helpers\ResponseHelper;

use Validators\HTTPRequestValidator;

use Models\RequestsModel;

class RequestsController
{
    private $pdo;
    private $requestsModel;
    private $acceptableParamsKeys = ['id', 'status', 'typeOfRequest', 'userID'];
    private $expectedPostPayloadKeys = ['userID', 'petID', 'typeOfRequest', 'status'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->requestsModel = new RequestsModel($this->pdo);
    }

    public function getRequestByID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = (int) $params['id'];

            $adoptionRequest = $this->requestsModel->getRequestByID($id);

            if (!$adoptionRequest) {
                return ResponseHelper::sendSuccessResponse([], 'No request found');
            }

            return ResponseHelper::sendSuccessResponse($adoptionRequest, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getRequestByTypeofRequest(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $typeOfRequest = $params['typeOfRequest'];

            $requestsLists = $this->requestsModel->getRequestByTypeofRequest($typeOfRequest);

            if (!$requestsLists) {
                return ResponseHelper::sendSuccessResponse([], 'No Requests found');
            }

            return ResponseHelper::sendSuccessResponse($requestsLists, 'Requests found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllRequestsByStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $status = $params['status'];

            $requestsLists = $this->requestsModel->getAllRequestsByStatus($status);

            if (!$requestsLists) {
                return ResponseHelper::sendSuccessResponse([], 'No Requests found');
            }

            return ResponseHelper::sendSuccessResponse($requestsLists, 'Requests found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = (int) $params['userID'];

            $adoptionRequest = $this->requestsModel->getUserRequestByUserID($userID);

            if (!$adoptionRequest) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($adoptionRequest, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = (int) $params['id'];
            $userID = (int) $params['userID'];

            $adoptionRequest = $this->requestsModel->getUserRequestByUserIDAndID($userID, $id);

            if (!$adoptionRequest) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($adoptionRequest, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewUserRequest(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $response = $this->requestsModel->addNewUserRequest($payload);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to add pet", 400);
            }

            return ResponseHelper::sendSuccessResponse($response, "New Request added successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateRequestStatus(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['status'], $params, $payload);

            $id = (int) $params['id'];
            $status = $payload['status'];

            $request = $this->requestsModel->updateRequestStatus($id, $status);

            if (!$request) {
                return ResponseHelper::sendErrorResponse("Failed to update request", 400);
            }

            return ResponseHelper::sendSuccessResponse($request, "Request updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
