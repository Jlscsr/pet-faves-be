<?php

namespace Models;

use Models\PetsModel;

use PDOException;

use PDO;

use RuntimeException;

class RequestsModel
{
    private $pdo;
    private const ADOPTION_REQUESTS_TABLE = 'adoption_requests_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petsModel = new PetsModel($this->pdo);
    }

    public function getRequestByID(string $id)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllRequestsByStatus(string $status)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE status = :status AND userOwnerID IS NULL";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllRequestsByUserOwnerIDAndStatus(string $userOwnerID, string $status)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userOwnerID = :userOwnerID AND status = :status";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userOwnerID', $userOwnerID, PDO::PARAM_STR);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserOwnerIDAndID(string $userOwnerID, string $id)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userOwnerID = :userOwnerID AND id = :id";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userOwnerID', $userOwnerID, PDO::PARAM_STR);
        $statement->bindValue(':id', $id, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserID(string $userID)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :userID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndStatus(string $userID, string $status)
    {
        try {
            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :userID AND status = :status";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndID(string $userID, string $id)
    {
        try {
            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :userID AND id = :id";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewUserRequest(array $payload)
    {
        try {
            $id = $payload['id'];
            $userID = $payload['userID'];
            $userOwnerID = $payload['userOwnerID'] ?? null;
            $petID = $payload['petID'] ?? null;
            $status = $payload['status'];

            $query = "INSERT INTO " . self::ADOPTION_REQUESTS_TABLE . " (id, userID, userOwnerID, petID, status) VALUES (:id, :userID, :userOwnerID, :petID, :status)";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':userOwnerID', $userOwnerID, PDO::PARAM_STR);
            $statement->bindValue(':petID', $petID, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            $lastInsertedID = $id;

            $updatePetAdoptionStatus = $this->petsModel->updatePetAdoptionStatus($petID, 1);

            if (!$updatePetAdoptionStatus) {
                throw new RuntimeException("Failed to update pet adoption status");
            }

            $selectQuery = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE id = :lastInsertedID";
            $selectStatement = $this->pdo->prepare($selectQuery);
            $selectStatement->bindValue(':lastInsertedID', $lastInsertedID, PDO::PARAM_STR);

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

    public function updateRequestStatus(string $id, string $status)
    {
        try {
            $query = "UPDATE " . self::ADOPTION_REQUESTS_TABLE . " SET status = :status WHERE id = :id";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(':status', $status, PDO::PARAM_INT);

            $statement->execute();

            $lastUpdatedID = $this->getRequestByID($id);

            return $lastUpdatedID;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
