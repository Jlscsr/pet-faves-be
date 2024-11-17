<?php

namespace App\Models;

use App\Models\PetsModel;

use PDOException;

use PDO;

use RuntimeException;

class RequestsModel
{
    private $pdo;
    private const ADOPTION_REQUESTS_TABLE = 'adoption_requests_tb';
    private $petsModel;

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

    public function getAllRequestsByStatusAndTypeOfRequest(string $status, string $typeOfRequest)
    {
        $formattedStatusString = str_replace("+", " ", $status);
        $formattedStatusString = str_replace("%20", " ", $formattedStatusString);

        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE status = :status AND typeOfRequest = :typeOfRequest AND userOwnerID IS NULL";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $formattedStatusString, PDO::PARAM_STR);
        $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

        try {
            $statement->execute();
            $requests = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) {
                return [];
            }

            return $requests;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllRequestsByUserOwnerIDAndStatus(string $userOwnerID, string $status)
    {
        $formattedStatusString = str_replace("+", " ", $status);

        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userOwnerID = :userOwnerID AND status = :status";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userOwnerID', $userOwnerID, PDO::PARAM_STR);
        $statement->bindValue(':status', $formattedStatusString, PDO::PARAM_STR);

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

    public function getUserRequestByUserIDAndTypeOfRequest(string $userID, string $typeOfRequest)
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :userID AND typeOfRequest = :typeOfRequest";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
        $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

        try {
            $statement->execute();
            $requests = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) {
                return [];
            }

            return $requests;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserRequestByUserIDAndStatus(string $userID, string $status)
    {
        try {
            $formattedStatusString = str_replace("+", " ", $status);

            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE userID = :userID AND status = :status";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':status', $formattedStatusString, PDO::PARAM_STR);

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

    public function getAllReturnRequests()
    {
        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE typeOfRequest = 'return'";

        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute();
            $returnRequests = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($returnRequests)) {
                return [];
            }

            return $returnRequests;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllReturnRequestsByStatus(string $status)
    {
        try {
            $formattedStatusString = str_replace("+", " ", $status);

            $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE typeOfRequest = 'return' AND status = :status";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(":status", $formattedStatusString, PDO::PARAM_STR);

            $statement->execute();

            $returnRequests = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($returnRequests)) {
                return [];
            }

            return $returnRequests;
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
            $typeOfRequest = $payload['typeOfRequest'] ?? null;

            $query = "INSERT INTO " . self::ADOPTION_REQUESTS_TABLE . " (id, userID, userOwnerID, petID, status, typeOfRequest) VALUES (:id, :userID, :userOwnerID, :petID, :status, :typeOfRequest)";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':userOwnerID', $userOwnerID, PDO::PARAM_STR);
            $statement->bindValue(':petID', $petID, PDO::PARAM_STR);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

            $statement->execute();

            $lastInsertedID = $id;

            if ($typeOfRequest === 'return') {
                $updatePetAdoptionStatus = $this->petsModel->updatePetAdoptionStatus($petID, 4);

                if (!$updatePetAdoptionStatus) {
                    throw new RuntimeException('Failed to Update pet adoption status');
                }
            } else {
                $updatePetAdoptionStatus = $this->petsModel->updatePetAdoptionStatus($petID, 1);

                if (!$updatePetAdoptionStatus) {
                    throw new RuntimeException("Failed to update pet adoption status");
                }
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

    public function updateRequestStatus(string $id, string $status, string $reason)
    {
        try {

            $query = "UPDATE " . self::ADOPTION_REQUESTS_TABLE . " SET status = :status, reason = :reason WHERE id = :id";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);
            $statement->bindValue(':reason', $reason, PDO::PARAM_STR);

            $statement->execute();

            $lastUpdatedID = $this->getRequestByID($id);

            return $lastUpdatedID;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateRequestTypeOfRequest(string $id, string $typeOfRequest)
    {
        try {
            $query = "UPDATE " . self::ADOPTION_REQUESTS_TABLE . " SET typeOfRequest = :typeOfRequest WHERE id = :id";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

            $statement->execute();

            $lastUpdatedID = $this->getRequestByID($id);

            return $lastUpdatedID;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
