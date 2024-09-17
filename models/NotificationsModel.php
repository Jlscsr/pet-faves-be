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

        $query = "SELECT * FROM " . self::NOTIFICATIONS_TABLE . " WHERE userID = :userID AND status = :status ORDER BY id DESC";

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

        $query = "INSERT INTO " . self::NOTIFICATIONS_TABLE . " (userID, requestID, typeOfRequest, status) VALUES (:userID, :requestID, :typeOfRequest, :status)";

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

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateNotificationStatus($id, $userID, $status)
    {
        if (!$userID || !$status || !$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "UPDATE " . self::NOTIFICATIONS_TABLE . " SET status = :status WHERE id = :id AND userID = :userID";

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
