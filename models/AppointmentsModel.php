<?php

namespace Models;

use RuntimeException;

use Models\RequestsModel;

use PDO;
use PDOException;

class AppointmentsModel
{
    private $pdo;

    // Constants
    private const APPOINTMENTS_TABLE = 'appointments_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
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

    public function getAppointmentByID(int $appointmentID)
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

    public function getAppointmentByRequestID(int $requestID)
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
        $query = "INSERT INTO " . self::APPOINTMENTS_TABLE . " (userOwnerID, requestID, userID, petID,  appointmentDate, appointmentTime) VALUES (:userOwnerID, :requestID, :userID, :petID, :date, :time)";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userOwnerID', $payload['userOwnerID'], PDO::PARAM_INT);
        $statement->bindValue(':requestID', $payload['requestID'], PDO::PARAM_INT);
        $statement->bindValue(':userID', $payload['userID'], PDO::PARAM_INT);
        $statement->bindValue(':petID', $payload['petID'], PDO::PARAM_INT);
        $statement->bindValue(':date', $payload['appointmentDate'], PDO::PARAM_STR);
        $statement->bindValue(':time', $payload['appointmentTime'], PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function deleteRequestAppointmentByID(int $appointmentID)
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
