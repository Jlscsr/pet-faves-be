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

    public function getAllPets()
    {
        $query = "SELECT * FROM " . self::PETS_TABLE;
        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsByLabel($label)
    {
        if (!$label) {
            throw new InvalidArgumentException('Invalid or missing label parameter');
            return;
        }

        if ($label === 'fadoption') {
            $label = 'adoption';
        }

        $query = "SELECT * FROM " . self::PETS_TABLE . " WHERE label = :label";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':label', $label, PDO::PARAM_STR);

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

            return $statement->fetchAll(PDO::FETCH_ASSOC);
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

    public function addNewPet($payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $userID = null;

        if (isset($payload['user_id'])) {
            $userID = $payload['user_id'];
        }

        $petName = $payload['petName'];
        $petAge = $payload['petAge'];
        $petGender = $payload['petGender'];
        $petType = $payload['petType'];
        $petBreed = $payload['petBreed'];
        $petVacHistory = $payload['petVacHistory'];
        $petHistory = $payload['petHistory'];
        $petPhotoURL = $payload['petPhotoURL'];
        $label = $payload['label'];

        $query = "INSERT INTO " . self::PETS_TABLE . " (userID, petName, age, gender, petType, petBreed, petVacHistory, petHistory, petPhotoURL, label) VALUES (:userID, :petName, :petAge, :petGender, :petType, :petBreed, :petVacHistory, :petHistory, :petPhotoURL, :label)";
        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':userID' => $userID,
            ':petName' => $petName,
            ':petAge' => $petAge,
            ':petGender' => $petGender,
            ':petType' => $petType,
            ':petBreed' => $petBreed,
            ':petVacHistory' => $petVacHistory,
            ':petHistory' => $petHistory,
            ':petPhotoURL' => $petPhotoURL,
            ':label' => $label,
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
}
