<?php

namespace App\Validators\PetsValidators;

use App\Validators\BaseFieldsValidator;

class AddNewPetsFieldValidator extends BaseFieldsValidator
{
    protected static array $expectedPayloadKeys = [
        'petName',
        'petAge',
        'petAgeCategory',
        'petGender',
        'petType',
        'petBreed',
        'petColor',
        'petVacHistory',
        'petHistory',
        'petPhotoURL',
        'adoptionStatus',
    ];

    protected static array $payloadRules = [
        'petName' => [
            'format' => '/^[a-zA-Z\s\-\.]+$/', // Letters, spaces, and hyphens
            'message' => 'PetName: Pet Name can only contain letters, spaces, or hyphens (e.g., "Buddy" or "Buddy-Smith").',
            'required' => true,
        ],
        'petAge' => [
            'format' => '/^(\d+(-\d+)?)\s*(week|month|year)s?\s*old$/i',
            'message' => 'PetAge: Pet age must be a number, a range, or a number followed by "week(s) old", "month(s) old", or "year(s) old" (e.g., "3", "3-4", "3 weeks old", "1 month old", "4 years old").',
            'required' => true,
        ],

        'petAgeCategory' => [
            'format' => '/^(baby|young|adult)$/i', // Specific keywords
            'message' => 'PetAgeCategory: Pet Age Category must be one of the following: "baby", "young", or "adult".',
            'required' => true,
        ],
        'petGender' => [
            'format' => '/^(male|female)$/i', // Male or female
            'message' => 'PetGender: Pet Gender must be either "male" or "female".',
            'required' => true,
        ],
        'petType' => [
            'format' => '/^[a-zA-Z\s\-]+$/', // Letters, spaces, and hyphens
            'message' => 'PetType: Pet Type can only contain letters, spaces, or hyphens (e.g., "Dog" or "Golden-Retriever").',
            'required' => true,
        ],
        'petBreed' => [
            'format' => '/^[a-zA-Z\s\-\/]+$/', // Letters, spaces, and hyphens
            'message' => 'PetBreed: Pet Breed can only contain letters, spaces, or hyphens (e.g., "Golden Retriever").',
            'required' => true,
        ],
        'petColor' => [
            'format' => '/^(white|black|brown|mixed)$/i', // Specific colors
            'message' => 'PetColor: Pet Color must be one of the following: "white", "black", "brown", or "mixed".',
            'required' => true,
        ],
        'petVacHistory' => [
            'format' => '',
            'message' => '',
            'required' => true,
        ],
        'petHistory' => [
            'format' => '/^[a-zA-Z0-9\s,.\-;\'()\/]+$/', // Added apostrophes and parentheses
            'message' => 'PetHistory: Pet History can only contain letters, numbers, spaces, commas, dots, hyphens, semicolons, apostrophes, and parentheses.',
            'required' => false,
        ],
        'petPhotoURL' => [
            'format' => '',
            'message' => '',
            'required' => true,
        ],
        'adoptionStatus' => [
            'format' => '/^(available|pending|adopted|not available|return)$/i', // Specific statuses
            'message' => 'AdoptionStatus: Adoption Status must be one of the following: "available", "pending", "adopted", "not available", or "return".',
            'required' => true,
        ],

    ];

    protected static function sanitizeField(string $key, $value)
    {
        switch ($key) {
            case 'petName':
                return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);

            case 'petAgeCategory':
                return trim($value);

            case 'petGender':
                return trim($value);

            case 'petType':
                return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);

            case 'petBreed':
                return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);

            case 'petColor':
                return trim($value);

            case 'petHistory':
                return filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);

            case 'adoptionStatus':
                return trim($value);
            default:
                return parent::sanitizeField($key, $value);
        }
    }
}
