<?php

namespace App\Models;

use PDOException;
use RuntimeException;


use PDO;


class DonationsModel
{
    private $pdo;
    private const DONATIONS_TABLE = 'donations_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function addNewDonation($payload)
    {
        try {
            $query = "INSERT INTO " . self::DONATIONS_TABLE . " (id, donorName, contactDetails, donationAmount) VALUES (:id, :donorName, :contactDetails, :donationAmount)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':id', $payload['id']);
            $statement->bindParam(':donorName', $payload['donorName']);
            $statement->bindParam(':contactDetails', $payload['contactDetails']);
            $statement->bindParam(':donationAmount', $payload['donationAmount']);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to add new donation'];
            }

            return ['status' => 'success', 'message' => 'Donation added successfully'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
