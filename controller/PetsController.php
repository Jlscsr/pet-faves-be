<?php


use Helpers\ResponseHelper;
use Helpers\HeaderHelper;

use Models\PetsModel;

class PetsController
{
    private $petsModel;

    /**
     * Constructor for the class.
     *
     * @param PDO $pdo The PDO object for database connection.
     * @return void
     */
    public function __construct($pdo)
    {
        $this->petsModel = new PetsModel($pdo);

        HeaderHelper::setResponseHeaders();
    }

    public function getAllPets()
    {
        try {
            $pets = $this->petsModel->getAllPets();

            if (!$pets) {
                ResponseHelper::sendSuccessResponse([], 'No pets found');
                return;
            }

            ResponseHelper::sendSuccessResponse($pets, 'Pets found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetsByLabel($param)
    {
        try {
            if (empty($param) || !isset($param['label'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing label parameter", 400);
                return;
            }

            $label = $param['label'];

            $pets = $this->petsModel->getAllPetsByLabel($label);

            if (!$pets) {
                ResponseHelper::sendSuccessResponse([], 'No pets found');
                return;
            }

            ResponseHelper::sendSuccessResponse($pets, 'Pets found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetTypes()
    {
        try {
            $types = $this->petsModel->getAllPetTypes();

            if (!$types) {
                ResponseHelper::sendSuccessResponse([], 'No types found');
                return;
            }

            ResponseHelper::sendSuccessResponse($types, 'Types found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getAllPetBreedsByType($param)
    {
        try {
            if (empty($param) || !isset($param['petType'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing pet type parameter", 400);
                return;
            }

            $petType = $param['petType'];

            $breeds = $this->petsModel->getAllPetBreedsByType($petType);

            if (!$breeds) {
                ResponseHelper::sendSuccessResponse([], 'No breeds found');
                return;
            }

            ResponseHelper::sendSuccessResponse($breeds, 'Breeds found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }

    public function getPetByID($param)
    {
        try {
            if (empty($param) || !isset($param['id'])) {
                ResponseHelper::sendErrorResponse("Invalid or missing pet id parameter", 400);
                return;
            }

            $petID = (int) $param['id'];

            $pet = $this->petsModel->getPetByID($petID);

            if (!$pet) {
                ResponseHelper::sendSuccessResponse([], 'No pet found');
                return;
            }

            ResponseHelper::sendSuccessResponse($pet, 'Pet found');
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }


    public function addNewPet($payload)
    {
        try {
            if (empty($payload)) {
                ResponseHelper::sendErrorResponse("Invalid payload or payload is empty", 400);
                return;
            }


            $pet = $this->petsModel->addNewPet($payload);

            if (!$pet) {
                ResponseHelper::sendErrorResponse("Failed to add pet", 400);
                return;
            }

            ResponseHelper::sendSuccessResponse([], "Pet added successfully");
        } catch (RuntimeException $e) {
            ResponseHelper::sendErrorResponse($e->getMessage());
        }
    }
}