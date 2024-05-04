<?php

class LoginValidator
{

    public static function validatePayload($paylad): string
    {
        $requiredFields = ['email', 'password'];

        foreach ($requiredFields as $key => $value) {
            if (!in_array($key, array_keys($paylad))) {
                return $key . ' field is invalid';
            }
        }
    }
}
