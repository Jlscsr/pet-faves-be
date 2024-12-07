<?php

namespace App\Models;

use RuntimeException;
use PDO;
use PDOException;

use App\Models\UsersModel;

class TokensModel
{
    private $pdo;
    private $usersModel;

    // Constants

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->usersModel = new UsersModel($pdo);
    }

    public function validateResetToken(string $resetToken)
    {

        try {
            $query = "SELECT resetToken FROM users_tb WHERE resetToken = :resetToken";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':resetToken', $resetToken, PDO::PARAM_STR);

            $statement->execute();

            $response = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($response)) {
                return [
                    'status' => 'failed',
                    'message' => 'Invalid reset token provided.'
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Valid reset token provided.'
            ];
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
