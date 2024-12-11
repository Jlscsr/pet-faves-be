<?php

namespace App\Validators\PetsValidators;

use App\Validators\BaseFieldsValidator;

class AddNewPetsFieldValidator extends BaseFieldsValidator
{
    protected static array $expectedPayloadKeys = [
        'userOwnerID',
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
        'approvalStatus',
        'postType',
    ];

    protected static array $payloadRules = [
        'userOwnerID' => [
            'format' => '', // Only numeric values
            'message' => '',
            'required' => false,
        ],
        'petName' => [
            'format' => '/^[a-zA-Z\s\-]+$/', // Letters, spaces, and hyphens
            'message' => 'PetName: Pet Name can only contain letters, spaces, or hyphens (e.g., "Buddy" or "Buddy-Smith").',
            'required' => true,
        ],
        'petAge' => [
            'format' => '',
            'message' => '',
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
            'format' => '/^[a-zA-Z\s\-]+$/', // Letters, spaces, and hyphens
            'message' => 'PetBreed: Pet Breed can only contain letters, spaces, or hyphens (e.g., "Golden Retriever").',
            'required' => true,
        ],
        'petColor' => [
            'format' => '/^(white|black|brown|mixed)$/i', // Specific colors
            'message' => 'PetColor: Pet Color must be one of the following: "white", "black", "brown", or "mixed".',
            'required' => true,
        ],
        'petVacHistory' => [
            'format' => '/^\d{4}-\d{2}-\d{2}$/', // YYYY-MM-DD date format
            'message' => 'PetVacHistory: Pet Vaccination History must be a valid date in YYYY-MM-DD format (e.g., "2023-11-01").',
            'required' => true,
        ],
        'petHistory' => [
        'format' => '/^[a-zA-Z0-9\s,.\-;]+$/', // Letters, numbers, spaces, commas, dots, hyphens, and semicolons
        'message' => 'PetHistory: Pet History can only contain letters, numbers, spaces, commas, dots, hyphens, and semicolons.',
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
        'approvalStatus' => [
            'format' => '/^(pending|approved)$/i', // Specific statuses
            'message' => 'ApprovalStatus: Approval Status must be either "pending" or "approved".',
            'required' => true,
        ],
        'postType' => [
            'format' => '/^post\-adoption$/i', // Exact keyword
            'message' => 'PostType: Post Type must be "post-adoption".',
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

            case 'approvalStatus':
                return trim($value);

            case 'postType':
                return trim($value);

            default:
                return parent::sanitizeField($key, $value);
        }
    }
}
