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
            $query = "INSERT INTO " . self::DONATIONS_TABLE . " (id, referenceNumber, donationAmount) VALUES (:id, :referenceNumber, :donationAmount)";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':id', $payload['id']);
            $statement->bindParam(':referenceNumber', $payload['referenceNumber']);
            $statement->bindParam(':donationAmount', $payload['donationAmount']);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
