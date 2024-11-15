<?php

use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

require_once dirname(__DIR__) . '/config/load_env.php';


function db_connect()
{
    HeaderHelper::setResponseHeaders();

    try {
        $environment = $_ENV['ENVIRONMENT'];

        if ($environment == "production") {
            $url = parse_url(getenv("JAWSDB_URL"));
            $host = $url["host"];
            $username = $url["user"];
            $Password = $url["pass"];
            $database = ltrim($url['path'], '/'); // Removes the leading slash from the database name
        } else {
            $host = $_ENV['DEV_DB_HOST'];
            $username = $_ENV['DEV_DB_USERNAME'];
            $Password = $_ENV['DEV_DB_PASSWORD'];
            $database = $_ENV['DEV_DB_NAME'];
        }

        $dsn = "mysql:host=$host;dbname=$database";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new \PDO($dsn, $username, $Password, $options);

        if ($pdo) {
            return $pdo;
        }
    } catch (PDOException $e) {
        ResponseHelper::sendDatabaseErrorResponse('Server Connection Failed. Please try again later.');
        die();
    }
}
