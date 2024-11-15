<?php

namespace App\Configs;

use Dotenv\Dotenv;

class EnvironmentLoader
{
    public static function load(): void
    {
        require_once dirname(dirname(path: __DIR__)) . '/vendor/autoload.php';

        $dotenv = Dotenv::createImmutable(dirname(dirname(__DIR__)));
        $dotenv->load();
    }
}
