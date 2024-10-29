<?php

use Helpers\ResponseHelper;

use Validators\HTTPRequestValidator;

use Models\NotificationsModel;

class NotificationsController
{
    private $pdo;
    private $acceptableParamsKeys = ['id', 'status', 'userID'];
    private $expectedPostPayloadKeys = ['userID', 'requestID', 'typeOfRequest', 'status'];
    private $expectedPutPayloadKeys = ['status'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->notificationsModel = new NotificationsModel($this->pdo);
    }

    public function getAllNotificationsByUserIDAndStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = (int) $params['userID'];
            $status =  $params['status'];

            $notificationsLists = $this->notificationsModel->getAllNotificationsByUserIDAndStatus($userID, $status);

            if (!$notificationsLists) {
                return ResponseHelper::sendSuccessResponse([], 'No notifications found');
            }

            return ResponseHelper::sendSuccessResponse($notificationsLists, 'Notifications found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewNotification(array $payload)
    {

        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $isAddingNotifSuccess = $this->notificationsModel->addNewNotification($payload);

            if (!$isAddingNotifSuccess) {
                return ResponseHelper::sendErrorResponse("Failed to add new notification", 400);
            }

            return ResponseHelper::sendSuccessResponse([], "New notification added successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateNotificationStatus(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, $this->expectedPutPayloadKeys, $params, $payload);

            $id = (int) $params['id'];
            $userID = (int) $params['userID'];
            $status = $payload['status'];

            $isUpdatingNotifSuccess = $this->notificationsModel->updateNotificationStatus($id, $userID, $status);

            if (!$isUpdatingNotifSuccess) {
                return ResponseHelper::sendErrorResponse("Failed to update notification status", 400);
            }

            return ResponseHelper::sendSuccessResponse([], "Notification status updated successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}