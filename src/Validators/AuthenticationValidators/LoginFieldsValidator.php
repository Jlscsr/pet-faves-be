<?php

namespace App\Validators\AuthenticationValidators;

use App\Validators\BaseFieldsValidator;

class LoginFieldsValidator extends BaseFieldsValidator
{
    protected static array $expectedPayloadKeys = ['email', 'password'];
    protected static array $payloadRules = [
        'email' => [
            'format' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'message' => 'Email: Please enter a valid email address in the format example@domain.com',
            'required' => true
        ],
        'password' => [
            'format' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/',
            'message' => 'Password: Password must contain at least one uppercase letter, one lowercase letter, one number, and be at least 8 characters long',
            'required' => true
        ],
    ];

    protected static function sanitizeField(string $key, $value)
    {
        switch ($key) {
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'password':
                return $value;
            default:
                return parent::sanitizeField($key, $value);
        }
    }
}
