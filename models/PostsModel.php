<?php

namespace Models;

use PDOException;
use RuntimeException;

use PDO;


class PostsModel
{
    private $pdo;
    private $cachedAllPosts = null;
    private $cachedAllPostsByStatus = [];
    private const POSTS_TABLE = 'posts_tb';
    private const MEDIA_POSTS_TABLE = 'media_posts_tb';
    private const EVENT_POSTS_TABLE = 'event_posts_tb';
    private const PETS_TABLE = 'pets_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPosts(string $status, int $offset, int $limit)
    {
        try {
            if ($this->cachedAllPosts === null) {
                $allPosts = [];

                // Fetch posts, media, event, and pets with user data
                $queryPosts = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city 
                FROM " . self::POSTS_TABLE . " p
                JOIN users_tb u ON p.userID = u.id
                WHERE p.approvalStatus = :approvalStatus
                ";

                $queryMediaPosts = "
                SELECT mp.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city 
                FROM " . self::MEDIA_POSTS_TABLE . " mp
                JOIN users_tb u ON mp.userID = u.id
                WHERE mp.approvalStatus = :approvalStatus
                ";

                $queryEventPosts = "
                SELECT ep.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city 
                FROM " . self::EVENT_POSTS_TABLE . " ep
                JOIN users_tb u ON ep.userID = u.id
                WHERE ep.approvalStatus = :approvalStatus
                ";

                $queryPets = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city 
                FROM " . self::PETS_TABLE . " p
                JOIN users_tb u ON p.userOwnerID = u.id
                WHERE p.approvalStatus = :approvalStatus AND p.userOwnerID IS NOT NULL
                ";

                // Execute and merge all queries
                $statementPosts = $this->pdo->prepare($queryPosts);
                $statementPosts->bindValue(':approvalStatus', $status);
                $statementPosts->execute();
                $allPosts = array_merge($allPosts, $statementPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementMediaPosts = $this->pdo->prepare($queryMediaPosts);
                $statementMediaPosts->bindValue(':approvalStatus', $status);
                $statementMediaPosts->execute();
                $allPosts = array_merge($allPosts, $statementMediaPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementEventPosts = $this->pdo->prepare($queryEventPosts);
                $statementEventPosts->bindValue(':approvalStatus', $status);
                $statementEventPosts->execute();
                $allPosts = array_merge($allPosts, $statementEventPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementPets = $this->pdo->prepare($queryPets);
                $statementPets->bindValue(':approvalStatus', $status);
                $statementPets->execute();
                $allPosts = array_merge($allPosts, $statementPets->fetchAll(PDO::FETCH_ASSOC));

                // Sort all posts by createdAt in descending order
                usort($allPosts, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });

                $this->cachedAllPosts = $allPosts; // Store in cache for future use
            }

            if ($limit > 0) {
                $pagedPosts = array_slice($this->cachedAllPosts, $offset, $limit);
            } else {
                $pagedPosts = array_slice($this->cachedAllPosts, $offset); // Fetch all posts
            }

            foreach ($pagedPosts as &$post) {
                $postId = $post['id'];
                $postType = $post['postType'];
                $queryLikes = "
                SELECT * FROM posts_likes_tb
                WHERE postID = :postId AND postType = :postType";

                $statementLikes = $this->pdo->prepare($queryLikes);
                $statementLikes->bindParam(':postId', $postId, PDO::PARAM_INT);
                $statementLikes->bindParam(':postType', $postType, PDO::PARAM_STR);
                $statementLikes->execute();
                $likesData = $statementLikes->fetchAll(PDO::FETCH_ASSOC);

                // Add user and likes data
                $post['user'] = [
                    'id' => $post['userID'],
                    'firstName' => $post['firstName'],
                    'lastName' => $post['lastName'],
                    'selfieImageURL' => $post['selfieImageURL'],
                    'province' => $post['province'],
                    'city' => $post['city'],
                ];
                $post['likes'] = !empty($likesData) ? $likesData : [];

                // Optionally remove user-related keys from the top-level post
                unset($post['userID'], $post['firstName'], $post['lastName'], $post['selfieImageURL'], $post['province'], $post['city']);
            }

            return $pagedPosts;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPostsByTypeOfPost(string $typeOfPost, int $offset, int $limit)
    {
        try {
            // Check if cached posts exist for the given status
            if (!isset($this->cachedAllPostsByStatus[$typeOfPost])) {
                $allPosts = [];

                if ($typeOfPost === 'petUpdates') {
                    $queryPosts = "
                    SELECT p.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::POSTS_TABLE . " p
                    JOIN users_tb u ON p.userID = u.id 
                    WHERE p.approvalStatus = 'approved'
                ";

                    $queryMediaPosts = "
                    SELECT m.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::MEDIA_POSTS_TABLE . " m
                    JOIN users_tb u ON m.userID = u.id 
                    WHERE m.approvalStatus = 'approved'
                ";

                    $queryEventPosts = "
                    SELECT e.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL,u.province, u.city FROM " . self::EVENT_POSTS_TABLE . " e
                    JOIN users_tb u ON e.userID = u.id 
                    WHERE e.approvalStatus = 'approved'
                ";

                    // Execute and merge all three queries
                    $statementPosts = $this->pdo->prepare($queryPosts);

                    $statementPosts->execute();
                    $posts = $statementPosts->fetchAll(PDO::FETCH_ASSOC);
                    $allPosts = array_merge($allPosts, $posts);

                    $statementMediaPosts = $this->pdo->prepare($queryMediaPosts);
                    $statementMediaPosts->execute();
                    $mediaPosts = $statementMediaPosts->fetchAll(PDO::FETCH_ASSOC);
                    $allPosts = array_merge($allPosts, $mediaPosts);

                    $statementEventPosts = $this->pdo->prepare($queryEventPosts);
                    $statementEventPosts->execute();
                    $eventPosts = $statementEventPosts->fetchAll(PDO::FETCH_ASSOC);
                    $allPosts = array_merge($allPosts, $eventPosts);
                } elseif ($typeOfPost === 'adoption') {
                    $queryAdoptionPosts = "
                    SELECT p.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::PETS_TABLE . " p
                    JOIN users_tb u ON p.userOwnerID = u.id 
                    WHERE p.approvalStatus = 'approved' AND p.userOwnerID IS NOT NULL
                ";

                    $statementAdoptionPosts = $this->pdo->prepare($queryAdoptionPosts);
                    $statementAdoptionPosts->execute();
                    $allPosts = $statementAdoptionPosts->fetchAll(PDO::FETCH_ASSOC);
                }

                // Format posts to include user details in a "user" key
                foreach ($allPosts as &$post) {
                    $postId = $post['id'];
                    $postType = $post['postType'];
                    $queryLikes = "
                    SELECT * FROM posts_likes_tb
                    WHERE postID = :postId AND postType = :postType";

                    $statementLikes = $this->pdo->prepare($queryLikes);
                    $statementLikes->bindParam(':postId', $postId, PDO::PARAM_INT);
                    $statementLikes->bindParam(':postType', $postType, PDO::PARAM_STR);
                    $statementLikes->execute();
                    $likesData = $statementLikes->fetchAll(PDO::FETCH_ASSOC);

                    $post['user'] = [
                        'id' => $post['userId'],
                        'firstName' => $post['firstName'],
                        'lastName' => $post['lastName'],
                        'selfieImageURL' => $post['selfieImageURL'],
                        'province' => $post['province'],
                        'city' => $post['city'],
                    ];
                    $post['likes'] = !empty($likesData) ? $likesData : [];
                    // Remove user details from top level
                    unset($post['userId'], $post['firstName'], $post['lastName'], $post['selfieImageURL'], $post['province'], $post['city']);
                }

                // Sort combined results by createdAt in descending order
                usort($allPosts, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });

                // Cache the combined results for the current status
                $this->cachedAllPostsByStatus[$typeOfPost] = $allPosts; // Store in cache
            }

            // Apply limit and offset on cached posts
            if ($limit > 0) {
                $pagedPosts = array_slice($this->cachedAllPostsByStatus[$typeOfPost], $offset, $limit);
            } else {
                $pagedPosts = array_slice($this->cachedAllPostsByStatus[$typeOfPost], $offset); // Fetch all posts
            }

            return $pagedPosts; // Return the limited and offset posts
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPost(array $payload)
    {
        try {

            $userID = (int) $payload['userID'];
            $postDescription = $payload['postDescription'];
            $approvalStatus = $payload['approvalStatus'];
            $postType = $payload['postType'];

            $query = "INSERT INTO " . self::POSTS_TABLE . " (userID, postDescription, approvalStatus, postType) VALUES (:userID, :postDescription, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':userID', $userID, PDO::PARAM_INT);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPostMedia(array $payload)
    {
        try {
            $userID = (int) $payload['userID'];
            $postDescription = $payload['postDescription'];
            $mediaURL = $payload['mediaURL'] ?? null;
            $approvalStatus = $payload['approvalStatus'];
            $postType = $payload['postType'];
            $mediaType = $payload['mediaType'];

            $query = "INSERT INTO " . self::MEDIA_POSTS_TABLE . " (userID, postDescription, mediaURL, mediaType, approvalStatus, postType) VALUES (:userID, :postDescription, :mediaURL, :mediaType, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':userID', $userID, PDO::PARAM_INT);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':mediaURL', $mediaURL, PDO::PARAM_STR);
            $statement->bindParam(':mediaType', $mediaType, PDO::PARAM_INT);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() > 0) {
                return [
                    "postID" => $this->pdo->lastInsertId(),
                ];
            }
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewEventPost(array $payload)
    {
        try {
            $userID = (int) $payload['userID'];
            $postDescription = $payload['postDescription'];
            $approvalStatus = $payload['approvalStatus'];
            $eventDate = $payload['eventDate'];
            $eventTime = $payload['eventTime'];
            $eventLocation = $payload['eventLocation'];
            $postType = $payload['postType'];

            $query = "INSERT INTO " . self::EVENT_POSTS_TABLE . " (userID, postDescription, eventDate, eventTime, eventLocation, approvalStatus, postType) VALUES (:userID, :postDescription, :eventDate, :eventTime, :eventLocation, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':userID', $userID, PDO::PARAM_INT);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':eventDate', $eventDate, PDO::PARAM_STR);
            $statement->bindParam(':eventTime', $eventTime, PDO::PARAM_STR);
            $statement->bindParam(':eventLocation', $eventLocation, PDO::PARAM_STR);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePostMedia(int $postID, string $mediaURL)
    {
        try {
            $query = "UPDATE " . self::MEDIA_POSTS_TABLE . " SET mediaURL = :mediaURL WHERE id = :postID";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':mediaURL', $mediaURL, PDO::PARAM_STR);
            $statement->bindParam(':postID', $postID, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePostApprovalStatus(int $postID, string $status, string $postType)
    {
        try {

            $table = null;

            if ($postType === 'event') {
                $table = self::EVENT_POSTS_TABLE;
            } else if ($postType === 'media') {
                $table = self::MEDIA_POSTS_TABLE;
            } else {
                $table = self::POSTS_TABLE;
            }


            $query = "UPDATE " . $table . " SET approvalStatus = :status WHERE id = :postID AND postType = :postType";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':status', $status, PDO::PARAM_STR);
            $statement->bindParam(':postID', $postID, PDO::PARAM_INT);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
