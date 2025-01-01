<?php

namespace App\Models;

use RuntimeException;

use App\Models\RequestsModel;

use PDO;
use PDOException;

class AppointmentsModel
{
    private $pdo;
    private $requestsModel;

    // Constants
    private const APPOINTMENTS_TABLE = 'appointments_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->requestsModel = new RequestsModel($pdo);
    }

    public function getAllAppointments()
    {
        try {

            $query = "SELECT * FROM " . self::APPOINTMENTS_TABLE;
            $statement = $this->pdo->prepare($query);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAppointmentByID(string $appointmentID)
    {
        try {
            $query = "SELECT * FROM " . self::APPOINTMENTS_TABLE . " WHERE id = :appointmentID";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':appointmentID', $appointmentID, PDO::PARAM_INT);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAppointmentByRequestID(string $requestID)
    {

        $query = "SELECT * FROM " . self::APPOINTMENTS_TABLE . " WHERE requestID = :requestID";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':requestID', $requestID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewAppointment(array $payload)
    {
        $existingRequestID = $this->getAppointmentByRequestID($payload['requestID']);

        if ($existingRequestID) {
            return $this->updateAppointment($existingRequestID['id'], $payload);
        }

        $query = "INSERT INTO " . self::APPOINTMENTS_TABLE . " (id, requestID, userID, petID,  appointmentDate, appointmentTime) VALUES (:id, :requestID, :userID, :petID, :date, :time)";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $payload['id'], PDO::PARAM_INT);
        $statement->bindValue(':requestID', $payload['requestID'], PDO::PARAM_INT);
        $statement->bindValue(':userID', $payload['userID'], PDO::PARAM_INT);
        $statement->bindValue(':petID', $payload['petID'], PDO::PARAM_INT);
        $statement->bindValue(':date', $payload['appointmentDate'], PDO::PARAM_STR);
        $statement->bindValue(':time', $payload['appointmentTime'], PDO::PARAM_STR);

        try {
            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to add new appointment'];
            }

            return ['status' => 'success', 'message' => 'Successfully added new appointment'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateAppointment(string $appointmentID, array $payload)
    {
        $query = "UPDATE " . self::APPOINTMENTS_TABLE . " SET userOwnerID = :userOwnerID, requestID = :requestID, userID = :userID, petID = :petID, appointmentDate = :date, appointmentTime = :time WHERE id = :appointmentID";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':appointmentID', $appointmentID, PDO::PARAM_STR);
        $statement->bindValue(':userOwnerID', $payload['userOwnerID'], PDO::PARAM_STR);
        $statement->bindValue(':requestID', $payload['requestID'], PDO::PARAM_STR);
        $statement->bindValue(':userID', $payload['userID'], PDO::PARAM_STR);
        $statement->bindValue(':petID', $payload['petID'], PDO::PARAM_STR);
        $statement->bindValue(':date', $payload['appointmentDate'], PDO::PARAM_STR);
        $statement->bindValue(':time', $payload['appointmentTime'], PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function deleteAppointmentByID(string $appointmentID)
    {
        $query = "DELETE FROM " . self::APPOINTMENTS_TABLE . " WHERE id = :appointmentID";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':appointmentID', $appointmentID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
