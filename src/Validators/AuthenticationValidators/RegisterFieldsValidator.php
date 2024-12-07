<?php

namespace App\Validators\AuthenticationValidators;

use App\Validators\BaseFieldsValidator;

class RegisterFieldsValidator extends BaseFieldsValidator
{
    protected static array $expectedPayloadKeys = ['firstName', 'lastName', 'email', 'password', 'phoneNumber'];

    protected static array $payloadRules = [
        'firstName' => [
            'format' => '/^[a-zA-Z\s-]{2,50}$/',
            'message' => 'First name must only contain letters, spaces, or hyphens and be between 2 and 50 characters long.',
            'required' => true,
        ],
        'lastName' => [
            'format' => '/^[a-zA-Z\s-]{2,50}$/',
            'message' => 'Last name must only contain letters, spaces, or hyphens and be between 2 and 50 characters long.',
            'required' => true,
        ],
        'email' => [
            'format' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'message' => 'Please enter a valid email address in the format example@domain.com.',
            'required' => true,
        ],
        'password' => [
            'format' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/',
            'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and be at least 8 characters long.',
            'required' => true,
        ],
        'phoneNumber' => [
            'format' => '/^09[0-9]{9}$/',
            'message' => 'Phone number must start with 09 and contain exactly 11 digits.',
            'required' => true,
        ],
    ];

    protected static function sanitizeField(string $key, $value)
    {
        switch ($key) {
            case 'firstName':
            case 'lastName':
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case 'password':
                return $value;

            case 'phoneNumber':
                $value = preg_replace('/\D/', '', $value);
                if (strlen($value) === 11 && strpos($value, '09') === 0) {
                    return $value;
                }
                throw new \RuntimeException('Invalid phone number. Must start with 09 and be 11 digits.', 400);

            default:
                return parent::sanitizeField($key, $value);
        }
    }
}
