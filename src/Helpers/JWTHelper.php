<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use RuntimeException;

// use App\Configs\EnvironmentLoader;


class JWTHelper
{
    private $secret_key;
    private $hash_algorithm;

    public function __construct()
    {
        // EnvironmentLoader::load();
        $this->secret_key = getenv('JWT_SECRET_KEY') ?: $_ENV['JWT_SECRET_KEY'];
        $this->hash_algorithm = getenv('JWT_HASH_ALGORITHM') ?: $_ENV['JWT_HASH_ALGORITHM'];
    }

    /**
     * Encodes the given data into a JSON Web Token (JWT) using the specified secret key and hash algorithm.
     *
     * @param mixed $data The data to be encoded into a JWT.
     * @return string The encoded JWT.
     */
    public function encodeDataToJWT($data)
    {
        $token = JWT::encode($data, $this->secret_key, $this->hash_algorithm);
        return $token;
    }

    /**
     * Decodes the given JWT token and returns the decoded data.
     *
     * @param string $token The JWT token to be decoded.
     * @throws Exception If there is an error decoding the token.
     * @return mixed The decoded data from the JWT token.
     */
    public function decodeJWTData(string $token)
    {
        try {
            $data = JWT::decode($token, new Key($this->secret_key, $this->hash_algorithm));
            return $data;
        } catch (\Exception $e) {
            ResponseHelper::sendUnauthorizedResponse('Invalid Token Signature');
            exit;
        }
    }

    /**
     * Validates the authenticity and expiration of the JWT token.
     *
     * @param mixed $token The JWT token to be validated.
     * @throws \Firebase\JWT\SignatureInvalidException If the token signature is invalid.
     * @return bool Returns true if the token is valid, false otherwise.
     */
    public function validateToken(string $token): array | bool
    {
        try {
            $data = $this->decodeJWTData($token);
            $expiry_date = $data->expiry_date;

            if ($expiry_date < time()) {
                return [
                    'status' => 'failed',
                    'message' => 'Token Expired. Please Login again.'
                ];
            }

            return true;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new RuntimeException('Invalid Token Signature');
        }
    }
}
