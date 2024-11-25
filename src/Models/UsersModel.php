<?php

namespace App\Models;

use RuntimeException;

use PDO;
use PDOException;

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
        try {
            $query = "SELECT id, firstName lastName, email, phoneNUmber, address, region, province, city, barangay, createdAt, updatedAt FROM " . self::USERS_TABLE;

            $statement = $this->pdo->prepare($query);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByID(string $userID)
    {
        try {
            $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE id = :userID";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByEmail(string $email)
    {
        try {
            $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE email = :email";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);

            $statement->execute();

            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function changePassword(string $email, string $oldPassword, string $newPassword)
    {
        try {
            $query = 'SELECT password FROM ' . self::USERS_TABLE . ' WHERE email = :email';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);

            $statement->execute();

            $response = $statement->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($oldPassword, $response['password'])) {
                throw new RuntimeException('Old password is incorrect');
            }

            $newPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 15]);

            $query = 'UPDATE ' . self::USERS_TABLE . ' SET password = :newPassword WHERE email = :email';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->bindValue(':newPassword', $newPassword, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewUser(array $userData)
    {
        try {
            // This checks if the email is already in use
            $response = $this->getUserByEmail($userData['email']);

            if ($response) {
                throw new RuntimeException('Email is already in use');
            }

            $id = $userData['id'];
            $firstName = $userData['firstName'];
            $lastName = $userData['lastName'];
            $email = $userData['email'];
            $phoneNumber = $userData['phoneNumber'];
            $password = $userData['password'];
            $role = 'customer';


            $query = "INSERT INTO " . self::USERS_TABLE . " (id, firstName, lastName, email, phoneNumber, password, address, region, province, city, barangay, validIDImageURL, selfieImageURL, role) VALUES (:id, :firstName, :lastName, :email, :phoneNumber, :password, '', '', '', '', '', '', '', :role)";

            $statement = $this->pdo->prepare($query);

            $bind_params = [
                ':id' => $id,
                ':firstName' => $firstName,
                ':lastName' => $lastName,
                ':email' => $email,
                ':phoneNumber' => $phoneNumber,
                ':password' => $password,
                ':role' => $role
            ];

            foreach ($bind_params as $param => $value) {
                $statement->bindValue($param, $value, PDO::PARAM_STR);
            }

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function updateUserData(string $userID, array $payload)
    {
        try {
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

            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
