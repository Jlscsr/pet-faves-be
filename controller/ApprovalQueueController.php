<?php

use Helpers\ResponseHelper;
use Models\ApprovalQueueModel;

class ApprovalQueueController
{
    private $pdo;
    private $approvalQueueModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->approvalQueueModel = new ApprovalQueueModel($this->pdo);
    }

    public function getApprovalQueueByID($param)
    {
        try {
            if (empty($param) || !isset($param['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing id parameter", 400);
                return;
            }

            $id = (int) $param['id'];

            $request = $this->approvalQueueModel->getApprovalQueueByID($id);

            if (!$request) {
                ResponseHelper::sendSuccessResponse([], 'No request found');
                return;
            }

            ResponseHelper::sendSuccessResponse($request, 'Request found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewApprovalQueue($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }

            $request = $this->approvalQueueModel->addNewApprovalQueue($payload);

            if (!$request) {
                ResponseHelper::sendErrorResponse("Failed to add new approval Queue", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse($request, "New Approval Queue added successfully");
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
