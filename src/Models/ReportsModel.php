<?php

namespace App\Models;

use PDOException;
use RuntimeException;

use App\Models\PetsModel;

use PDO;


class ReportsModel
{
    private $pdo;
    private $petsModel;
    private const PETS_TABLE = 'pets_tb';
    private const REQUESTS_TABLE = 'adoption_requests_tb';
    private const POSTS_TABLE = 'posts_tb';
    private const POSTS_MEDIA_TABLE = 'media_posts_tb';
    private const POSTS_EVENT_TABLE = 'event_posts_tb';
    private const DONATIONS_TABLE = 'donations_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->petsModel = new PetsModel($pdo);
    }

    public function getAllReports()
    {
        try {
            $totalPetsQuery = "SELECT COUNT(*) as totalPets FROM " . self::PETS_TABLE;
            $totalAdoptedPetsQuery = "SELECT COUNT(*) as totalAdoptedPets FROM " . self::PETS_TABLE . " WHERE adoptionStatus = '2'";
            $totalAvailablePetsQuery = "SELECT COUNT(*) as totalAvailablePets FROM " . self::PETS_TABLE . " WHERE adoptionStatus = '0'";
            $totalAdoptionRequestsQuery = "SELECT COUNT(*) as totalAdoptionRequests FROM " . self::REQUESTS_TABLE . " WHERE status = 'pending'";
            $totalPostsRequestsQuery = "SELECT COUNT(*) as totalPosts FROM " . self::POSTS_TABLE . " WHERE approvalStatus = 'pending'";
            $totalMediaPostsQuery = "SELECT COUNT(*) as totalMediaPosts FROM " . self::POSTS_MEDIA_TABLE . " WHERE approvalStatus = 'pending'";
            $totalEventPostsQuery = "SELECT COUNT(*) as totalEventPosts FROM " . self::POSTS_EVENT_TABLE . " WHERE approvalStatus = 'pending'";
            $totalPostAdoptionRequestsQuery = "SELECT COUNT(*) as totalPostAdoptionRequests FROM " . self::PETS_TABLE . " WHERE approvalStatus = 'pending'";
            $totalDonationsQuery = "SELECT SUM(donationAmount) as totalAmountDonations, COUNT(*) as totalDonators FROM " . self::DONATIONS_TABLE;

            $totalPetsStatement = $this->pdo->prepare($totalPetsQuery);
            $totalAdoptedPetsStatement = $this->pdo->prepare($totalAdoptedPetsQuery);
            $totalAvailablePetsStatement = $this->pdo->prepare($totalAvailablePetsQuery);
            $totalAdoptionRequestsStatement = $this->pdo->prepare($totalAdoptionRequestsQuery);
            $totalPostsRequestsStatement = $this->pdo->prepare($totalPostsRequestsQuery);
            $totalMediaPostsStatement = $this->pdo->prepare($totalMediaPostsQuery);
            $totalEventPostsStatement = $this->pdo->prepare($totalEventPostsQuery);
            $totalPostAdoptionRequestsStatement = $this->pdo->prepare($totalPostAdoptionRequestsQuery);
            $totalDonationsStatement = $this->pdo->prepare($totalDonationsQuery);

            $totalPetsStatement->execute();
            $totalAdoptedPetsStatement->execute();
            $totalAvailablePetsStatement->execute();
            $totalAdoptionRequestsStatement->execute();
            $totalPostsRequestsStatement->execute();
            $totalMediaPostsStatement->execute();
            $totalEventPostsStatement->execute();
            $totalPostAdoptionRequestsStatement->execute();
            $totalDonationsStatement->execute();

            $totalPets = $totalPetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAdoptedPets = $totalAdoptedPetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAvailablePets = $totalAvailablePetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAdoptionRequests = $totalAdoptionRequestsStatement->fetch(PDO::FETCH_ASSOC);
            $totalPostsRequests = $totalPostsRequestsStatement->fetch(PDO::FETCH_ASSOC);
            $totalMediaPosts = $totalMediaPostsStatement->fetch(PDO::FETCH_ASSOC);
            $totalEventPosts = $totalEventPostsStatement->fetch(PDO::FETCH_ASSOC);
            $totalPostAdoptionRequests = $totalPostAdoptionRequestsStatement->fetch(PDO::FETCH_ASSOC);
            $donationsData = $totalDonationsStatement->fetch(PDO::FETCH_ASSOC);

            $totalPets = $totalPets['totalPets'];
            $totalAdoptedPets = $totalAdoptedPets['totalAdoptedPets'];
            $totalAvailablePets = $totalAvailablePets['totalAvailablePets'];
            $totalAdoptionRequests = $totalAdoptionRequests['totalAdoptionRequests'];
            $totalPostsRequests = $totalPostsRequests['totalPosts'];
            $totalMediaPosts = $totalMediaPosts['totalMediaPosts'];
            $totalEventPosts = $totalEventPosts['totalEventPosts'];
            $totalPostAdoptionRequests = $totalPostAdoptionRequests['totalPostAdoptionRequests'];
            $totalDonations = $donationsData['totalAmountDonations'];
            $totalDonators = $donationsData['totalDonators'];

            return [
                'totalPets' => $totalPets,
                'totalAdoptedPets' => $totalAdoptedPets,
                'totalAvailablePets' => $totalAvailablePets,
                'totalRequests' => $totalPostsRequests + $totalMediaPosts + $totalEventPosts + $totalPostAdoptionRequests,
                'totalAdoptionRequests' => $totalAdoptionRequests,
                'totalPostsRequests' => $totalPostsRequests,
                'totalMediaPostsRequests' => $totalMediaPosts,
                'totalEventPostsRequests' => $totalEventPosts,
                'totalAdoptionPostsRequests' => $totalPostAdoptionRequests,
                'totalDonations' => $totalDonations,
                'totalDonators' => $totalDonators
            ];
        } catch (PDOException $e) {
            throw new RuntimeException('Error fetching total pets: ' . $e->getMessage());
        }
    }
}
