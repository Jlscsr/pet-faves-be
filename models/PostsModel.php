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
    private const PET_FEEDS_TABLE = 'pet_feeds_tb';
    private const POSTS_TABLE = 'posts_tb';
    private const MEDIA_POSTS_TABLE = 'media_posts_tb';
    private const EVENT_POSTS_TABLE = 'event_posts_tb';
    private const PETS_TABLE = 'pets_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllPosts($offset, $limit)
    {
        try {
            if ($this->cachedAllPosts === null) {
                $allPosts = [];

                // Fetch posts with user data
                $queryPosts = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::POSTS_TABLE . " p
                JOIN users_tb u ON p.userID = u.id
                WHERE p.approvalStatus = 'approved'
            ";

                // Fetch media posts with user data
                $queryMediaPosts = "
                SELECT mp.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::MEDIA_POSTS_TABLE . " mp
                JOIN users_tb u ON mp.userID = u.id
                WHERE mp.approvalStatus = 'approved'
            ";

                // Fetch event posts with user data
                $queryEventPosts = "
                SELECT ep.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::EVENT_POSTS_TABLE . " ep
                JOIN users_tb u ON ep.userID = u.id
                WHERE ep.approvalStatus = 'approved'
            ";

                // Fetch pets with user data
                $queryPets = "
                SELECT p.*, u.id AS userID, u.firstName, u.lastName, u.selfieImageURL, u.province, u.city FROM " . self::PETS_TABLE . " p
                JOIN users_tb u ON p.userOwnerID = u.id
                WHERE p.approvalStatus = 'approved' AND p.userOwnerID IS NOT NULL
            ";

                // Execute and merge all queries
                $statementPosts = $this->pdo->prepare($queryPosts);
                $statementPosts->execute();
                $allPosts = array_merge($allPosts, $statementPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementMediaPosts = $this->pdo->prepare($queryMediaPosts);
                $statementMediaPosts->execute();
                $allPosts = array_merge($allPosts, $statementMediaPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementEventPosts = $this->pdo->prepare($queryEventPosts);
                $statementEventPosts->execute();
                $allPosts = array_merge($allPosts, $statementEventPosts->fetchAll(PDO::FETCH_ASSOC));

                $statementPets = $this->pdo->prepare($queryPets);
                $statementPets->execute();
                $allPosts = array_merge($allPosts, $statementPets->fetchAll(PDO::FETCH_ASSOC));

                // Sort all posts by createdAt in descending order
                usort($allPosts, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });

                $this->cachedAllPosts = $allPosts; // Store in cache for future use
            }

            $pagedPosts = array_slice($this->cachedAllPosts, $offset, $limit);

            // Format the results to include the user object
            foreach ($pagedPosts as &$post) {
                $post['user'] = [
                    'id' => $post['userID'],
                    'firstName' => $post['firstName'],
                    'lastName' => $post['lastName'],
                    'selfieImageURL' => $post['selfieImageURL'],
                    'province' => $post['province'],
                    'city' => $post['city'],
                ];
                // Optionally remove user-related keys from the top-level post
                unset($post['userID'], $post['firstName'], $post['lastName'], $post['selfieImageURL']);
            }

            return $pagedPosts;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAllPostsByTypeOfPost($typeOfPost, $offset, $limit)
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
                    $post['user'] = [
                        'id' => $post['userId'],
                        'firstName' => $post['firstName'],
                        'lastName' => $post['lastName'],
                        'selfieImageURL' => $post['selfieImageURL'],
                        'province' => $post['province'],
                        'city' => $post['city'],
                    ];
                    // Remove user details from top level
                    unset($post['userId'], $post['firstName'], $post['lastName'], $post['selfieImageURL']);
                }

                // Sort combined results by createdAt in descending order
                usort($allPosts, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });

                // Cache the combined results for the current status
                $this->cachedAllPostsByStatus[$typeOfPost] = $allPosts; // Store in cache
            }

            // Apply limit and offset on cached posts
            $pagedPosts = array_slice($this->cachedAllPostsByStatus[$typeOfPost], $offset, $limit);

            return $pagedPosts; // Return the limited and offset posts
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
