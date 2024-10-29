<?php

namespace Models;

use Models\PetsModel;

use PDOException;

use PDO;

use RuntimeException;

class RequestsModel
{
    private $pdo;
    private const REQUESTS_TABLE = 'requests_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petsModel = new PetsModel($this->pdo);
    }

    public function getRequestByID(int $id)
    {
        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getRequestByTypeofRequest(string $typeOfRequest)
    {
        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE typeOfRequest = :typeOfRequest AND status != 'completed' AND status != 'cancelled'";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllRequestsByStatus(string $status)
    {
        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE status = :status";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserID(int $userID)
    {
        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE userID = :userID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndID(int $userID, int $id)
    {
        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE userID = :userID AND id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }



    public function addNewUserRequest(array $payload)
    {
        try {
            $userID = (int) $payload['userID'];
            $petID = (int) $payload['petID'] ?? null;
            $typeOfRequest = $payload['typeOfRequest'];
            $status = $payload['status'];

            $query = "INSERT INTO " . self::REQUESTS_TABLE . " (userID, petID, typeOfRequest, status) VALUES (:userID, :petID, :typeOfRequest, :status)";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
            $statement->bindValue(':petID', $petID, PDO::PARAM_INT);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            $lastInsertedID = $this->pdo->lastInsertId();

            if ($typeOfRequest === 'adoption') {
                $updatePetAdoptionStatus = $this->petsModel->updatePetAdoptionStatus($petID, 1);

                if (!$updatePetAdoptionStatus) {
                    throw new RuntimeException("Failed to update pet adoption status");
                }
            }

            $selectQuery = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE id = :lastInsertedID";
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

    public function updateRequestStatus(int $id, string $status)
    {
        try {
            $query = "UPDATE " . self::REQUESTS_TABLE . " SET status = :status WHERE id = :id";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':status', $status, PDO::PARAM_STR);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);

            $statement->execute();

            $lastUpdatedID = $this->getRequestByID($id);

            return $lastUpdatedID;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
