<?php

namespace Validators\Controllers;

use Interfaces\ValidatorInterface;

use RuntimeException;
use InvalidArgumentException;

class PetFeedsValidator implements ValidatorInterface
{
    public static function validateGETParameter(array $parameter)
    {
        $validFields = ['id', 'status'];

        if (!is_array($parameter) && empty($parameter)) {
            throw new InvalidArgumentException("Invalid parameter or parameter is empty");
        }

        foreach ($validFields as $field) {
            if (array_key_exists($field, $parameter)) {
                break;
            }
        }
    }

    public static function validatePOSTPayload(array $payload)
    {
        $requiredFields = ["userID", "petName", "petType", "petBreed", "petAge", "petGender", "petCaption", "petPhotoURL", "approvalStatus"];


        // Check if payload is an array or empty
        if (!is_array($payload) && empty($payload)) {
            throw new InvalidArgumentException("Invalid payload or payload is empty");
        }

        // Check if all required fields are in the payload
        foreach ($requiredFields as $field) {

            // Check if the field in the paylaod is not in the required fields
            if (!in_array($field, array_keys($payload))) {
                throw new RuntimeException($field . " is invalid");
            }

            // Check if the field in the payload is required and is not set
            if (!isset($payload[$field])) {
                throw new RuntimeException($field . " is required");
            }
        }
    }

    public static function validatePUTPayload(array $payload)
    {
        //
    }

    public static function validateDELETEParameter(array $parameter)
    {
        //
    }
}
