<?php

namespace App\Models;

use PDO;

use PDOException;
use RuntimeException;

class PetsModel
{
    private $pdo;
    private const PETS_TABLE = 'pets_tb';
    const ADOPTION_STATUS_MAP = [
        'available' => '0',
        'pending' => '1',
        'adopted' => '2',
        'not available' => '3',
        'return' => '4',
        '0' => 'available',
        '1' => 'pending',
        '2' => 'adopted',
        '3' => 'not available',
        '4' => 'return',
    ];


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPets()
    {
        try {
            $query = "SELECT * FROM " . self::PETS_TABLE;

            $statement = $this->pdo->prepare($query);

            $statement->execute();

            $pets = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pets)) {
                return [];
            }

            foreach ($pets as $key => $pet) {
                // Decode fields that were sanitized
                $pets[$key]['petName'] = htmlspecialchars_decode($pet['petName'], ENT_QUOTES);
                $pets[$key]['petType'] = htmlspecialchars_decode($pet['petType'], ENT_QUOTES);
                $pets[$key]['petBreed'] = htmlspecialchars_decode($pet['petBreed'], ENT_QUOTES);
                $pets[$key]['petHistory'] = htmlspecialchars_decode($pet['petHistory'], ENT_QUOTES);

                // Map adoption status
                $pets[$key]['adoptionStatus'] = array_search($pet['adoptionStatus'], self::ADOPTION_STATUS_MAP);
            }

            return $pets;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }


    public function getAllPetsByAdoptionStatus(string $status)
    {
        try {

            $status = self::ADOPTION_STATUS_MAP[$status];

            $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE adoptionStatus = :status AND approvalStatus = 'approved'";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            $pets = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pets)) {
                return [];
            }

            foreach ($pets as $key => $pet) {
                $pets[$key]['adoptionStatus'] = array_search($pet['adoptionStatus'], self::ADOPTION_STATUS_MAP);
            }

            return $pets;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsByUserIDAndApprovalStatus(string $userID, string $approvalStatus)
    {
        try {
            $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE userOwnerID = :userID AND approvalStatus = :approvalStatus";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':approvalStatus', $approvalStatus, PDO::PARAM_STR);

            $statement->execute();
            $pets = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pets)) {
                return [];
            }

            $pets = array_map(function ($pet) {
                $pet['adoptionStatus'] = array_search($pet['adoptionStatus'], self::ADOPTION_STATUS_MAP);
                return $pet;
            }, $pets);

            return $pets;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getPetByID(string $petID)
    {
        try {
            $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE id = :petID";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':petID', $petID, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getPetByIDAndAdoptionStatus(string $petID, string $adoptionStatus)
    {
        try {
            $status = self::ADOPTION_STATUS_MAP[$adoptionStatus];

            $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE id = :petID AND adoptionStatus = :adoptionStatus";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':petID', $petID, PDO::PARAM_STR);
            $statement->bindValue(':adoptionStatus', $status, PDO::PARAM_INT);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetTypes()
    {
        try {
            $query = "SELECT DISTINCT petType FROM " . self::PETS_TABLE;
            $statement = $this->pdo->prepare($query);

            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            $petTypes = array_map(function ($row) {
                return $row['petType'];
            }, $result);

            return $petTypes;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetBreedsByType(string $petType)
    {
        $query = "SELECT DISTINCT petBreed FROM " . self::PETS_TABLE . " WHERE petType = :petType";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':petType', $petType, PDO::PARAM_STR);


        try {
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            $petBreeds = array_map(function ($row) {
                return $row['petBreed'];
            }, $result);

            return $petBreeds;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsAgeCategories()
    {
        try {
            $query = "SELECT DISTINCT ageCategory FROM " . self::PETS_TABLE;
            $statement = $this->pdo->prepare($query);

            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            $ageCategories = array_map(function ($row) {
                return $row['ageCategory'];
            }, $result);

            return $ageCategories;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPet(array $payload)
    {
        $userID = null;

        if (isset($payload['userOwnerID']) && $payload['userOwnerID'] !== null) {
            $userID = $payload['userOwnerID'];
        }

        $payload['adoptionStatus'] = self::ADOPTION_STATUS_MAP[$payload['adoptionStatus']];
        $id = $payload['id'];
        $petName = $payload['petName'];
        $petAge = $payload['petAge'];
        $petAgeCategory = $payload['petAgeCategory'];
        $petGender = $payload['petGender'];
        $petType = $payload['petType'];
        $petBreed = $payload['petBreed'];
        $petColor = $payload['petColor'];
        $petVacHistory = $payload['petVacHistory'];
        $petHistory = $payload['petHistory'];
        $petPhotoURL = $payload['petPhotoURL'];
        $adoptionStatus = $payload['adoptionStatus'];
        $approvalStatus = $payload['approvalStatus'];
        $postType = $payload['postType'];


        $query = "INSERT INTO " . self::PETS_TABLE . " (id, userOwnerID, petName, petAge, petAgeCategory, petGender, petType, petBreed, petColor,  petVacHistory, petHistory, petPhotoURL, adoptionStatus, approvalStatus, postType) VALUES (:id, :userOwnerID, :petName, :petAge, :petAgeCategory, :petGender, :petType, :petBreed, :petColor, :petVacHistory, :petHistory, :petPhotoURL, :adoptionStatus, :approvalStatus, :postType)";

        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':id' => $id,
            ':userOwnerID' => $userID,
            ':petName' => $petName,
            ':petAge' => $petAge,
            ':petAgeCategory' => $petAgeCategory,
            ':petGender' => $petGender,
            ':petType' => $petType,
            ':petBreed' => $petBreed,
            ':petColor' => $petColor,
            ':petVacHistory' => $petVacHistory,
            ':petHistory' => $petHistory,
            ':petPhotoURL' => $petPhotoURL,
            ':adoptionStatus' => $adoptionStatus,
            ':approvalStatus' => $approvalStatus,
            ':postType' => $postType,
        ];

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Error: Failed to add new pet'];
            }

            return ['status' => 'success', 'message' => 'New pet added successfully', 'data' => $payload['id']];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePetData(string $petID, array $payload)
    {
        try {
            $payload['adoptionStatus'] = self::ADOPTION_STATUS_MAP[(string) $payload['adoptionStatus']];
            $query = "UPDATE " . self::PETS_TABLE . " SET ";

            foreach ($payload as $key => $value) {
                if ($key === array_key_last($payload)) {
                    $query .= $key . " = :" . $key . " WHERE id = :petID";
                } else {
                    $query .= $key . " = :" . $key . ", ";
                }
            }

            $statement = $this->pdo->prepare($query);

            foreach ($payload as $key => $value) {
                $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }

            $statement->bindValue(':petID', $petID, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'No changes made'];
            }

            return ['status' => 'success', 'message' => 'Pet data updated successfully'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePetAdoptionStatus(string $petID, string $status)
    {

        $query = "UPDATE " . self::PETS_TABLE . " SET adoptionStatus = :status WHERE id = :petID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_INT);
        $statement->bindValue(':petID', $petID, PDO::PARAM_STR);

        try {

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }


    public function updatePetApprovalStatus(string $petID, string $status, string $reason)
    {
        $query = "UPDATE " . self::PETS_TABLE . " SET approvalStatus = :status, reason = :reason WHERE id = :petID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);
        $statement->bindValue(':petID', $petID, PDO::PARAM_STR);
        $statement->bindValue(':reason', $reason, PDO::PARAM_STR);

        try {

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
    public function deletePet(string $petID)
    {
        $deletePetInAppointments = "DELETE FROM appointments_tb WHERE petID = :petID";

        $statement = $this->pdo->prepare($deletePetInAppointments);
        $statement->bindValue(':petID', $petID, PDO::PARAM_STR);

        $statement->execute();

        $deletePetInAdoptionRequests = "DELETE FROM adoption_requests_tb WHERE petID = :petID";

        $statement = $this->pdo->prepare($deletePetInAdoptionRequests);
        $statement->bindValue(':petID', $petID, PDO::PARAM_STR);
        $statement->execute();

        $query = "DELETE FROM " . self::PETS_TABLE . " WHERE id = :petID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':petID', $petID, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
