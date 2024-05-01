<?php

namespace Models;

use Models\PetsModel;
use PDOException;

use PDO;

use RuntimeException;
use InvalidArgumentException;

class AdoptionRequestsModel
{
    private $pdo;
    private $petsModel;
    private const ADOPTION_REQUESTS_TABLE = 'requests_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petsModel = new PetsModel($this->pdo);
    }

    public function getAllUserRequestsByStatus($status)
    {
        if (!$status) {
            throw new InvalidArgumentException("Invalid or missing status parameter");
            return;
        }

        $query = null;

        if (is_array($status)) {
            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE status = :status1 OR status = :status2 ORDER BY id DESC";
        } else {
            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE status = :status ORDER BY id DESC";
        }

        $statement = $this->pdo->prepare($query);

        if (is_array($status)) {
            $statement->bindValue(':status1', $status[0], PDO::PARAM_STR);
            $statement->bindValue(':status2', $status[1], PDO::PARAM_STR);
        } else {
            $statement->bindValue(':status', $status, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByID($id)
    {
        if (!$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE id = :id";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserID($id)
    {
        if (!$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :id";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }


    public function addNewUserRequest($payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $userID = $payload['userID'];
        $petID = $payload['petID'] ?? null;
        $approvalQueueID = $payload['approvalQueueID'] ?? null;
        $label = $payload['label'];
        $status = $payload['status'];
        $validIDPhotoURL = $payload['validID'] ?? null;
        $selfiePhotoURL = $payload['selfiePhoto'] ?? null;

        $query = "INSERT INTO " . self::ADOPTION_REQUESTS_TABLE . " (userID, petID, approvalQueueID, label, status, validIDPhotoURL, selfiePhotoURL) VALUES (:userID, :petID, :approvalQueueID, :label, :status, :validID, :selfiePhoto)";

        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':userID' => $userID,
            ':petID' => $petID,
            ':label' => $label,
            ':status' => $status,
            ':validID' => $validIDPhotoURL,
            ':selfiePhoto' => $selfiePhotoURL,
            ':approvalQueueID' => $approvalQueueID
        ];

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            $lastInsertedID = $this->pdo->lastInsertId();

            // Construct and execute a SELECT query to fetch the last inserted data
            $selectQuery = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE id = :lastInsertedID";
            $selectStatement = $this->pdo->prepare($selectQuery);
            $selectStatement->bindValue(':lastInsertedID', $lastInsertedID, PDO::PARAM_INT);

            try {
                $selectStatement->execute();

                // Fetch the last inserted data
                $lastInsertedData = $selectStatement->fetch(PDO::FETCH_ASSOC);

                return $lastInsertedData;
            } catch (PDOException $e) {
                throw new RuntimeException($e->getMessage());
            }
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateUserRequestStatus($id, $payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $status = $payload['status'];

        $query = "UPDATE " . self::ADOPTION_REQUESTS_TABLE . " SET status = :status WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            if ($statement->rowCount() > 0) {
                $response = $this->getUserRequestByID($id);

                if (!$response) {
                    throw new RuntimeException("No user request found");
                }

                if ($response['approvalQueueID'] !== '' && $response['status'] === 'approved') {
                    $approvalQueueID = $response['approvalQueueID'];
                    $query = "SELECT * FROM approval_queue_tb WHERE id = ? LIMIT 1";
                    $statement = $this->pdo->prepare($query);
                    $statement->execute([$approvalQueueID]);
                    $pendingPetData = $statement->fetch();

                    if (!$pendingPetData) {
                        throw new RuntimeException("No approval queue found");
                    };

                    $newlyAddedPetData = [
                        "userID" => $response['userID'],
                        "petName" => $pendingPetData['petName'],
                        "petAge" => $pendingPetData['age'],
                        "petGender" => $pendingPetData['gender'],
                        "petType" => $pendingPetData['petType'],
                        "petBreed" => $pendingPetData['petBreed'],
                        "petVacHistory" => $pendingPetData['petVacHistory'],
                        "petHistory" => $pendingPetData['petHistory'],
                        "petPhotoURL" => $pendingPetData['petPhotoURL'],
                        "label" => $pendingPetData['label'],
                    ];

                    $response = $this->petsModel->addNewPet($newlyAddedPetData);

                    if (!$response) {
                        throw new RuntimeException("No pet added");
                    }

                    return $response;
                }
            }
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
