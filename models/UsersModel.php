<?php

namespace Models;

use InvalidArgumentException;
use RuntimeException;

use PDO;

class UsersModel
{
    private $pdo;

    // Constants
    private const USERS_TABLE = 'users_tb';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers()
    {
        $query = "SELECT id, firstName lastName, email, phoneNUmber, address, region, province, city, barangay, created_at, updated_at FROM " . self::USERS_TABLE;

        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute();
            $customers = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($customers)) {
                throw new RuntimeException('No users found');
            }

            return $customers;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByID($customerID)
    {
        if (!$customerID) {
            throw new InvalidArgumentException('Invalid customer ID');
        }

        $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $customerID, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByEmail($email)
    {
        if (!is_string($email)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE email = :email";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);

        try {
            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewUser($userData)
    {
        if (!is_array($userData) && empty($userData)) {
            throw new InvalidArgumentException('Invalid or missing user data');
        }

        // This checks if the email is already in use
        $response = $this->getUserByEmail($userData['email']);

        if ($response) {
            return "Email already in use";
        }

        $firstName = $userData['firstName'];
        $lastName = $userData['lastName'];
        $email = $userData['email'];
        $password = $userData['password'];
        $role = 'customer';


        $query = "INSERT INTO " . self::USERS_TABLE . " (firstName, middleName, lastName, email, phoneNumber, gender, password, address, region, province, city, barangay, role) VALUES (:firstName, '', :lastName, :email, '', '', :password, '', '', '', '', '', :role)";

        $statement = $this->pdo->prepare($query);

        $bind_params = [
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role
        ];

        foreach ($bind_params as $param => $value) {
            $statement->bindValue($param, $value, PDO::PARAM_STR);
        }

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateUserData($userID, $payload)
    {
        $query = "UPDATE " . self::USERS_TABLE . " SET ";

        foreach ($payload as $key => $value) {
            if ($key === array_key_last($payload)) {
                $query .= $key . " = :" . $key . " WHERE id = :userID";
            } else {
                $query .= $key . " = :" . $key . ", ";
            }
        }

        $statement = $this->pdo->prepare($query);

        foreach ($payload as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);

        try {
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
