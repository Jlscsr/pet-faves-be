<?php

namespace App\Controllers;

use Ramsey\Uuid\Uuid;

use App\Helpers\ResponseHelper;

use App\Validators\HTTPRequestValidator;

use App\Models\PetsModel;

use RuntimeException;

class PetsController
{
    private $petsModel;
    private $acceptableParamsKeys = ['id', 'userID', 'approvalStatus', 'status', 'label', 'petType', 'adoptionStatus'];
    private $expectedPostPayloadKeys = ['userOwnerID', 'petName', 'petAge', 'petAgeCategory', 'petColor', 'petGender', 'petType', 'petBreed', 'petVacHistory', 'petHistory', 'petPhotoURL', 'adoptionStatus', 'approvalStatus', 'postType'];

    public function __construct($pdo)
    {
        $this->petsModel = new PetsModel($pdo);
    }

    public function getAllPets()
    {
        try {

            $limit;
            $offset;

            if(isset($_GET['limit']) && isset($_GET['offset'])){
                $limit = (int) $_GET['limit'];
                $offset = (int) $_GET['offset'];
            }else{
                $limit = 0;
                $offset = 0;
            }

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

            if(isset($_GET['limit']) && isset($_GET['offset'])){
                $limit = (int) $_GET['limit'];
                $offset = (int) $_GET['offset'];
            }else{
                $limit = 0;
                $offset = 0;
            }

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
            HTTPRequestValidator::validatePOSTPayload($this->expectedPostPayloadKeys, $payload);

            $uuid = Uuid::uuid7()->toString();
            $payload['id'] = $uuid;

            $response = $this->petsModel->addNewPet($payload);


            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to add pet", 400);
            }

            return ResponseHelper::sendSuccessResponse($response, "Pet added successfully");
        } catch (RuntimeException $e) {
            return ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function updatePetData(array $params, array $payload)
    {
        try {
            HTTPRequestValidator::validatePUTPayload($this->acceptableParamsKeys, $this->expectedPostPayloadKeys, $params, $payload);

            $petID = $params['id'];

            $response = $this->petsModel->updatePetData($petID, $payload);

            if (!$response) {
                return ResponseHelper::sendErrorResponse("Failed to update pet data", 400);
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

            $response = $this->petsModel->updatePetApprovalStatus($postID, $approvalStatus);

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
