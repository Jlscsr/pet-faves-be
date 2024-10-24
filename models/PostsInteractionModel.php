<?php

namespace Models;

use PDOException;

use PDO;

use RuntimeException;
use InvalidArgumentException;

class PostsInteractionModel
{
    private $pdo;
    private const POST_INTERACTIONS_TABLE = 'posts_likes_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPostLikesByPostID($posts)
    {
        if (!is_array($posts)) {
            throw new InvalidArgumentException("Invalid or missing posts parameter");
            return;
        }
    }

    public function addNewPostInteraction($payload)
    {
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
            return;
        }

        $postID = $payload['postID'];
        $userID = $payload['userID'];
        $postType = $payload['typeOfPost'];

        $query = "INSERT INTO " . self::POST_INTERACTIONS_TABLE . " (postID, userID, postType) VALUES (:postID, :userID, :postType)";
        $statement = $this->pdo->prepare($query);

        $bindParams = [
            ':postID' => $postID,
            ':userID' => $userID,
            ':postType' => $postType
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

    public function deletePostInteraction($params)

    {
        try {
            if (!is_array($params) && empty($params)) {
                ResponseHelper::sendErrorResponse("Invalid or missing payload parameter", 400);
                return;
            }

            $postID = $params['postID'];
            $userID = $params['userID'];

            $query = "DELETE FROM " . self::POST_INTERACTIONS_TABLE . " WHERE postID = :postID AND userID = :userID";
            $statement = $this->pdo->prepare($query);

            $bindParams = [
                ':postID' => $postID,
                ':userID' => $userID
            ];

            foreach ($bindParams as $key => $value) {
                $statement->bindValue($key, $value, PDO::PARAM_INT);
            }

            $statement->execute();
            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
