<?php

namespace Models;

use PDOException;

use PDO;

use RuntimeException;

class PetCareModel
{
    private $pdo;
    private const PET_CARE_TABLE = 'pet_care_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPetCarePosts()
    {
        try {
            $query = "SELECT * FROM " . self::PET_CARE_TABLE;

            $statement = $this->pdo->prepare($query);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPetCarePostsByStatus(string $status)
    {
        try {
            $query = "SELECT * FROM " . self::PET_CARE_TABLE . " WHERE status = :status";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':status', $status, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPetCarePost(array $payload)
    {

        $id = $payload['id'];
        $title = $payload['title'];
        $category = $payload['category'];
        $featuredImageURL = $payload['featuredImageURL'] ?? null;
        $description = $payload['description'];
        $content = $payload['content'];
        $status = $payload['status'];

        $query = "INSERT INTO " . self::PET_CARE_TABLE . " (id, title, category, featuredImageURL, description, content, status) VALUES (:id, :title, :category, :featuredImageURL, :description, :content, :status)";

        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':id' => $id,
            ':title' => $title,
            ':category' => $category,
            'featuredImageURL' => $featuredImageURL,
            ':description' => $description,
            ':content' => $content,
            ':status' => $status
        ];

        foreach ($bindParams as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            if ($statement->rowCount() === 0) {
                return false;
            }

            return ['id' => $id];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePetCarePost(string $id, array $payload)
    {
        try {
            $query = "UPDATE " . self::PET_CARE_TABLE . " SET ";

            foreach ($payload as $key => $value) {
                if ($key === array_key_last($payload)) {
                    $query .= $key . " = :" . $key . " WHERE id = :id";
                } else {
                    $query .= $key . " = :" . $key . ", ";
                }
            }

            $statement = $this->pdo->prepare($query);

            foreach ($payload as $key => $value) {
                $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }

            $statement->bindValue(':id', $id, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function deletePetCarePost(string $id)
    {
        try {
            $query = "DELETE FROM " . self::PET_CARE_TABLE . " WHERE id = :id";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
