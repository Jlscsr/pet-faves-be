<?php

namespace App\Interfaces;

interface ValidatorInterface
{
    public static function validateGETParameter(array $acceptableKeys, array $parameter);
    public static function validatePOSTPayload(array $expectedPayloadKeys, array $payload);
    public static function validatePUTPayload(array $expectedParamsKeys, array $expectedPayloadKeys, array $params, array $payload);
    public static function validateDELETEParameter(array $acceptableKeys, array $parameter);
}
