<?php

namespace App\Interfaces;

interface ValidateInputFieldsInterface
{
    public static function validateAndSanitizeFields(array $fields);
    public static function validatePayloadKeys(array $fields);
    public static  function validateRequiredFields(array $fields);
    public static  function validateFieldsPattern(array $fields);
    public static function sanitizeFields(array $fields);
}
