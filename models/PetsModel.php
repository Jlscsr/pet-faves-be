<?php

namespace Models;

use PDO;

use InvalidArgumentException;
use PDOException;
use RuntimeException;

class PetsModel
{
    private $pdo;
    private const PRODUCTS_TABLE = 'pets_tb';


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPets()
    {
    }

    public function getPetByID($petID)
    {
        if (!$petID) {
            throw new InvalidArgumentException('Invalid or missing product ID parameter');
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

        $petPhotoURL = $payload['petPhotoURL'];
        $petName = $payload['petName'];
        $petAge = $payload['petAge'];
        $petGender = $payload['petGender'];
        $petType = $payload['petType'];
        $petBreed = $payload['petBreed'];
        $petVacHistory = $payload['petVacHistory'];
        $petHistory = $payload['petHistory'];

        $query = "INSERT INTO " . self::PRODUCTS_TABLE . " (userID, petName, age, gender, petType, petBreed, petVacHistory, petHistory, petPhotoURL) VALUES (:userID, :petName, :petAge, :petGender, :petType, :petBreed, :petVacHistory, :petHistory, :petPhotoURL)";
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
            ':petPhotoURL' => $petPhotoURL
        ];

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            return $this->pdo->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
