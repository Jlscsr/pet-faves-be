<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Validators\PetsValidators\AddNewPetsFieldValidator;

use App\Models\PetsModel;

use RuntimeException;

class PetsController
{
    private $petsModel;
    private $acceptableParamsKeys = ['id', 'userID', 'approvalStatus', 'status', 'label', 'petType', 'adoptionStatus'];

    public function __construct($pdo)
    {
        $this->petsModel = new PetsModel($pdo);
    }

    public function getAllPets()
    {
        try {

            $pets = $this->petsModel->getAllPets();

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

            $status = $params['status'];

            $pets = $this->petsModel->getAllPetsByAdoptionStatus($status);

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

            $petID = $params['id'];

            $pet = $this->petsModel->getPetByID($petID);

            if (!$pet) {
                return ResponseHelper::sendSuccessResponse([], 'No pet found');
            }

            return ResponseHelper::sendSuccessResponse($pet, 'Pet found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getPetByIDAndAdoptionStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $id = $params['id'];
            $adoptionStatus = $params['adoptionStatus'];

            $pet = $this->petsModel->getPetByIDAndAdoptionStatus($id, $adoptionStatus);

            if (!$pet) {
                return ResponseHelper::sendSuccessResponse([], 'No pet found');
            }

            return ResponseHelper::sendSuccessResponse($pet, 'Pet found');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsByUserIDAndApprovalStatus(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $userID = $params['userID'];
            $approvalStatus = $params['approvalStatus'];

            $pet = $this->petsModel->getAllPetsByUserIDAndApprovalStatus($userID, $approvalStatus);

            if (!$pet) {
                return ResponseHelper::sendSuccessResponse([], 'No pet found');
            }

            return ResponseHelper::sendSuccessResponse($pet, 'Pet found');
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
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

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
            $sanitizedPayload = AddNewPetsFieldValidator::validateAndSanitizeFields($payload);

            $uuid = Uuid::uuid7()->toString();
            $sanitizedPayload['id'] = $uuid;

            $response = $this->petsModel->addNewPet($sanitizedPayload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse($response['message'], 400);
            }

            return ResponseHelper::sendSuccessResponse($response['data'], $response['message']);
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePetData(array $params, array $payload)
    {
        try {
            $sanitizedPayload = AddNewPetsFieldValidator::validateAndSanitizeFields($payload);

            $petID = $params['id'];

            $response = $this->petsModel->updatePetData($petID, $sanitizedPayload);

            if ($response['status'] === 'failed') {
                return ResponseHelper::sendErrorResponse("Failed to update pet data");
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully updated pet data.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePetAdoptionStatus($params, $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['adoptionStatus'], $params, $payload);

            $petID = $params['id'];
            $adoptionStatus =  $this->petsModel::ADOPTION_STATUS_MAP[$payload['adoptionStatus']];

            $response = $this->petsModel->updatePetAdoptionStatus($petID, $adoptionStatus);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to update post approval status", 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully updated post approval status.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePetApprovalStatus(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, ['status'], $params, $payload);

            $postID = $params['id'];
            $approvalStatus = $payload['status'];
            $reason = $payload['reason'] ?? 'n/a';

            $response = $this->petsModel->updatePetApprovalStatus($postID, $approvalStatus, $reason);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to update post approval status", 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Successfully updated post approval status.');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function deletePet(array $params)
    {
        try {
            HTTPRequestValidator::validateGETParameter($this->acceptableParamsKeys, $params);

            $petID = $params['id'];

            $response = $this->petsModel->deletePet($petID);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to delete pet", 400);
            }

            return ResponseHelper::sendSuccessResponse([], 'Pet deleted successfully');
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}
