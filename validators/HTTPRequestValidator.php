<?php

namespace Validators;

use Interfaces\ValidatorInterface;

use Helpers\ResponseHelper;

class HTTPRequestValidator implements ValidatorInterface
{

    public static function validateGETParameter(array $acceptableKeys, array $params)
    {
        if (empty($params)) {
            ResponseHelper::sendErrorResponse("GET: Parameter array is empty", 400);
            exit;
        }

        foreach (array_keys($params) as $key) {
            if (!in_array($key, $acceptableKeys)) {
                ResponseHelper::sendErrorResponse("GET: Unexpected parameter provided: $key", 400);
                exit;
            }

            if (empty($params[$key])) {
                ResponseHelper::sendErrorResponse("GET: Parameter $key cannot be empty", 400);
                exit;
            }
        }
    }

    public static function validatePOSTPayload(array $expectedPayloadKeys, array $payload)
    {

        if (empty($payload)) {
            ResponseHelper::sendErrorResponse("POST: Payload is empty", 400);
            exit;
        }

        foreach ($expectedPayloadKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                ResponseHelper::sendErrorResponse("POST: Missing required parameter : $key", 400);
                exit;
            }
        }
    }

    public static function validatePUTPayload(array $acceptableKeys, array $expectedPayloadKeys, array $params, array $payload)
    {
        if (empty($params)) {
            ResponseHelper::sendErrorResponse("PUT: Parameter array is empty", 400);
            exit;
        }

        if (empty($payload)) {
            return esponseHelper::sendErrorResponse("PUT: Payload is empty", 400);
            exit;
        }

        foreach (array_keys($params) as $key) {
            if (!in_array($key, $acceptableKeys)) {
                ResponseHelper::sendErrorResponse("PUT: Unexpected parameter provided: $key", 400);
                exit;
            }

            if (empty($params[$key])) {
                ResponseHelper::sendErrorResponse("PUT: Parameter $key cannot be empty", 400);
                exit;
            }
        }

        foreach ($expectedPayloadKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                ResponseHelper::sendErrorResponse("PUT: Missing required parameter : $key", 400);
                exit;
            }
        }
    }

    public static function validateDELETEParameter(array $acceptableKeys, array $params)
    {
        if (empty($params)) {
            ResponseHelper::sendErrorResponse("DEL: Parameter array is empty", 400);
            exit;
        }

        foreach (array_keys($params) as $key) {
            if (!in_array($key, $acceptableKeys)) {
                ResponseHelper::sendErrorResponse("DEL: Unexpected parameter provided: $key", 400);
                exit;
            }

            if (empty($params[$key])) {
                ResponseHelper::sendErrorResponse("DEL: Parameter $key cannot be empty", 400);
                exit;
            }
        }
    }
}
