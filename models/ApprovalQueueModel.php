<?php

namespace Models;

use Models\AdoptionRequestsModel;

use PDO;

use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ApprovalQueueModel
{
    private $pdo;
    private const APPROVAL_QUEUE_TABLE = 'approval_queue_tb';


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->adoptionRequestModel = new AdoptionRequestsModel($this->pdo);
    }

    public function getAllApprovalQueues()
    {
        return $this->pdo->query("SELECT * FROM " . self::APPROVAL_QUEUE_TABLE)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovalQueueByID($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
        }

        $query = "SELECT * FROM " . self::APPROVAL_QUEUE_TABLE . " WHERE id = ? LIMIT 1";

        try {
            return $this->pdo->query($query, [$id])->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewApprovalQueue($payload)
    {
        if (!is_array($payload) || empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $requiredFields = ['userID', 'petPhotoURL', 'petName', 'petAge', 'petGender', 'petType', 'petBreed', 'petVacHistory', 'petHistory', 'petLabel'];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        $query = "INSERT INTO " . self::APPROVAL_QUEUE_TABLE . " (userID, petName, age, gender, petType, petBreed, petVacHistory, petHistory, petPhotoURL, label) 
                VALUES (:userID, :petName, :petAge, :petGender, :petType, :petBreed, :petVacHistory, :petHistory, :petPhotoURL, :label)";
        $statement = $this->pdo->prepare($query);

        try {
            $this->pdo->beginTransaction();

            $statement->execute($payload);
            $lastInsertedID = $this->pdo->lastInsertId();

            $newUserRequestData = [
                'userID' => $payload['userID'],
                'approvalQueueID' => $lastInsertedID,
                'label' => $payload['petLabel'],
                'status' => 'pending',
            ];

            $response = $this->adoptionRequestModel->addNewUserRequest($newUserRequestData);

            if (!$response) {
                throw new RuntimeException("Failed to add new user request");
            }

            $this->pdo->commit();

            return $response;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException($e->getMessage());
        }
    }


    public function deleteApprovalQueueByID($id)
    {
        if (!$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "DELETE FROM " . self::APPROVAL_QUEUE_TABLE . " WHERE id = :id";

        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->rowCount();
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
