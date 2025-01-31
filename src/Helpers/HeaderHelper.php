<?php

namespace App\Helpers;

class HeaderHelper
{

    private const ALLOWED_ORIGINS = [
        'https://localhost:5173',
        'https://pet-faves-2c3c8.web.app'
    ];


    public static function SendPreflighthHeaders()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

            if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], self::ALLOWED_ORIGINS)) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            }

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
        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], self::ALLOWED_ORIGINS)) {
            header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        }

        header("Referrer-Policy: no-referrer");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Referrer, Content-Type, Authorization");
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Length");
    }
}
