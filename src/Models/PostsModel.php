<?php

namespace App\Models;

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

    public function getAllPostsPetFeeds(string $status)
    {
        try {
            if ($this->cachedAllPosts === null) {
                $allPosts = [];

                // Fetch posts, media, event, and pets with user data
                $queryPosts = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::POSTS_TABLE . " p
                JOIN users_tb u ON p.userID = u.id
                WHERE p.approvalStatus = :approvalStatus
                ";

                $queryMediaPosts = "
                SELECT mp.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::MEDIA_POSTS_TABLE . " mp
                JOIN users_tb u ON mp.userID = u.id
                WHERE mp.approvalStatus = :approvalStatus
                ";

                $queryEventPosts = "
                SELECT ep.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::EVENT_POSTS_TABLE . " ep
                JOIN users_tb u ON ep.userID = u.id
                WHERE ep.approvalStatus = :approvalStatus
                ";

                $queryPets = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::PETS_TABLE . " p 
                JOIN users_tb u ON p.userOwnerID = u.id 
                WHERE p.approvalStatus = :approvalStatus AND p.userOwnerID IS NOT NULL";

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

            // Add likes and user data to each post
            foreach ($this->cachedAllPosts as &$post) {
                $postId = $post['id'];
                $postType = $post['postType'];
                $queryLikes = "
                SELECT * FROM posts_likes_tb
                WHERE postID = :postId AND postType = :postType";

                $statementLikes = $this->pdo->prepare($queryLikes);
                $statementLikes->bindParam(':postId', $postId, PDO::PARAM_STR);
                $statementLikes->bindParam(':postType', $postType, PDO::PARAM_STR);
                $statementLikes->execute();
                $likesData = $statementLikes->fetchAll(PDO::FETCH_ASSOC);

                // Add user and likes data
                $post['user'] = [
                    'id' => $post['userID'],
                    'firstName' => $post['firstName'],
                    'lastName' => $post['lastName'],
                    'selfieImageURL' => $post['selfieImageURL'],
                    'validIDImageURL' => $post['validIDImageURL'],
                    'email' => $post['email'],
                    'phoneNumber' => $post['phoneNumber'],
                    'province' => $post['province'],
                    'city' => $post['city'],
                ];
                $post['likes'] = !empty($likesData) ? $likesData : [];

                // Optionally remove user-related keys from the top-level post
                unset($post['userID'], $post['firstName'], $post['lastName'], $post['selfieImageURL'], $post['province'], $post['city'], $post['email'], $post['phoneNumber'], $post['validIDImageURL']);
            }

            return $this->cachedAllPosts;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }


    public function getAllPostsByTypeOfPost(string $typeOfPost)
    {
        try {
            // Check if cached posts exist for the given status
            if (!isset($this->cachedAllPostsByStatus[$typeOfPost])) {
                $allPosts = [];

                if ($typeOfPost === 'petUpdates') {
                    $queryPosts = "
                SELECT p.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::POSTS_TABLE . " p
                JOIN users_tb u ON p.userID = u.id
                WHERE p.approvalStatus = 'approved'
                ";

                    $queryMediaPosts = "
                SELECT m.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::MEDIA_POSTS_TABLE . " m
                JOIN users_tb u ON m.userID = u.id 
                WHERE m.approvalStatus = 'approved'
                ";

                    $queryEventPosts = "
                SELECT e.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city 
                FROM " . self::EVENT_POSTS_TABLE . " e
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
                } elseif ($typeOfPost === 'post-adoption') {
                    $queryAdoptionPosts = "
                SELECT p.*, u.id AS userId, u.firstName, u.lastName, u.selfieImageURL, u.validIDImageURL, u.email, u.phoneNumber, u.province, u.city
                FROM " . self::PETS_TABLE . " p
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
                        'validIDImageURL' => $post['validIDImageURL'],
                        'email' => $post['email'],
                        'phoneNumber' => $post['phoneNumber'],
                        'province' => $post['province'],
                        'city' => $post['city'],
                    ];
                    $post['likes'] = !empty($likesData) ? $likesData : [];
                    // Remove user details from top level
                    unset($post['userId'], $post['firstName'], $post['lastName'], $post['selfieImageURL'], $post['province'], $post['city'], $post['email'], $post['phoneNumber'], $post['validIDImageURL']);
                }

                // Sort combined results by createdAt in descending order
                usort($allPosts, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });

                // Cache the combined results for the current typeOfPost
                $this->cachedAllPostsByStatus[$typeOfPost] = $allPosts; // Store in cache
            }

            return $this->cachedAllPostsByStatus[$typeOfPost]; // Return all cached posts
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }


    public function getAllPostsByUserIDAndStatus(string $userID, string $status)
    {
        try {
            $allPostsRequests = [];

            $queryPosts = "SELECT * FROM " . self::POSTS_TABLE . " WHERE userID = :userID AND approvalStatus = :approvalStatus";

            $mediaPosts = "SELECT * FROM " . self::MEDIA_POSTS_TABLE . " WHERE userID = :userID AND approvalStatus = :approvalStatus";

            $eventPosts = "SELECT * FROM " . self::EVENT_POSTS_TABLE . " WHERE userID = :userID AND approvalStatus = :approvalStatus";

            $statementPosts = $this->pdo->prepare($queryPosts);
            $statementPosts->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statementPosts->bindValue(':approvalStatus', $status, PDO::PARAM_STR);
            $statementPosts->execute();
            $queryPosts = $statementPosts->fetchAll(PDO::FETCH_ASSOC);
            $allPostsRequests = array_merge($allPostsRequests, $queryPosts);

            $statementMediaPosts = $this->pdo->prepare($mediaPosts);
            $statementMediaPosts->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statementMediaPosts->bindValue(':approvalStatus', $status, PDO::PARAM_STR);
            $statementMediaPosts->execute();
            $queryMediaPosts = $statementMediaPosts->fetchAll(PDO::FETCH_ASSOC);
            $allPostsRequests = array_merge($allPostsRequests, $queryMediaPosts);

            $statementEventPosts = $this->pdo->prepare($eventPosts);
            $statementEventPosts->bindValue(':userID', $userID, PDO::PARAM_STR);
            $statementEventPosts->bindValue(':approvalStatus', $status, PDO::PARAM_STR);
            $statementEventPosts->execute();
            $queryEventPosts = $statementEventPosts->fetchAll(PDO::FETCH_ASSOC);
            $allPostsRequests = array_merge($allPostsRequests, $queryEventPosts);

            usort($allPostsRequests, function ($a, $b) {
                return strtotime($b['updatedAt']) - strtotime($a['updatedAt']);
            });

            return $allPostsRequests;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPostsByIDAndTypeOfPost(string $id, string $typeOfPost)
    {
        try {
            $table = '';

            if ($typeOfPost === 'post') {
                $table = self::POSTS_TABLE;
            } else if ($typeOfPost === 'media') {
                $table = self::MEDIA_POSTS_TABLE;
            } else if ($typeOfPost === 'event') {
                $table = self::EVENT_POSTS_TABLE;
            } else {
                $table = self::PETS_TABLE;
            }

            $query = "SELECT * FROM " . $table . " WHERE id = :id";

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPosts()
    {
        try {
            $query = "SELECT * FROM " . self::POSTS_TABLE;

            $statement = $this->pdo->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllMediaPosts()
    {
        try {
            $query = "SELECT * FROM " . self::MEDIA_POSTS_TABLE;

            $statement = $this->pdo->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllEventPosts()
    {
        try {
            $query = "SELECT * FROM " . self::EVENT_POSTS_TABLE;

            $statement = $this->pdo->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPost(array $payload)
    {
        try {

            $id = $payload['id'];
            $userID = $payload['userID'];
            $postDescription = $payload['postDescription'];
            $approvalStatus = $payload['status'];
            $postType = $payload['postType'];

            $query = "INSERT INTO " . self::POSTS_TABLE . " (id, userID, postDescription, approvalStatus, postType) VALUES (:id, :userID, :postDescription, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':id', $id, PDO::PARAM_STR);
            $statement->bindParam(':userID', $userID, PDO::PARAM_STR);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            return $id;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewPostMedia(array $payload)
    {
        try {
            $id = $payload['id'];
            $userID = $payload['userID'];
            $postDescription = $payload['postDescription'];
            $mediaURL = $payload['mediaURL'] ?? null;
            $approvalStatus = $payload['status'];
            $postType = $payload['postType'];
            $mediaType = $payload['mediaType'];

            $query = "INSERT INTO " . self::MEDIA_POSTS_TABLE . " (id, userID, postDescription, mediaURL, mediaType, approvalStatus, postType) VALUES (:id, :userID, :postDescription, :mediaURL, :mediaType, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':id', $id, PDO::PARAM_STR);
            $statement->bindParam(':userID', $userID, PDO::PARAM_STR);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':mediaURL', $mediaURL, PDO::PARAM_STR);
            $statement->bindParam(':mediaType', $mediaType, PDO::PARAM_STR);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();


            return $id;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewEventPost(array $payload)
    {
        try {
            $id = $payload['id'];
            $userID = $payload['userID'];
            $postDescription = $payload['postDescription'];
            $approvalStatus = $payload['status'];
            $eventDate = $payload['eventDate'];
            $eventTime = $payload['eventTime'];
            $eventLocation = $payload['eventLocation'];
            $postType = $payload['postType'];

            $query = "INSERT INTO " . self::EVENT_POSTS_TABLE . " (id, userID, postDescription, eventDate, eventTime, eventLocation, approvalStatus, postType) VALUES (:id, :userID, :postDescription, :eventDate, :eventTime, :eventLocation, :approvalStatus, :postType)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':id', $id, PDO::PARAM_STR);
            $statement->bindParam(':userID', $userID, PDO::PARAM_STR);
            $statement->bindParam(':postDescription', $postDescription, PDO::PARAM_STR);
            $statement->bindParam(':eventDate', $eventDate, PDO::PARAM_STR);
            $statement->bindParam(':eventTime', $eventTime, PDO::PARAM_STR);
            $statement->bindParam(':eventLocation', $eventLocation, PDO::PARAM_STR);
            $statement->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);

            $statement->execute();

            return $id;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updatePostMedia(string $postID, string $mediaURL)
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

    public function updatePostApprovalStatus(string $postID, string $status, string $postType)
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
            $statement->bindParam(':postID', $postID, PDO::PARAM_STR);
            $statement->bindParam(':status', $status, PDO::PARAM_STR);
            $statement->bindParam(':postType', $postType, PDO::PARAM_STR);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
