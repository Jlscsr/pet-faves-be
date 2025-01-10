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

            // This reports is for dashboard
            $totalPetsQuery = "SELECT COUNT(*) as totalPets FROM " . self::PETS_TABLE;
            $totalAdoptedPetsQuery = "SELECT COUNT(*) as totalAdoptedPets FROM " . self::PETS_TABLE . " WHERE adoptionStatus = '2'";
            $totalAvailablePetsQuery = "SELECT COUNT(*) as totalAvailablePets FROM " . self::PETS_TABLE . " WHERE adoptionStatus = '0'";
            $totalAdoptionRequestsQuery = "SELECT COUNT(*) as totalAdoptionRequests FROM " . self::REQUESTS_TABLE . " WHERE status = 'pending'";
            $totalPostsRequestsQuery = "SELECT COUNT(*) as totalPosts FROM " . self::POSTS_TABLE . " WHERE approvalStatus = 'pending'";
            $totalMediaPostsQuery = "SELECT COUNT(*) as totalMediaPosts FROM " . self::POSTS_MEDIA_TABLE . " WHERE approvalStatus = 'pending'";
            $totalEventPostsQuery = "SELECT COUNT(*) as totalEventPosts FROM " . self::POSTS_EVENT_TABLE . " WHERE approvalStatus = 'pending'";
            $totalDonationsQuery = "SELECT SUM(donationAmount) as totalAmountDonations, COUNT(*) as totalDonators FROM " . self::DONATIONS_TABLE;

            $totalPetsStatement = $this->pdo->prepare($totalPetsQuery);
            $totalAdoptedPetsStatement = $this->pdo->prepare($totalAdoptedPetsQuery);
            $totalAvailablePetsStatement = $this->pdo->prepare($totalAvailablePetsQuery);
            $totalAdoptionRequestsStatement = $this->pdo->prepare($totalAdoptionRequestsQuery);
            $totalPostsRequestsStatement = $this->pdo->prepare($totalPostsRequestsQuery);
            $totalMediaPostsStatement = $this->pdo->prepare($totalMediaPostsQuery);
            $totalEventPostsStatement = $this->pdo->prepare($totalEventPostsQuery);
            $totalDonationsStatement = $this->pdo->prepare($totalDonationsQuery);

            $totalPetsStatement->execute();
            $totalAdoptedPetsStatement->execute();
            $totalAvailablePetsStatement->execute();
            $totalAdoptionRequestsStatement->execute();
            $totalPostsRequestsStatement->execute();
            $totalMediaPostsStatement->execute();
            $totalEventPostsStatement->execute();
            $totalDonationsStatement->execute();

            $totalPets = $totalPetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAdoptedPets = $totalAdoptedPetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAvailablePets = $totalAvailablePetsStatement->fetch(PDO::FETCH_ASSOC);
            $totalAdoptionRequests = $totalAdoptionRequestsStatement->fetch(PDO::FETCH_ASSOC);
            $totalPostsRequests = $totalPostsRequestsStatement->fetch(PDO::FETCH_ASSOC);
            $totalMediaPosts = $totalMediaPostsStatement->fetch(PDO::FETCH_ASSOC);
            $totalEventPosts = $totalEventPostsStatement->fetch(PDO::FETCH_ASSOC);
            $donationsData = $totalDonationsStatement->fetch(PDO::FETCH_ASSOC);

            $totalPets = $totalPets['totalPets'];
            $totalAdoptedPets = $totalAdoptedPets['totalAdoptedPets'];
            $totalAvailablePets = $totalAvailablePets['totalAvailablePets'];
            $totalAdoptionRequests = $totalAdoptionRequests['totalAdoptionRequests'];
            $totalPostsRequests = $totalPostsRequests['totalPosts'];
            $totalMediaPosts = $totalMediaPosts['totalMediaPosts'];
            $totalEventPosts = $totalEventPosts['totalEventPosts'];
            $totalDonations = $donationsData['totalAmountDonations'];
            $totalDonators = $donationsData['totalDonators'];

            return [
                'totalPets' => $totalPets,
                'totalAdoptedPets' => $totalAdoptedPets,
                'totalAvailablePets' => $totalAvailablePets,
                'totalRequests' => $totalPostsRequests + $totalMediaPosts + $totalEventPosts,
                'totalAdoptionRequests' => $totalAdoptionRequests,
                'totalPostsRequests' => $totalPostsRequests,
                'totalMediaPostsRequests' => $totalMediaPosts,
                'totalEventPostsRequests' => $totalEventPosts,
                'totalDonations' => $totalDonations,
                'totalDonators' => $totalDonators
            ];
        } catch (PDOException $e) {
            throw new RuntimeException('Error fetching total pets: ' . $e->getMessage());
        }
    }

    public function generateReports($startDate, $endDate)
    {
        try {

            $currentDate = date('Y-m-d');

            // Fetch the oldest dates in the tables
            $oldestPetsDate = $this->getOldestTableDate(self::PETS_TABLE);
            $oldestAdoptionRequestsDate = $this->getOldestTableDate(self::REQUESTS_TABLE);
            $oldestDonationsDate = $this->getOldestTableDate(self::DONATIONS_TABLE);

            // Determine startDate and endDate
            if ($startDate === 'n/a' && $endDate === 'n/a') {
                $startDate = min($oldestPetsDate, $oldestAdoptionRequestsDate, $oldestDonationsDate);
                $endDate = $currentDate;
            } elseif ($endDate === 'n/a') {
                $endDate = $currentDate;
            } elseif ($startDate === 'n/a') {
                $startDate = min($oldestPetsDate, $oldestAdoptionRequestsDate, $oldestDonationsDate);
            }

            $totalPetsQuery = "SELECT COUNT(*) AS totalPet FROM pets_tb WHERE DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $totalAvailablePetsQuery = "SELECT COUNT(*) AS totalAvailablePets FROM pets_tb WHERE adoptionStatus = '0' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $availablePetsListsQuery = "SELECT * FROM pets_tb WHERE adoptionStatus = '0' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $totalAdoptedPetsQuery = "SELECT COUNT(*) AS totalAdoptedPets FROM pets_tb WHERE adoptionStatus = '2' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $adoptedPetsListsQuery = "SELECT * FROM pets_tb WHERE adoptionStatus = '2' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";

            $totalAdoptionRequestsQuery = "SELECT COUNT(*) AS totalAdoptionRequests FROM adoption_requests_tb WHERE DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $totalCompletedAdoptionRequestsQuery = "SELECT COUNT(*) AS totalCompletedAdoptionRequests FROM adoption_requests_tb WHERE status = 'completed' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $completedAdoptionRequestsListsQuery = "SELECT * FROM adoption_requests_tb WHERE status = 'completed' AND DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";

            $totalAmountDonationsQuery = "SELECT SUM(donationAmount) AS totalAmountDonations FROM donations_tb WHERE DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $totalDonatorsQuery = "SELECT COUNT(*) AS totalDonators FROM donations_tb WHERE DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";
            $donatorsListsQuery = "SELECT * FROM donations_tb WHERE DATE(createdAt) >= :startDate AND DATE(createdAt) <= :endDate";


            $totalPetsStatement = $this->pdo->prepare($totalPetsQuery);
            $totalAvailablePetsStatement = $this->pdo->prepare($totalAvailablePetsQuery);
            $availablePetsListsStatement = $this->pdo->prepare($availablePetsListsQuery);
            $totalAdoptedPetsStatement = $this->pdo->prepare($totalAdoptedPetsQuery);
            $adoptedPetsListsStatement = $this->pdo->prepare($adoptedPetsListsQuery);

            $totalAdoptionRequestsStatement = $this->pdo->prepare($totalAdoptionRequestsQuery);
            $totalCompletedAdoptionRequestsStatement = $this->pdo->prepare($totalCompletedAdoptionRequestsQuery);
            $completedAdoptionRequestsListsStatement = $this->pdo->prepare($completedAdoptionRequestsListsQuery);

            $totalAmountDonationsStatement = $this->pdo->prepare($totalAmountDonationsQuery);
            $totalDonatorsStatement = $this->pdo->prepare($totalDonatorsQuery);
            $donatorsListsStatement = $this->pdo->prepare($donatorsListsQuery);

            $totalPetsStatement->bindParam(':startDate', $startDate);
            $totalPetsStatement->bindParam(':endDate', $endDate);

            $totalAvailablePetsStatement->bindParam(':startDate', $startDate);
            $totalAvailablePetsStatement->bindParam(':endDate', $endDate);

            $availablePetsListsStatement->bindParam(':startDate', $startDate);
            $availablePetsListsStatement->bindParam(':endDate', $endDate);

            $totalAdoptedPetsStatement->bindParam(':startDate', $startDate);
            $totalAdoptedPetsStatement->bindParam(':endDate', $endDate);

            $adoptedPetsListsStatement->bindParam(':startDate', $startDate);
            $adoptedPetsListsStatement->bindParam(':endDate', $endDate);

            $totalAdoptionRequestsStatement->bindParam(':startDate', $startDate);
            $totalAdoptionRequestsStatement->bindParam(':endDate', $endDate);

            $totalCompletedAdoptionRequestsStatement->bindParam(':startDate', $startDate);
            $totalCompletedAdoptionRequestsStatement->bindParam(':endDate', $endDate);

            $completedAdoptionRequestsListsStatement->bindParam(':startDate', $startDate);
            $completedAdoptionRequestsListsStatement->bindParam(':endDate', $endDate);

            $totalAmountDonationsStatement->bindParam(':startDate', $startDate);
            $totalAmountDonationsStatement->bindParam(':endDate', $endDate);

            $totalDonatorsStatement->bindParam(':startDate', $startDate);
            $totalDonatorsStatement->bindParam(':endDate', $endDate);

            $donatorsListsStatement->bindParam(':startDate', $startDate);
            $donatorsListsStatement->bindParam(':endDate', $endDate);

            $totalPetsStatement->execute();
            $totalAvailablePetsStatement->execute();
            $availablePetsListsStatement->execute();
            $totalAdoptedPetsStatement->execute();
            $adoptedPetsListsStatement->execute();

            $totalAdoptionRequestsStatement->execute();
            $totalCompletedAdoptionRequestsStatement->execute();
            $completedAdoptionRequestsListsStatement->execute();

            $totalAmountDonationsStatement->execute();
            $totalDonatorsStatement->execute();
            $donatorsListsStatement->execute();

            // Fetch the date, if there is no data return empty array or interger 0 if its not lists

            $totalPets = $totalPetsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;
            $totalAvailablePets = $totalAvailablePetsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;
            $totalAdoptedPets = $totalAdoptedPetsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;

            $totalAdoptionRequests = $totalAdoptionRequestsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;
            $totalCompletedAdoptionRequests = $totalCompletedAdoptionRequestsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;

            $totalAmountDonations = $totalAmountDonationsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;
            $totalDonators = $totalDonatorsStatement->fetch(PDO::FETCH_ASSOC) ?? 0;

            $pets = $availablePetsListsStatement->fetchAll(PDO::FETCH_ASSOC) ?? [];
            $adoptedPets = $adoptedPetsListsStatement->fetchAll(PDO::FETCH_ASSOC) ?? [];

            $adoptionRequests = $completedAdoptionRequestsListsStatement->fetchAll(PDO::FETCH_ASSOC) ?? [];

            $donators = $donatorsListsStatement->fetchAll(PDO::FETCH_ASSOC) ?? [];


            if (!empty($adoptionRequests)) {
                foreach ($adoptionRequests as $key => $request) {
                    $userID = $request['userID'];
                    $petID = $request['petID'];

                    $userQuery = "SELECT * FROM users_tb WHERE id = :userID";
                    $userStatement = $this->pdo->prepare($userQuery);
                    $userStatement->bindParam(':userID', $userID);
                    $userStatement->execute();
                    $user = $userStatement->fetch(PDO::FETCH_ASSOC);

                    $adoptionRequests[$key]['user'] = $user;

                    $petQuery = "SELECT * FROM pets_tb WHERE id = :petID";
                    $petStatement = $this->pdo->prepare($petQuery);
                    $petStatement->bindParam(':petID', $petID);
                    $petStatement->execute();
                    $pet = $petStatement->fetch(PDO::FETCH_ASSOC);

                    $adoptionRequests[$key]['pet'] = $pet;
                }
            }

            $startDate = date('M d, Y', strtotime($startDate));
            $endDate = date('M d, Y', strtotime($endDate));

            return [
                'status' => 'success',
                'data' => [
                    'reports' => [
                        'date' => [
                            'from' => $startDate,
                            'to' => $endDate
                        ],
                        'pets' => [
                            'stats' => [
                                'totalPets' => $totalPets,
                                'totalAvailablePets' => $totalAvailablePets,
                                'totalAdoptedPets' => $totalAdoptedPets,
                            ],
                            'lists' => [
                                'availablePets' => $pets,
                                'adoptedPets' => $adoptedPets,
                            ]
                        ],
                        'requests' => [
                            'stats' => [
                                'totalAdoptionRequests' => $totalAdoptionRequests,
                                'totalCompletedAdoptionRequests' => $totalCompletedAdoptionRequests,
                            ],
                            'lists' => [
                                'completedAdoptionRequests' => $adoptionRequests,
                            ]
                        ],
                        'donations' => [
                            'stats' => [
                                'totalAmountDonations' => $totalAmountDonations,
                                'totalDonators' => $totalDonators,
                            ],
                            'lists' => [
                                'donators' => $donators
                            ]
                        ]
                    ]

                ]
            ];
        } catch (PDOException $e) {
            throw new RuntimeException('Error generating reports: ' . $e->getMessage());
        }
    }

    private function getOldestTableDate($table)
    {
        try {
            $query = "SELECT DATE(createdAt) AS createdAt FROM $table ORDER BY createdAt ASC LIMIT 1";
            $statement = $this->pdo->prepare($query);

            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['createdAt'] : null; // Return null if no records
        } catch (PDOException $e) {
            throw new RuntimeException("Error getting oldest table date: " . $e->getMessage());
        }
    }
}
