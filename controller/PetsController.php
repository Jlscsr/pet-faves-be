<?php

use Helpers\ResponseHelper;

use Validators\HTTPRequestValidator;

use Models\PetsModel;

class PetsController
{
    private $petsModel;
    private $acceptableParamsKeys = ['id', 'status', 'label', 'petType'];
    private $expectedPostPayloadKeys = ['userID', 'petName', 'petAge', 'petAgeCategory', 'petGender', 'petType', 'petBreed', 'petVacHistory', 'petHistory', 'petPhotoURL', 'adoptionStatus', 'approvalStatus', 'postType'];

    public function __construct($pdo)
    {
        $this->petsModel = new PetsModel($pdo);
    }

    public function getAllPets()
    {
        try {
            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $pets = $this->petsModel->getAllPets($limit, $offset);

            if (!$pets) {
                return ResponseHelper::sendSuccessResponse([], 'No pets found');
            }

            return ResponseHelper::sendSuccessResponse($pets, 'Pets found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsByAdoptionStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $limit = (int) $_GET['limit'] ?? 0;
            $offset = (int) $_GET['offset'] ?? 0;

            $status = $params['status'];

            $pets = $this->petsModel->getAllPetsByAdoptionStatus($status, $limit, $offset);

            if (!$pets) {
                return ResponseHelper::sendSuccessResponse([], 'No pets found');
            }

            return ResponseHelper::sendSuccessResponse($pets, 'Pets found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getPetByID(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $petID = (int) $params['id'];

            $pet = $this->petsModel->getPetByID($petID);

            if (!$pet) {
                return ResponseHelper::sendSuccessResponse([], 'No pet found');
            }

            return ResponseHelper::sendSuccessResponse($pet, 'Pet found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsByLabel(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParams($this->acceptableParamsKeys, $params);

            $label = $params['label'];
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $pets = $this->petsModel->getAllPetsByLabel($label, $limit, $offset);

            if (!$pets) {
                return ResponseHelper::sendSuccessResponse([], 'No pets found');
            }

            return ResponseHelper::sendSuccessResponse($pets, 'Pets found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetTypes()
    {
        try {
            $types = $this->petsModel->getAllPetTypes();

            if (!$types) {
                return ResponseHelper::sendSuccessResponse([], 'No types found');
            }

            return ResponseHelper::sendSuccessResponse($types, 'Types found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetBreedsByType(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParams($this->acceptableParamsKeys, $params);

            $petType = $params['petType'];

            $breeds = $this->petsModel->getAllPetBreedsByType($petType);

            if (!$breeds) {
                return ResponseHelper::sendSuccessResponse([], 'No breeds found');
            }

            return ResponseHelper::sendSuccessResponse($breeds, 'Breeds found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsAgeCategories()
    {
        try {
            $ageCategories = $this->petsModel->getAllPetsAgeCategories();

            if (!$ageCategories) {
                return  ResponseHelper::sendSuccessResponse([], 'No age categories found');
            }

            return ResponseHelper::sendSuccessResponse($ageCategories, 'Age categories found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }


    public function addNewPet(array $payload)
    {
        try {
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $pet = $this->petsModel->addNewPet($payload);

            if (!$pet) {
                return ResponseHelper::sendErrorResponse("Failed to add pet", 400);
            }

            return ResponseHelper::sendSuccessResponse([], "Pet added successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
