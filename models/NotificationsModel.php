<?php

namespace Models;

use PDOException;

use PDO;

use RuntimeException;
use InvalidArgumentException;

class NotificationsModel
{
    private $pdo;
    private const NOTIFICATIONS_TABLE = 'notifications_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllNotificationsByUserIDAndStatus($userID, $status)
    {
        if (!$status) {
            throw new InvalidArgumentException("Invalid or missing status parameter");
            return;
        }

        $query = "SELECT n.*, r.* FROM " . self::NOTIFICATIONS_TABLE . " n 
              JOIN requests_tb r ON n.requestID = r.id 
              WHERE n.userID = :userID AND n.notificationStatus = :status 
              ORDER BY n.id DESC";

        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewNotification($payload)
    {
        if (empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
            return;
        }

        $userID = $payload['userID'];
        $requestID = $payload['requestID'];
        $typeOfRequest = $payload['typeOfRequest'];
        $status = 'unread';

        // check first if the id is already exist
        $query = "SELECT * FROM " . self::NOTIFICATIONS_TABLE . " WHERE userID = :userID AND requestID = :requestID AND typeOfRequest = :typeOfRequest";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
        $statement->bindValue(':requestID', $requestID, PDO::PARAM_STR);
        $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

        try {
            $statement->execute();

            if ($statement->rowCount() > 0) {
                // update the status of existing one to unread
                return $this->updateNotificationStatus($statement->fetch(PDO::FETCH_ASSOC)['id'], $userID, $status);
            }

            $query = "INSERT INTO " . self::NOTIFICATIONS_TABLE . " (userID, requestID, typeOfRequest, notificationStatus) VALUES (:userID, :requestID, :typeOfRequest, :status)";

            $statement = $this->pdo->prepare($query);

            $bindParams = [
                ':userID' => $userID,
                ':requestID' => $requestID,
                ':typeOfRequest' => $typeOfRequest,
                ':status' => $status
            ];

            foreach ($bindParams as $key => $value) {
                $statement->bindValue($key, $value, PDO::PARAM_STR);
            }

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            print_r($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateNotificationStatus($id, $userID, $status)
    {
        if (!$userID || !$status || !$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "UPDATE " . self::NOTIFICATIONS_TABLE . " SET notificationStatus = :status WHERE userID = :userID AND requestID = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);
        $statement->bindValue(':id', $id, PDO::PARAM_STR);
        $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

        try {
            $statement->execute();
            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
