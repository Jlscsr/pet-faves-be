<?php

namespace Models;

use Models\PetsModel;
use Models\UsersModel;
use PDOException;

use PDO;

use RuntimeException;
use InvalidArgumentException;

class RequestsModel
{
    private $pdo;
    private $petsModel;
    private const REQUESTS_TABLE = 'requests_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petsModel = new PetsModel($this->pdo);
        $this->usersModel = new UsersModel($this->pdo);
    }

    public function getRequestByTypeofRequest($type)
    {
        if (!$type) {
            throw new InvalidArgumentException("Invalid or missing status parameter");
            return;
        }

        $query = "SELECT * FROM " . self::REQUESTS_TABLE . " WHERE typeOfRequest = :type ORDER BY id DESC";

        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':type', $type, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getRequestByID($id)
    {
        if (!$id) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

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

    public function getAllRequestsByStatus($status)
    {

        if (!$status) {
            throw new InvalidArgumentException("Invalid or missing status parameter");
            return;
        }

        $query = "SELECT pets_tb.*, requests_tb.*, users_tb.* FROM " . self::REQUESTS_TABLE . " INNER JOIN pets_tb ON pets_tb.id = requests_tb.petID INNER JOIN users_tb ON users_tb.id = requests_tb.userID WHERE requests_tb.status = :status";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
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

        $query = "SELECT pets_tb.*, requests_tb.* FROM " . self::REQUESTS_TABLE . " INNER JOIN pets_tb ON pets_tb.id = requests_tb.petID WHERE requests_tb.userID = :id";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndID($id, $requestID)
    {
        if (!$id || !$requestID) {
            throw new InvalidArgumentException("Invalid or missing id parameter");
            return;
        }

        $query = "SELECT pets_tb.*, requests_tb.*, pets_tb.createdAt AS petCreatedAt, pets_tb.updatedAt AS petUpdatedAt FROM " . self::REQUESTS_TABLE . " INNER JOIN pets_tb ON pets_tb.id = requests_tb.petID WHERE requests_tb.userID = :id AND requests_tb.id = :requestID";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':requestID', $requestID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
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
        $typeOfRequest = $payload['typeOfRequest'];
        $status = $payload['status'];

        $query = "INSERT INTO " . self::REQUESTS_TABLE . " (userID, petID, typeOfRequest, status) VALUES (:userID, :petID, :typeOfRequest, :status)";

        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':userID' => $userID,
            ':petID' => $petID,
            ':typeOfRequest' => $typeOfRequest,
            ':status' => $status,
        ];

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            $lastInsertedID = $this->pdo->lastInsertId();

            if ($typeOfRequest === 'adoption') {
                $updatePetAdoptionStatus = $this->petsModel->updatePetAdoptionStatus($petID, 1);
            }

            if (!$updatePetAdoptionStatus) {
                throw new RuntimeException("Failed to update pet adoption status");
            }

            // Construct and execute a SELECT query to fetch the last inserted data
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

    public function updateRequestStatus($id, $payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $status = $payload['status'];

        $query = "UPDATE " . self::REQUESTS_TABLE . " SET status = :status WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            $lastUpdatedID = $this->getRequestByID($id);

            return $lastUpdatedID;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
