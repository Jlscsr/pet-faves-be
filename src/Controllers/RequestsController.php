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
    private $acceptableParamsKeys = ['id', 'status', 'userID', 'typeOfRequest', 'userOwnerID', 'reason'];
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

    public function getAllRequestsByStatusAndTypeOfRequest(array $params)
    {
        try {
            $status = $params['status'];
            $typeOfRequest = $params['typeOfRequest'];

            $response = $this->requestsModel->getAllRequestsByStatusAndTypeOfRequest($status, $typeOfRequest);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendSuccessResponse([], $response['message']);
            }

            return ResponseHelper::sendSuccessResponse($response['data'], $response['message']);
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

            $response = $this->requestsModel->getAllRequestsByUserOwnerIDAndStatus($userID, $status);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendSuccessResponse([], 'No Requests found');
            }

            return ResponseHelper::sendSuccessResponse($response['data'], 'Requests found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndTypeOfRequest(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['userID'];
            $typeOfRequest = $params['typeOfRequest'];

            $requestsLists = $this->requestsModel->getUserRequestByUserIDAndTypeOfRequest($userID, $typeOfRequest);

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

            $response = $this->requestsModel->getUserRequestByUserIDAndID($userID, $id);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendSuccessResponse([], 'No Request found');
            }

            return ResponseHelper::sendSuccessResponse($response['data'], 'Request found');
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
            // TODO: Change this validator to the new one
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->requestsModel->addNewUserRequest($payload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse("Failed to add new request");
            }

            return ResponseHelper::sendSuccessResponse($response['data'], "New Request added successfully");
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
            $reason = $payload['reason'] ?? 'n/a';

            $request = $this->requestsModel->updateRequestStatus($id, $status, $reason);

            if (!$request) {
                return ResponseHelper::sendErrorResponse("Failed to update request", 400);
            }

            return ResponseHelper::sendSuccessResponse($request, "Request updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function checkIfUserAlreadyRequestedPet($params)
    {
        try {

            $userID = $params['userID'];
            $petID = $params['petID'];

            $response = $this->requestsModel->checkIfUserAlreadyRequestedPet($userID, $petID);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message']);
            }

            return ResponseHelper::sendSuccessResponse([], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function cancelMultitpleRequests(array $payload)
    {
        try {

            $response = $this->requestsModel->cancelMultitpleRequests($payload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse("Failed to cancel requests");
            }

            return ResponseHelper::sendSuccessResponse([], "Requests cancelled successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateRequestTypeOfRequest(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['typeOfRequest'], $params, $payload);

            $id = $params['id'];
            $typeOfRequest = $payload['typeOfRequest'];

            $request = $this->requestsModel->updateRequestTypeOfRequest($id, $typeOfRequest);

            if (!$request) {
                return ResponseHelper::sendErrorResponse("Failed to update request", 400);
            }

            return ResponseHelper::sendSuccessResponse($request, "Request updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
