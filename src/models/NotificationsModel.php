<?php

namespace App\Models;

use PDOException;

use PDO;

use RuntimeException;

class NotificationsModel
{
    private $pdo;
    private const NOTIFICATIONS_TABLE = 'notifications_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllNotificationsByUserIDAndStatus(string $userID, string $status)
    {
        try {
            $query = "SELECT * FROM " . self::NOTIFICATIONS_TABLE . "
            WHERE userID = :userID AND notificationStatus = :status 
            AND requestStatus != 'pending' ORDER BY id DESC";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewNotification(array $payload)
    {
        try {
            $id = $payload['id'];
            $userID = $payload['userID'];
            $requestID = $payload['requestID'] ?? null;
            $postID = $payload['postID'] ?? null;
            $typeOfRequest = $payload['typeOfRequest'];
            $notificationStatus = $payload['notificationStatus'];
            $requestStatus = $payload['requestStatus'];

            $existingNotifID = $this->checkIfNotificationExist($userID, $requestID, $postID, $typeOfRequest);

            if ($existingNotifID) {
                return $this->updateNotificationStatus($existingNotifID, $userID, $notificationStatus, $requestStatus);
            }

            // If there is no existing notification, insert a new one
            $query = "INSERT INTO " . self::NOTIFICATIONS_TABLE . " (id, userID, requestID, postID, typeOfRequest, notificationStatus, requestStatus) VALUES (:id, :userID, :requestID, :postID, :typeOfRequest, :notificationStatus, :requestStatus)";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':requestID', $requestID, PDO::PARAM_STR);
            $statement->bindValue(':postID', $postID, PDO::PARAM_STR);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);
            $statement->bindValue(':notificationStatus', $notificationStatus, PDO::PARAM_STR);
            $statement->bindValue(':requestStatus', $requestStatus, PDO::PARAM_STR);


            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateNotificationStatus(string $id, string $userID, string $notificationStatus, string $requestStatus)
    {
        $query = "UPDATE " . self::NOTIFICATIONS_TABLE . " SET notificationStatus = :notificationStatus, requestStatus = :requestStatus WHERE id = :id AND userID = :userID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
        $statement->bindValue(':notificationStatus', $notificationStatus, PDO::PARAM_STR);
        $statement->bindValue(':requestStatus', $requestStatus, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function checkIfNotificationExist(string $userID, string | null $requestID, string | null $postID, string $typeOfRequest)
    {
        try {

            $query = null;

            if ($requestID !== 0) {
                $query .= "SELECT * FROM " . self::NOTIFICATIONS_TABLE . " WHERE userID = :userID AND requestID = :requestID AND typeOfRequest = :typeOfRequest";
            } else {
                $query .= "SELECT * FROM " . self::NOTIFICATIONS_TABLE . " WHERE userID = :userID AND postID = :postID AND typeOfRequest = :typeOfRequest";
            }

            $statement = $this->pdo->prepare($query);

            if ($requestID !== 0) {
                $statement->bindValue(':requestID', $requestID, PDO::PARAM_STR);
            } else {
                $statement->bindValue(':postID', $postID, PDO::PARAM_STR);
            }

            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() > 0) {
                return $statement->fetch(PDO::FETCH_ASSOC)['id'];
            }

            return false;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
