<?php

namespace Models;

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

    public function getAllNotificationsByUserIDAndStatus(int $userID, string $status)
    {
        try {
            $query = "SELECT * FROM " . self::NOTIFICATIONS_TABLE . "
            WHERE userID = :userID AND notificationStatus = :status 
            ORDER BY id DESC";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
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
            $userID = (int) $payload['userID'];
            $requestID = (int) $payload['requestID'];
            $typeOfRequest = $payload['typeOfRequest'];
            $status = $payload['status'];

            $existingNotifID = $this->checkIfNotificationExist($userID, $requestID, $typeOfRequest);

            if ($existingNotifID) {
                return $this->updateNotificationStatus($existingNotifID, $userID, $status);
            }

            // If there is no existing notification, insert a new one
            $query = "INSERT INTO " . self::NOTIFICATIONS_TABLE . " (userID, requestID, typeOfRequest, notificationStatus) VALUES (:userID, :requestID, :typeOfRequest, :status)";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
            $statement->bindValue(':requestID', $requestID, PDO::PARAM_INT);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            print_r($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateNotificationStatus(int $id, int $userID, string $status)
    {
        $query = "UPDATE " . self::NOTIFICATIONS_TABLE . " SET notificationStatus = :status WHERE id = :id AND userID = :userID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function checkIfNotificationExist(int $userID, int $requestID, string $typeOfRequest)
    {
        try {

            $query = "SELECT * FROM " . self::NOTIFICATIONS_TABLE . " WHERE userID = :userID AND requestID = :requestID AND typeOfRequest = :typeOfRequest";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
            $statement->bindValue(':requestID', $requestID, PDO::PARAM_INT);
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
