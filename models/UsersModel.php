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
        $query = "SELECT id, firstName, middleName lastName, email, phoneNUmber, address, region, province, city, barangay, created_at, updated_at FROM " . self::USERS_TABLE;

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
        $middleName = $userData['middleName'];
        $lastName = $userData['lastName'];
        $email = $userData['email'];
        $phoneNumber = $userData['phoneNumber'];
        $password = $userData['password'];
        $address = $userData['address'];
        $region = $userData['region'];
        $province = $userData['province'];
        $city = $userData['city'];
        $barangay = $userData['barangay'];


        $query = "INSERT INTO " . self::USERS_TABLE . " (firstName, middleName, lastName, email, phoneNumber, password, address, region, province, city, barangay) VALUES (:firstName, :middleName, :lastName, :email, :phoneNumber, :password, :region, :address, :province, :city, :barangay)";

        $statement = $this->pdo->prepare($query);

        $bind_params = [
            ':firstName' => $firstName,
            ':middleName' => $middleName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':phoneNumber' => $phoneNumber,
            ':password' => $password,
            ':address' => $address,
            ':region' => $region,
            ':province' => $province,
            ':city' => $city,
            ':barangay' => $barangay
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
}
