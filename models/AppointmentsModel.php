<?php

namespace Models;

use InvalidArgumentException;
use RuntimeException;

use Models\RequestsModel;

use PDO;

class AppointmentsModel
{
    private $pdo;
    private $requestModel;

    // Constants
    private const APPOINTMENTS_TABLE = 'appointments_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->requestModel = new RequestsModel($pdo);
    }

    public function getRequestAppointmentByRequestID($id)
    {

        if (!$id) {
            throw new RuntimeException('Invalid request ID');
            return;
        }

        $query = "SELECT * FROM " . self::APPOINTMENTS_TABLE . " WHERE requestID = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewRequestAppointment($payload)
    {

        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        $query = "INSERT INTO " . self::APPOINTMENTS_TABLE . " (userOwnerID, requestID, userID, petID,  appointmentDate, appointmentTime, appointmentStatus) VALUES (:userOwnerID, :requestID, :userID, :petID, :date, :time, :status)";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':userOwnerID', $payload['userOwnerID'], PDO::PARAM_INT);
        $statement->bindValue(':requestID', $payload['requestID'], PDO::PARAM_INT);
        $statement->bindValue(':userID', $payload['userID'], PDO::PARAM_INT);
        $statement->bindValue(':petID', $payload['petID'], PDO::PARAM_INT);
        $statement->bindValue(':date', $payload['date'], PDO::PARAM_STR);
        $statement->bindValue(':time', $payload['time'], PDO::PARAM_STR);
        $statement->bindValue(':status', $payload['status'], PDO::PARAM_STR);

        try {
            $statement->execute();

            if ($statement->rowCount() > 0) {
                return $this->requestModel->updateRequestStatus($payload['requestID'], ['status' => 'on going process']);
            }
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
