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

    public function getAllUsers(): array
    {
        try {
            $query = "SELECT id, firstName lastName, email, phoneNUmber, address, region, province, city, barangay, createdAt, updatedAt FROM " . self::USERS_TABLE;

            $statement = $this->pdo->prepare($query);

            $statement->execute();

            $users = $statement->fetchAll(PDO::FETCH_ASSOC);

            return ['status' => 'success', 'message' => 'Successfully Fetch all users.', 'data' => $users];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByID(string $userID): array
    {
        try {
            $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE id = :userID";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':userID', $userID, PDO::PARAM_STR);

            $statement->execute();

            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($user)) {
                return ['status' => 'failed', 'message' => 'Failed or No User Found by ID.', 'data' => []];
            }

            return ['status' => 'success', 'message' => 'Successfully Fetch user by ID.', 'data' => $user];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getUserByEmail(string $email): array
    {
        try {
            $query = "SELECT * FROM " . self::USERS_TABLE . " WHERE email = :email";
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);

            $statement->execute();

            $userData = $statement->fetch(PDO::FETCH_ASSOC);



            if (empty($userData)) {
                return ['status' => 'failed', 'message' => 'User not found'];
            }

            return ['status' => 'success', 'message' => 'User found', 'data' => $userData];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function verifyAccount(string $activationToken, string $activationCode): array
    {
        try {
            $query = 'SELECT activationCode FROM ' . self::USERS_TABLE . ' WHERE activationToken = :token';

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':token', $activationToken, PDO::PARAM_STR);

            $statement->execute();

            $response = $statement->fetch(PDO::FETCH_ASSOC);

            if ($response['activationCode'] !== $activationCode) {
                return ['status' => 'failed', 'message' => 'Invalid activation code'];
            }

            $query = 'UPDATE ' . self::USERS_TABLE . ' SET isVerified = 1, activationCode = null, activationToken = null WHERE activationToken = :token';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':token', $activationToken, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to verify account'];
            }

            return ['status' => 'success', 'message' => 'Account verified'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function resendVerificationEmail(string $email): array
    {
        try {
            $query = 'SELECT * FROM ' . self::USERS_TABLE . ' WHERE email = :email';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);

            $statement->execute();

            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($user)) {
                return ['status' => 'failed', 'message' => 'User not found'];
            }

            $activationToken = $this->generateActivationToken($user['email'], $user['id']);
            $activationCode = $this->generateActivationCode();

            $query = 'UPDATE ' . self::USERS_TABLE . ' SET activationToken = :activationToken, activationCode = :activationCode, isVerified = 0 WHERE email = :email AND isVerified = 0';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->bindValue(':activationToken', $activationToken, PDO::PARAM_STR);
            $statement->bindValue(':activationCode', $activationCode, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to resend verification email'];
            }

            return ['status' => 'success', 'message' => '
            Verification email has been sent to ' . $email, 'data' => ['activationToken' => $activationToken, 'activationCode' => $activationCode]];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function forgotPassword(string $email): array
    {
        try {
            $query = 'SELECT * FROM ' . self::USERS_TABLE . ' WHERE email = :email';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);

            $statement->execute();

            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($user)) {
                return ['status' => 'failed', 'message' => 'User not found'];
            }

            $resetToken = $this->generateActivationToken($user['email'], $user['id']);

            $query = 'UPDATE ' . self::USERS_TABLE . ' SET resetToken = :resetToken WHERE email = :email';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->bindValue(':resetToken', $resetToken, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to reset password'];
            }

            return ['status' => 'success', 'message' => 'Password reset email has been sent to ' . $email, 'data' => ['resetToken' => $resetToken]];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function resetPassword(string $resetToken, string $newPassword)
    {
        try {
            $query = 'SELECT email FROM ' . self::USERS_TABLE . ' WHERE resetToken =
            :resetToken';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':resetToken', $resetToken, PDO::PARAM_STR);

            $statement->execute();

            $response = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($response)) {
                return ['status' => 'failed', 'message' => 'Invalid reset token'];
            }

            $newPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 15]);

            $query = 'UPDATE ' . self::USERS_TABLE . ' SET password = :newPassword, resetToken = null WHERE resetToken = :resetToken';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':newPassword', $newPassword, PDO::PARAM_STR);
            $statement->bindValue(':resetToken', $resetToken, PDO::PARAM_STR);

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to reset password'];
            }

            return ['status' => 'success', 'message' => 'Password reset successfully'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function changePassword(string $email, string $oldPassword, string $newPassword): array
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

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to change password'];
            }

            return ['status' => 'success', 'message' => 'Password changed successfully'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function addNewUser(array $userData): array
    {
        try {
            $id = $userData['id'];
            $firstName = $userData['firstName'];
            $lastName = $userData['lastName'];
            $email = $userData['email'];
            $phoneNumber = $userData['phoneNumber'];
            $password = $userData['password'];
            $role = 'customer';
            $activationToken = self::generateActivationToken($email, $id);
            $activationCode = self::generateActivationCode();

            $query = "INSERT INTO " . self::USERS_TABLE . " (id, firstName, lastName, email, phoneNumber, password, address, region, province, city, barangay, validIDImageURL, selfieImageURL, role, activationToken, activationCode) VALUES (:id, :firstName, :lastName, :email, :phoneNumber, :password, '', '', '', '', '', '', '', :role, :activationToken, :activationCode)";

            $statement = $this->pdo->prepare($query);

            $bind_params = [
                ':id' => $id,
                ':firstName' => $firstName,
                ':lastName' => $lastName,
                ':email' => $email,
                ':phoneNumber' => $phoneNumber,
                ':password' => $password,
                ':role' => $role,
                ':activationToken' => $activationToken,
                ':activationCode' => $activationCode
            ];

            foreach ($bind_params as $param => $value) {
                $statement->bindValue($param, $value, PDO::PARAM_STR);
            }

            $statement->execute();

            if ($statement->rowCount() === 0) {
                return ['status' => 'failed', 'message' => 'Failed to register user'];
            }

            return ['status' => 'success', 'message' => 'User registered successfully. Please check your email to activate your account.', 'data' => ['activationToken' => $activationToken, 'activationCode' => $activationCode]];
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

            return  ['status' => 'success', 'message' => 'Update success'];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function generateActivationToken(string $email, string $id): string
    {
        $activationString = $email . $id . bin2hex(random_bytes(16));

        // Hash the string using SHA-256
        return hash('sha256', $activationString);
    }

    private function generateActivationCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
}
