<?php

namespace Models;

use PDOException;

use PDO;

use RuntimeException;

class PostsInteractionModel
{
    private $pdo;
    private const POST_INTERACTIONS_TABLE = 'posts_likes_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function addNewPostInteraction(array $payload)
    {
        try {

            $id = $payload['id'];
            $postID = $payload['postID'];
            $userID = $payload['userID'];
            $postType = $payload['typeOfPost'];

            $query = "INSERT INTO " . self::POST_INTERACTIONS_TABLE . " (id, postID, userID, postType) VALUES (:id,:postID, :userID, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id', $id, PDO::PARAM_STR);
            $statement->bindValue(':postID', $postID, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statement->bindValue(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function deletePostInteraction(string $postID, string $userID)
    {
        try {
            $query = "DELETE FROM " . self::POST_INTERACTIONS_TABLE . " WHERE postID = :postID AND userID = :userID";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':postID', $postID, PDO::PARAM_STR);
            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
