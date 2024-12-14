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

        $query = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE status = :status AND typeOfRequest = :typeOfRequest AND userOwnerID IS NULL OR userOwnerID = ''";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $formattedStatusString, PDO::PARAM_STR);
        $statement->bindValue(':typeOfRequest', $typeOfRequest, PDO::PARAM_STR);

        try {
            $statement->execute();
            $requests = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) {
                return ['status' => 'failed', 'message' => 'No requests found'];
            }

            return ['status' => 'success', 'message' => 'Successfully fetched all requests', 'data' => $requests];
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
            }

            $selectQuery = "SELECT * FROM " . self::ADOPTION_REQUESTS_TABLE . " WHERE id = :lastInsertedID";
            $selectStatement = $this->pdo->prepare($selectQuery);
            $selectStatement->bindValue(':lastInsertedID', $lastInsertedID, PDO::PARAM_STR);

            try {
                $selectStatement->execute();

                // Fetch the last inserted data
                $lastInsertedData = $selectStatement->fetch(PDO::FETCH_ASSOC);

                if (empty($lastInsertedData)) {
                    return ['status' => 'failed', 'message' => 'Failed to fetch last inserted data'];
                }

                return ['status' => 'success', 'message' => 'Successfully added new user request', 'data' => $lastInsertedData];
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

    public function cancelMultitpleRequests($payload)
    {
        try {
            $reason = $payload['reason'] ?? 'n/a';
            $status = $payload['status'] ?? 'cancelled';
            $requestIDs = $payload['requestIDs'];

            // Create the list of named placeholders for the IN clause
            $placeholders = array_map(function ($index) {
                return ":requestID_$index";
            }, range(0, count($requestIDs) - 1));

            // Build the query with named placeholders
            $query = "UPDATE " . self::ADOPTION_REQUESTS_TABLE . " 
                  SET status = :status, reason = :reason 
                  WHERE id IN (" . implode(',', $placeholders) . ")";

            $statement = $this->pdo->prepare($query);

            // Bind the status and reason
            $statement->bindValue(':status', $status, PDO::PARAM_STR);
            $statement->bindValue(':reason', $reason, PDO::PARAM_STR);

            // Bind the request IDs using named parameters
            foreach ($requestIDs as $index => $requestID) {
                $statement->bindValue(":requestID_$index", $requestID, PDO::PARAM_INT);
            }

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to cancel requests'];
            }

            return ['status' => 'success', 'message' => 'Successfully cancelled requests'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
