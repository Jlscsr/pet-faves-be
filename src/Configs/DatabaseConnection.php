<?php

namespace App\Configs;

use App\Configs\EnvironmentLoader;

use PDO;
use PDOException;

class DatabaseConnection
{
    public static function connect(): ?PDO
    {

        print_r("Hello, World!3");

        try {
            $environment = parse_url(getenv('ENVIRONMENT'));
            $environment = $environment['path'];

            if ($environment == "production") {
                // Use the Heroku JawsDB URL directly
                $url = parse_url(getenv('JAWSDB_URL'));

                $host = $url['host'];
                $username = $url['user'];
                $password = $url['pass'];
                $database = ltrim($url['path'], '/'); // Removes the leading slash from the database name
            } else {
                $host = $_ENV['DEV_DB_HOST'];
                $username = $_ENV['DEV_DB_USERNAME'];
                $password = $_ENV['DEV_DB_PASSWORD'];
                $database = $_ENV['DEV_DB_NAME'];
            }

            $dsn = "mysql:host=$host;dbname=$database";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die("Database Connection failed: " . $e->getMessage());
        }
    }
}
