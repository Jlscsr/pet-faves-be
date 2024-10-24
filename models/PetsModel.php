<?php

namespace Models;

use PDO;

use InvalidArgumentException;
use PDOException;
use RuntimeException;

class PetsModel
{
    private $pdo;
    private const PETS_TABLE = 'pets_tb';


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPets($limit, $offset)
    {
        $query = "SELECT * FROM " . self::PETS_TABLE;

        if ($limit !== 0 || $offset !== 0) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $statement = $this->pdo->prepare($query);

        if ($limit !== 0 || $offset !== 0) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsByAdoptionStatus($status, $limit, $offset)
    {
        $adoptionStatusMap = [
            'available' => 0,
            'pending' => 1,
            'adopted' => 2,
            'for approval' => 3
        ];

        $status = $adoptionStatusMap[$status];

        $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE adoptionStatus = :status";

        if ($limit !== 0 || $offset !== 0) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_INT);

        if ($limit !== 0 || $offset !== 0) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getPetByID($petID)
    {
        if (!$petID) {
            throw new InvalidArgumentException('Invalid or missing pet ID parameter');
        }

        $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE id = :petID";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':petID', $petID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsByLabel($label, $limit, $offset)
    {
        if (!$label) {
            throw new InvalidArgumentException('Invalid or missing label parameter');
            return;
        }

        if ($label === 'fadoption') {
            $label = 'adoption';
        }

        $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE label = :label LIMIT :limit OFFSET :offset";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':label', $label, PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetTypes()
    {
        $query = "SELECT DISTINCT petType FROM " . self::PETS_TABLE;
        $statement = $this->pdo->prepare($query);

        try {
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

    public function getAllPetBreedsByType($petType)
    {
        if (!$petType) {
            throw new InvalidArgumentException('Invalid or missing pet type parameter');
            return;
        }

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
        $query = "SELECT DISTINCT ageCategory FROM " . self::PETS_TABLE;
        $statement = $this->pdo->prepare($query);


        try {
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

    public function addNewPet($payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $userID = null;

        if (isset($payload['userID'])) {
            $userID = $payload['userID'];
        }

        $petName = $payload['petName'];
        $petAge = $payload['petAge'];
        $petAgeCategory = $payload['petAgeCategory'];
        $petGender = $payload['petGender'];
        $petType = $payload['petType'];
        $petBreed = $payload['petBreed'];
        $petVacHistory = $payload['petVacHistory'];
        $petHistory = $payload['petHistory'];
        $petPhotoURL = $payload['petPhotoURL'];
        $adoptionStatus = $payload['adoptionStatus'];
        $approvalStatus = 'pending';
        $postType = 'adoption';


        $query = "INSERT INTO " . self::PETS_TABLE . " (userOwnerID, petName, age, ageCategory, gender, petType, petBreed, petVacHistory, petHistory, petPhotoURL, adoptionStatus, approvalStatus, postType) VALUES (:userID, :petName, :petAge, :petAgeCategory, :petGender, :petType, :petBreed, :petVacHistory, :petHistory, :petPhotoURL, :adoptionStatus, :approvalStatus, :postType)";
        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':userID' => $userID,
            ':petName' => $petName,
            ':petAge' => $petAge,
            ':petAgeCategory' => $petAgeCategory,
            ':petGender' => $petGender,
            ':petType' => $petType,
            ':petBreed' => $petBreed,
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

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePetAdoptionStatus($petID, $status)
    {
        $query = "UPDATE " . self::PETS_TABLE . " SET adoptionStatus = :status WHERE id = :petID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_INT);
        $statement->bindValue(':petID', $petID, PDO::PARAM_INT);

        try {

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (RuntimeException $e) {
            print_r($e->getMessage());
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
