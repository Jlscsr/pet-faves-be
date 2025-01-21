<?php

namespace App\Helpers;

class HeaderHelper
{


    public static function SendPreflighthHeaders()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // header("Access-Control-Allow-Origin: https://localhost:5173");
            header("Access-Control-Allow-Origin: https://pet-faves-2c3c8.web.app");

            header("Referrer-Policy: no-referrer");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
            header("Access-Control-Allow-Headers: Referrer, Content-Type, Authorization");
            header('Content-Type: application/json');
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Expose-Headers: Content-Length");

            http_response_code(200);
            exit();
        }
    }
    public static function setResponseHeaders()
    {

        // header("Access-Control-Allow-Origin: https://localhost:5173");
        header("Access-Control-Allow-Origin: https://pet-faves-2c3c8.web.app");

        header("Referrer-Policy: no-referrer");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Referrer, Content-Type, Authorization");
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Length");
    }
}
