<?php

namespace App\Validators;

use App\Interfaces\ValidateInputFieldsInterface;

use RuntimeException;

abstract class BaseFieldsValidator implements ValidateInputFieldsInterface
{
    protected static array $expectedPayloadKeys = [];
    protected static array $payloadRules = [];

    public static function validateAndSanitizeFields(array $fields): array
    {
        $fields = self::validatePayloadKeys($fields);
        $fields = self::validateRequiredFields($fields);
        $fields = self::validateFieldsPattern($fields);
        return self::sanitizeFields($fields);
    }

    public static function validatePayloadKeys(array $fields): array
    {
        foreach (static::$expectedPayloadKeys as $key) {
            if (!array_key_exists($key, $fields)) {
                throw new RuntimeException($key . ' is required', 400);
            }
        }
        return $fields;
    }

    public static function validateRequiredFields(array $fields): array
    {
        foreach (static::$payloadRules as $key => $value) {
            if (empty($fields[$key]) && $value['required']) {
                throw new RuntimeException($key . ' is required and cannot be empty', 400);
            }
        }
        return $fields;
    }

    public static function validateFieldsPattern(array $fields): array
    {
        foreach (static::$payloadRules as $key => $value) {
            if (empty($value['format'])) {
                continue;
            }

            if (!preg_match($value['format'], $fields[$key])) {
                throw new RuntimeException($value['message'], 400);
            }
        }
        return $fields;
    }

    public static function sanitizeFields(array $fields): array
    {
        foreach ($fields as $key => $value) {
            $fields[$key] = static::sanitizeField($key, $value);
        }
        return $fields;
    }

    protected static function sanitizeField(string $key, $value)
    {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
