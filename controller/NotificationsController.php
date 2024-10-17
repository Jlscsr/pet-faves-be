<?php

use Helpers\ResponseHelper;
use Models\NotificationsModel;

class NotificationsController
{
    private $pdo;
    private $requestsModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->notificationsModel = new NotificationsModel($this->pdo);
    }

    public function getAllNotificationsByUserIDAndStatus($params)
    {
        if (empty($params)) {
            ResponseHelper::sendErrorResponse("Invalid or missing status parameter", 400);
            return;
        }

        $status = $params['status'];
        $userID = $params['userID'];

        try {
            $notificationsLists = $this->notificationsModel->getAllNotificationsByUserIDAndStatus($userID, $status);
            if (!$notificationsLists) {
                ResponseHelper::sendSuccessResponse([], 'No notifications found');
                return;
            }

            ResponseHelper::sendSuccessResponse($notificationsLists, 'Notifications found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function addNewNotification($payload)
    {
        if (empty($payload)) {
            ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
            return;
        }

        try {
            $isAddingNotifSuccess = $this->notificationsModel->addNewNotification($payload);

            if (!$isAddingNotifSuccess) {
                ResponseHelper::sendErrorResponse("Failed to add new notification", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse([], "New notification added successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updateNotificationStatus($params, $payload)
    {
        if (empty($payload)) {
            ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
            return;
        }

        $id = $params['id'];
        $status = $payload['status'];
        $userID = $params['userID'];

        try {
            $isUpdatingNotifSuccess = $this->notificationsModel->updateNotificationStatus($id, $userID, $status);

            if (!$isUpdatingNotifSuccess) {
                ResponseHelper::sendErrorResponse("Failed to update notification status", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse([], "Notification status updated successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
