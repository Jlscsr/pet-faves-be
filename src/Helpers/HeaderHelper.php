<?php

namespace App\Helpers;

class HeaderHelper
{
    private static $allowedOrigins = [
        "https://pet-faves-2c3c8.web.app",
        "http://localhost:5173"
    ];

    public static function SendPreflighthHeaders()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

           /*  if (in_array($origin, self::$allowedOrigins)) {
                header("Access-Control-Allow-Origin: $origin");
            } */
            header("Access-Control-Allow-Origin: https://pet-faves-2c3c8.web.app");
            header("Referrer-Policy: no-referrer");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
            header("Access-Control-Allow-Headers: Referrer, Content-Type, Authorization");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Expose-Headers: Content-Length");

            http_response_code(200);
            exit();
        }
    }
    public static function setResponseHeaders()
    {

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        /* if (in_array($origin, self::$allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } */

        header("Access-Control-Allow-Origin: https://pet-faves-2c3c8.web.app");
        header("Referrer-Policy: no-referrer");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Referrer, Content-Type, Authorization");
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Length");
    }
}
