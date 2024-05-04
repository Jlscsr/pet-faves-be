<?php

namespace Models;

use PDOException;
use RuntimeException;

use PDO;

class PetFeedsModel
{
    private $pdo;
    private const PET_FEEDS_TABLE = 'pet_feeds_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPetsFromPetFeeds()
    {
        $query = "SELECT users_tb.id as mainUserID, users_tb.firstName, users_tb.middleName, users_tb.lastName, pet_feeds_tb.* FROM users_tb INNER JOIN " . self::PET_FEEDS_TABLE . " ON users_tb.id = pet_feeds_tb.userID";
        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetsFromPetFeedsByStatus($status)
    {
        $query = "SELECT users_tb.id as mainUserID, users_tb.firstName, users_tb.middleName, users_tb.lastName, pet_feeds_tb.* FROM users_tb INNER JOIN " . self::PET_FEEDS_TABLE . " ON users_tb.id = pet_feeds_tb.userID WHERE pet_feeds_tb.approvalStatus = :status";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPetToPetFeeds($payload)
    {
        $userID = (int) $payload['userID'];
        $petName = $payload['petName'];
        $petType = $payload['petType'];
        $petBreed = $payload['petBreed'];
        $petAge = $payload['petAge'];
        $petGender = $payload['petGender'];
        $petCaption = $payload['petCaption'];
        $petPhotoURL = $payload['petPhotoURL'];
        $approvalStatus = $payload['approvalStatus'];

        $query = "INSERT INTO " . self::PET_FEEDS_TABLE . " (userID, petName, petType, petBreed, petAge, petGender, petCaption, petPhotoURL, approvalStatus) VALUES (:userID, :petName, :petType, :petBreed, :petAge, :petGender, :petCaption, :petPhotoURL, :approvalStatus)";
        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':petName' => $petName,
            ':petType' => $petType,
            ':petBreed' => $petBreed,
            ':petAge' => $petAge,
            ':petGender' => $petGender,
            ':petCaption' => $petCaption,
            ':petPhotoURL' => $petPhotoURL,
            ':approvalStatus' => $approvalStatus,
        ];

        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);

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
