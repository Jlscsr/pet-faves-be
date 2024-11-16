<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\RequestsModel;

use RuntimeException;;

class RequestsController
{
    private $pdo;
    private $requestsModel;
    private $acceptableParamsKeys = ['id', 'status', 'userID', 'typeOfPost', 'userOwnerID', 'reason'];
    private $expectedPostPayloadKeys = ['userID', 'petID', 'status', 'userOwnerID', 'typeOfRequest'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->requestsModel = new RequestsModel($this->pdo);
    }

    public function getRequestByID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];

            $request = $this->requestsModel->getRequestByID($id);

            if (!$request) {
                return ResponseHelper::sendSuccessResponse([], 'No request found');
            }

            return ResponseHelper::sendSuccessResponse($request, 'Request found');
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

    public function getAllRequestsByUserOwnerIDAndStatus($params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['userOwnerID'];
            $status = $params['status'];

            $requestsLists = $this->requestsModel->getAllRequestsByUserOwnerIDAndStatus($userID, $status);

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

            $userID = $params['userID'];

            $requestsLists = $this->requestsModel->getUserRequestByUserID($userID);

            if (!$requestsLists) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($requestsLists, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];
            $userID = $params['userID'];

            $request = $this->requestsModel->getUserRequestByUserIDAndID($userID, $id);

            if (!$request) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($request, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['userID'];
            $status = $params['status'];

            $request = $this->requestsModel->getUserRequestByUserIDAndStatus($userID, $status);

            if (!$request) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($request, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserOwnerIDAndID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];
            $userOwnerID = $params['userOwnerID'];

            $request = $this->requestsModel->getUserRequestByUserOwnerIDAndID($userOwnerID, $id);

            if (!$request) {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($request, 'Request found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllReturnRequests()
    {
        try {
            $requestsLists = $this->requestsModel->getAllReturnRequests();

            if (empty($requestsLists)) {
                return ResponseHelper::sendSuccessResponse([], 'No Requests found');
            }

            return ResponseHelper::sendSuccessResponse($requestsLists, 'Requests found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllReturnRequestsByStatus(array $params)
    {
        try {
            $status = $params['status'];
            $requests = $this->requestsModel->getAllReturnRequestsByStatus($status);

            if (empty($requests)) {
                return ResponseHelper::sendSuccessResponse([], 'No Return requests found');
            }

            return ResponseHelper::sendSuccessResponse($requests, 'Return requests found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewUserRequest(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

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

            $id = $params['id'];
            $status = $payload['status'];
            $reason = $payload['reason'] || 'n/a';

            print_r($reason);

            $request = $this->requestsModel->updateRequestStatus($id, $status, $reason);

            if (!$request) {
                return ResponseHelper::sendErrorResponse("Failed to update request", 400);
            }

            return ResponseHelper::sendSuccessResponse($request, "Request updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
